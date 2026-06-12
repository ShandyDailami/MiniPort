<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Credential;
use App\Models\Log;
use App\Models\Bucket; // Tambahkan ini
use Aws\S3\S3Client;   // Tambahkan ini
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function view()
    {
        $user = Auth::user();
        return view('frontend.index', [
            'name' => $user->name
        ]);
    }

    public function dashboardView(Request $request)
    {
        $user = Auth::user();

        $activeCredential = Credential::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        $logs = Log::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $buckets = Bucket::where('user_id', $user->id)
            ->latest()
            ->get();

        $latestBuckets = $buckets->take(3);

        $totalBuckets = $buckets->count();
        $totalObjects = 0;
        $usedStorageBytes = 0;

        if ($activeCredential) {
            foreach ($buckets as $bucket) {
                try {
                    $s3Client = new S3Client([
                        'version' => 'latest',
                        'region' => $bucket->region,
                        'endpoint' => env('MINISTACK_ENDPOINT', 'http://ministack:4566'),
                        'use_path_style_endpoint' => true,
                        'credentials' => [
                            'key' => $activeCredential->access_key,
                            'secret' => $activeCredential->secret_key,
                        ],
                    ]);

                    $params = [
                        'Bucket' => $bucket->bucket_name,
                    ];

                    do {
                        $result = $s3Client->listObjectsV2($params);

                        foreach ($result['Contents'] ?? [] as $object) {
                            $totalObjects++;
                            $usedStorageBytes += $object['Size'];
                        }

                        $params['ContinuationToken'] = $result['NextContinuationToken'] ?? null;
                    } while (!empty($result['IsTruncated']));

                } catch (\Throwable $e) {
                    // Bucket yang error dilewati agar dashboard tetap bisa dibuka.
                    continue;
                }
            }
        }

        $storageLimitBytes = (int) env('MINIPORT_DEFAULT_STORAGE_LIMIT_MB', 50) * 1024 * 1024;
        $remainingStorageBytes = max(0, $storageLimitBytes - $usedStorageBytes);

        $usagePercentage = 0;
        if ($storageLimitBytes > 0) {
            $usagePercentage = min(100, ($usedStorageBytes / $storageLimitBytes) * 100);
        }

        $planName = $user->plan_name ?? 'Basic';

        return view('frontend.dashboard', [
            'name' => $user->name,
            'activeCredential' => $activeCredential,
            'logs' => $logs,
            'buckets' => $buckets,
            'latestBuckets' => $latestBuckets,
            'planName' => $planName,

            'totalBuckets' => $totalBuckets,
            'totalObjects' => $totalObjects,
            'usedStorageBytes' => $usedStorageBytes,
            'storageLimitBytes' => $storageLimitBytes,
            'remainingStorageBytes' => $remainingStorageBytes,
            'usagePercentage' => $usagePercentage,

            'usedStorageText' => $this->formatBytes($usedStorageBytes),
            'storageLimitText' => $this->formatBytes($storageLimitBytes),
            'remainingStorageText' => $this->formatBytes($remainingStorageBytes),
        ]);
    }
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
