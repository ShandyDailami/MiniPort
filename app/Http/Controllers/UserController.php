<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use App\Models\Credential;
use App\Models\Log;
use App\Models\Subscription;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Menampilkan halaman frontend lama jika masih digunakan.
     */
    public function view()
    {
        $user = Auth::user();

        return view('frontend.index', [
            'name' => $user->name,
        ]);
    }

    /**
     * Menampilkan dashboard utama MiniPort.
     */
    public function dashboardView()
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | Credential aktif
        |--------------------------------------------------------------------------
        */

        $activeCredential = Credential::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Subscription dan paket aktif
        |--------------------------------------------------------------------------
        */

        $activeSubscription = Subscription::with('plan')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->latest()
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Log aktivitas terbaru
        |--------------------------------------------------------------------------
        */

        $logs = Log::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Bucket milik user
        |--------------------------------------------------------------------------
        */

        $buckets = Bucket::where('user_id', $user->id)
            ->latest()
            ->get();

        $latestBuckets = $buckets->take(3);

        /*
        |--------------------------------------------------------------------------
        | Metrik Object Storage
        |--------------------------------------------------------------------------
        */

        $totalBuckets = $buckets->count();
        $totalObjects = 0;
        $usedStorageBytes = 0;
        $unavailableBuckets = 0;

        if ($activeCredential) {
            foreach ($buckets as $bucket) {
                try {
                    $s3Client = $this->createS3Client(
                        $bucket->region,
                        $activeCredential
                    );

                    /*
                     * Paginator memastikan seluruh object dihitung,
                     * termasuk jika jumlah object melebihi 1.000.
                     */
                    $pages = $s3Client->getPaginator('ListObjectsV2', [
                        'Bucket' => $bucket->bucket_name,
                    ]);

                    foreach ($pages as $page) {
                        foreach ($page['Contents'] ?? [] as $object) {
                            $totalObjects++;
                            $usedStorageBytes += (int) $object['Size'];
                        }
                    }
                } catch (\Throwable $e) {
                    /*
                     * Bucket yang tidak tersedia dilewati agar dashboard
                     * tetap dapat dibuka.
                     */
                    $unavailableBuckets++;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Storage quota berdasarkan subscription
        |--------------------------------------------------------------------------
        |
        | Jika user memiliki subscription aktif, quota diambil dari plan.
        | Jika tidak, quota menggunakan nilai default dari .env.
        |
        */

        $defaultStorageLimitMb = (int) env(
            'MINIPORT_DEFAULT_STORAGE_LIMIT_MB',
            50
        );

        $storageLimitMb = (int) (
            $activeSubscription?->plan?->storage_limit_mb
            ?? $defaultStorageLimitMb
        );

        $storageLimitBytes = $storageLimitMb * 1024 * 1024;

        $remainingStorageBytes = max(
            0,
            $storageLimitBytes - $usedStorageBytes
        );

        $usagePercentage = 0;

        if ($storageLimitBytes > 0) {
            $usagePercentage = min(
                100,
                ($usedStorageBytes / $storageLimitBytes) * 100
            );
        }

        $planName = $activeSubscription?->plan?->plan_name
            ?? 'Default';

        /*
        |--------------------------------------------------------------------------
        | Kirim data ke dashboard
        |--------------------------------------------------------------------------
        */

        return view('frontend.dashboard', [
            'name' => $user->name,

            'activeCredential' => $activeCredential,
            'activeSubscription' => $activeSubscription,

            'logs' => $logs,
            'buckets' => $buckets,
            'latestBuckets' => $latestBuckets,

            'planName' => $planName,
            'storageLimitMb' => $storageLimitMb,

            'totalBuckets' => $totalBuckets,
            'totalObjects' => $totalObjects,
            'unavailableBuckets' => $unavailableBuckets,

            'usedStorageBytes' => $usedStorageBytes,
            'storageLimitBytes' => $storageLimitBytes,
            'remainingStorageBytes' => $remainingStorageBytes,
            'usagePercentage' => $usagePercentage,

            'usedStorageText' => $this->formatBytes(
                $usedStorageBytes
            ),

            'storageLimitText' => $this->formatBytes(
                $storageLimitBytes
            ),

            'remainingStorageText' => $this->formatBytes(
                $remainingStorageBytes
            ),
        ]);
    }

    /**
     * Membuat client MiniStack / S3.
     */
    private function createS3Client(
        string $region,
        Credential $credential
    ): S3Client {
        return new S3Client([
            'version' => 'latest',
            'region' => $region,

            'endpoint' => env(
                'MINISTACK_ENDPOINT',
                'http://ministack:4566'
            ),

            'use_path_style_endpoint' => true,

            'credentials' => [
                'key' => $credential->access_key,
                'secret' => $credential->secret_key,
            ],
        ]);
    }

    /**
     * Mengubah byte menjadi format yang mudah dibaca.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }
}