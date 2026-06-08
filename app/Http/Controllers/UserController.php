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

        // 1. Ambil Kredensial Aktif
        $activeCredential = Credential::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        // 2. Ambil 5 Log Aktivitas Terakhir
        $logs = Log::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // 3. LOGIKA DATA ASLI: Menghitung total penyimpanan S3 di MiniStack
        $usedStorageGB = 0;

        if ($activeCredential) {
            // Inisialisasi S3Client
            $s3Client = new S3Client([
                'version' => 'latest',
                'region' => 'ap-southeast-1',
                'endpoint' => 'http://localhost:4566',
                'use_path_style_endpoint' => true,
                'credentials' => [
                    'key' => $activeCredential->access_key,
                    'secret' => $activeCredential->secret_key,
                ],
            ]);

            // Ambil daftar bucket milik user ini
            $buckets = Bucket::where('user_id', $user->id)->get();
            $totalBytes = 0;

            // Looping ke setiap bucket untuk menghitung ukuran file (objek) di dalamnya
            foreach ($buckets as $bucket) {
                try {
                    $objects = $s3Client->listObjectsV2([
                        'Bucket' => $bucket->bucket_name
                    ]);

                    if (isset($objects['Contents'])) {
                        foreach ($objects['Contents'] as $object) {
                            $totalBytes += $object['Size']; // Size dalam format byte
                        }
                    }
                } catch (\Exception $e) {
                    // Abaikan jika error (misal bucket kosong atau belum siap)
                }
            }

            // Konversi dari Bytes ke Gigabytes
            $usedStorageGB = $totalBytes / 1073741824;
        }

        // 4. DATA ASLI KOTA & PAKET (Dari tabel database)
        // Kita ambil dari tabel users. Jika belum Anda buat kolomnya, ia akan menggunakan nilai bawaan (fallback).
        $planName = $user->plan_name ?? 'Basic';
        $totalStorageGB = $user->storage_limit_gb ?? 50;

        // 5. Kalkulasi Persentase UI
        $usagePercentage = 0;
        if ($totalStorageGB > 0) {
            $usagePercentage = ($usedStorageGB / $totalStorageGB) * 100;
        }

        // Batasi persentase maksimal 100% agar bar UI tidak melebihi kotak
        if ($usagePercentage > 100)
            $usagePercentage = 100;

        // 6. Kirim semua data ke View
        return view('frontend.dashboard', [
            'name' => $user->name,
            'activeCredential' => $activeCredential,
            'logs' => $logs,
            'planName' => $planName,
            'totalStorageGB' => $totalStorageGB,
            'usedStorageGB' => $usedStorageGB,
            'usagePercentage' => $usagePercentage
        ]);
    }
}
