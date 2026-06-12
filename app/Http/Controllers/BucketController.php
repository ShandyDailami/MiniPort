<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use App\Models\Credential;
use App\Models\Log;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BucketController extends Controller
{
    public function index()
    {
        $buckets = Bucket::where('user_id', Auth::id())->latest()->get();
        return view('frontend.bucket.index', compact('buckets'));
    }
    public function create()
    {
        return view('frontend.bucket.create');
    }
    public function store(Request $request)
    {
        $request->validate([
            'bucket_name' => 'required|string|min:3|max:63|unique:buckets,bucket_name|regex:/^[a-z0-9.-]+$/',
            'region' => 'required|string'
        ]);

        $user = $request->user();
        $bucketName = $request->bucket_name;
        $region = $request->region;

        $userCredential = Credential::where('user_id', $user->id)->where('status', 'active')->first();

        if (!$userCredential) {
            return redirect()->back()->with('error', 'Anda belum memiliki API Key aktif. Silakan generate API Key di menu API Keys terlebih dahulu sebelum membuat bucket.');
        }
        // hit ke S3 untuk request
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => $region,
            'endpoint' => env('MINISTACK_ENDPOINT', 'http://ministack:4566'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $userCredential->access_key,
                'secret' => $userCredential->secret_key,
            ]
        ]);

        DB::beginTransaction();
        try {
            $createParams = [
                'Bucket' => $bucketName,
            ];

            if ($region !== 'us-east-1') {
                $createParams['CreateBucketConfiguration'] = [
                    'LocationConstraint' => $region,
                ];
            }

            $s3Client->createBucket($createParams);

            $s3Client->waitUntil('BucketExists', [
                'Bucket' => $bucketName,
            ]);

            $bucket = Bucket::create([
                'user_id' => $user->id,
                'bucket_name' => $bucketName,
                'region' => $region,
            ]);

            Log::create([
                'user_id' => $user->id,
                'action' => "CREATE_BUCKET",
                'ip_address' => $request->ip(),
                'details' => "Berhasil membuat bucket s3: {$bucketName} di region {$region}",
            ]);

            DB::commit();
            return redirect('/buckets')->with('success', "Bucket {$bucketName} berhasil dibuat.");
        } catch (AwsException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghubungi server MiniStack: ' . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
    public function show(Bucket $bucket)
    {
        if ($bucket->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke bucket ini.');
        }

        $credential = Credential::where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if (!$credential) {
            return redirect('/credentials')
                ->with('error', 'Anda belum memiliki API Key aktif.');
        }

        try {
            $s3Client = $this->s3Client($bucket->region, $credential);

            $s3Client->headBucket([
                'Bucket' => $bucket->bucket_name,
            ]);

            $result = $s3Client->listObjectsV2([
                'Bucket' => $bucket->bucket_name,
            ]);

            $objects = collect($result['Contents'] ?? [])->map(function ($object) {
                return [
                    'key' => $object['Key'],
                    'size' => $object['Size'],
                    'last_modified' => $object['LastModified'] ?? null,
                ];
            });

            $usedStorageBytes = $this->getUserStorageUsageBytes(Auth::id(), $credential);
            $storageLimitBytes = $this->getStorageLimitBytes();

            $usedStorageText = $this->formatBytes($usedStorageBytes);
            $storageLimitText = $this->formatBytes($storageLimitBytes);

            $usagePercentage = 0;
            if ($storageLimitBytes > 0) {
                $usagePercentage = min(100, ($usedStorageBytes / $storageLimitBytes) * 100);
            }

            return view('frontend.bucket.show', compact(
                'bucket',
                'objects',
                'usedStorageText',
                'storageLimitText',
                'usagePercentage'
            ));
        } catch (\Aws\Exception\AwsException $e) {
            if ($e->getAwsErrorCode() === 'NoSuchBucket') {
                return redirect('/buckets')
                    ->with('error', "Bucket {$bucket->bucket_name} ada di database, tetapi tidak ada di MiniStack/S3. Hapus record DB atau buat ulang bucket di MiniStack.");
            }

            return redirect('/buckets')
                ->with('error', 'Gagal membuka bucket: ' . $e->getAwsErrorMessage());
        }
    }
    public function uploadObject(Request $request, Bucket $bucket)
    {
        if ($bucket->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke bucket ini.');
        }

        $request->validate([
            'object_file' => 'required|file|max:10240', // max 10 MB
        ]);

        $credential = Credential::where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if (!$credential) {
            return redirect('/credentials')
                ->with('error', 'Anda belum memiliki API Key aktif.');
        }

        $file = $request->file('object_file');

        $fileSizeBytes = $file->getSize();
        $usedStorageBytes = $this->getUserStorageUsageBytes(Auth::id(), $credential);
        $storageLimitBytes = $this->getStorageLimitBytes();

        if (($usedStorageBytes + $fileSizeBytes) > $storageLimitBytes) {
            return redirect()
                ->back()
                ->with('error', 'Upload ditolak. Storage quota tidak cukup. Terpakai: '
                    . $this->formatBytes($usedStorageBytes)
                    . ', ukuran file: '
                    . $this->formatBytes($fileSizeBytes)
                    . ', limit: '
                    . $this->formatBytes($storageLimitBytes)
                );
        }

        $originalName = $file->getClientOriginalName();
        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $extension = $file->getClientOriginalExtension();

        $objectKey = 'uploads/' . now()->format('Ymd_His') . '_' . $safeName;

        if ($extension) {
            $objectKey .= '.' . $extension;
        }

        try {
            $s3Client = $this->s3Client($bucket->region, $credential);

            $s3Client->headBucket([
                'Bucket' => $bucket->bucket_name,
            ]);

            $s3Client->putObject([
                'Bucket' => $bucket->bucket_name,
                'Key' => $objectKey,
                'SourceFile' => $file->getRealPath(),
                'ContentType' => $file->getMimeType(),
            ]);

            Log::create([
                'user_id' => Auth::id(),
                'action' => 'UPLOAD_OBJECT',
                'ip_address' => $request->ip(),
                'details' => "Upload file {$originalName} ke bucket {$bucket->bucket_name} dengan key {$objectKey}",
            ]);

            return redirect()
                ->back()
                ->with('success', "File {$originalName} berhasil diupload.");
        } catch (AwsException $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal upload file ke MiniStack/S3: ' . $e->getAwsErrorMessage());
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan sistem saat upload: ' . $e->getMessage());
        }
    }
    public function downloadObject(Request $request, Bucket $bucket)
    {
        if ($bucket->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke bucket ini.');
        }

        $request->validate([
            'key' => 'required|string',
        ]);

        $objectKey = $request->query('key');

        $credential = Credential::where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if (!$credential) {
            return redirect('/credentials')
                ->with('error', 'Anda belum memiliki API Key aktif.');
        }

        try {
            $s3Client = $this->s3Client($bucket->region, $credential);

            $result = $s3Client->getObject([
                'Bucket' => $bucket->bucket_name,
                'Key' => $objectKey,
            ]);

            Log::create([
                'user_id' => Auth::id(),
                'action' => 'DOWNLOAD_OBJECT',
                'ip_address' => $request->ip(),
                'details' => "Download object {$objectKey} dari bucket {$bucket->bucket_name}",
            ]);

            $fileName = basename($objectKey);
            $contentType = $result['ContentType'] ?? 'application/octet-stream';

            return response()->streamDownload(function () use ($result) {
                echo $result['Body']->getContents();
            }, $fileName, [
                'Content-Type' => $contentType,
            ]);

        } catch (AwsException $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal download file dari MiniStack/S3: ' . $e->getAwsErrorMessage());
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan sistem saat download: ' . $e->getMessage());
        }
    }
    public function deleteObject(Request $request, Bucket $bucket)
    {
        if ($bucket->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke bucket ini.');
        }

        $request->validate([
            'key' => 'required|string',
        ]);

        $objectKey = $request->input('key');

        $credential = Credential::where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if (!$credential) {
            return redirect('/credentials')
                ->with('error', 'Anda belum memiliki API Key aktif.');
        }

        try {
            $s3Client = $this->s3Client($bucket->region, $credential);

            $s3Client->deleteObject([
                'Bucket' => $bucket->bucket_name,
                'Key' => $objectKey,
            ]);

            Log::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_OBJECT',
                'ip_address' => $request->ip(),
                'details' => "Menghapus object {$objectKey} dari bucket {$bucket->bucket_name}",
            ]);

            return redirect()
                ->back()
                ->with('success', "Object {$objectKey} berhasil dihapus.");
        } catch (AwsException $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus file dari MiniStack/S3: ' . $e->getAwsErrorMessage());
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan sistem saat hapus file: ' . $e->getMessage());
        }
    }
    public function shareObject(Request $request, Bucket $bucket)
    {
        if ($bucket->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke bucket ini.');
        }

        $request->validate([
            'key' => 'required|string',
            'expires' => 'nullable|integer|min:1|max:60',
        ]);

        $objectKey = $request->query('key');
        $expires = (int) $request->query('expires', 5);

        $credential = Credential::where('user_id', Auth::id())
            ->where('status', 'active')
            ->first();

        if (!$credential) {
            return redirect('/credentials')
                ->with('error', 'Anda belum memiliki API Key aktif.');
        }

        try {
            // Cek dulu object benar-benar ada lewat endpoint internal Docker
            $internalClient = $this->s3Client($bucket->region, $credential);

            $internalClient->headObject([
                'Bucket' => $bucket->bucket_name,
                'Key' => $objectKey,
            ]);

            // Buat presigned URL dengan endpoint public agar bisa dibuka browser
            $publicClient = $this->s3PublicClient($bucket->region, $credential);

            $command = $publicClient->getCommand('GetObject', [
                'Bucket' => $bucket->bucket_name,
                'Key' => $objectKey,
            ]);

            $presignedRequest = $publicClient->createPresignedRequest(
                $command,
                "+{$expires} minutes"
            );

            $shareUrl = (string) $presignedRequest->getUri();

            Log::create([
                'user_id' => Auth::id(),
                'action' => 'SHARE_OBJECT',
                'ip_address' => $request->ip(),
                'details' => "Membuat temporary share link untuk object {$objectKey} dari bucket {$bucket->bucket_name}, berlaku {$expires} menit",
            ]);

            return redirect()
                ->back()
                ->with('success', "Temporary link untuk {$objectKey} berhasil dibuat.")
                ->with('share_url', $shareUrl)
                ->with('share_key', $objectKey)
                ->with('share_expires', $expires);
        } catch (AwsException $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal membuat share link dari MiniStack/S3: ' . $e->getAwsErrorMessage());
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan sistem saat membuat share link: ' . $e->getMessage());
        }
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
    private function getStorageLimitBytes(): int
    {
        $limitMb = (int) env('MINIPORT_DEFAULT_STORAGE_LIMIT_MB', 50);

        return $limitMb * 1024 * 1024;
    }
    private function getUserStorageUsageBytes(int $userId, Credential $credential): int
    {
        $buckets = Bucket::where('user_id', $userId)->get();
        $totalBytes = 0;

        foreach ($buckets as $bucket) {
            try {
                $s3Client = $this->s3Client($bucket->region, $credential);

                $params = [
                    'Bucket' => $bucket->bucket_name,
                ];

                do {
                    $result = $s3Client->listObjectsV2($params);

                    foreach ($result['Contents'] ?? [] as $object) {
                        $totalBytes += $object['Size'];
                    }

                    $params['ContinuationToken'] = $result['NextContinuationToken'] ?? null;
                } while (!empty($result['IsTruncated']));

            } catch (\Throwable $e) {
                // Untuk sementara abaikan bucket yang gagal dibaca.
                // Nanti bisa dicatat ke log kalau mau lebih rapi.
                continue;
            }
        }

        return $totalBytes;
    }
    private function s3Client(string $region, Credential $credential): S3Client
    {
        return new S3Client([
            'version' => 'latest',
            'region' => $region,
            'endpoint' => env('MINISTACK_ENDPOINT', 'http://ministack:4566'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $credential->access_key,
                'secret' => $credential->secret_key,
            ],
        ]);
    }
    private function s3PublicClient(string $region, Credential $credential): S3Client
    {
        return new S3Client([
            'version' => 'latest',
            'region' => $region,
            'endpoint' => env('MINISTACK_PUBLIC_ENDPOINT', 'http://localhost:4566'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $credential->access_key,
                'secret' => $credential->secret_key,
            ],
        ]);
    }
}
