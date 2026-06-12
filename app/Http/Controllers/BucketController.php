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

            return view('frontend.bucket.show', compact('bucket', 'objects'));
        } catch (\Aws\Exception\AwsException $e) {
            if ($e->getAwsErrorCode() === 'NoSuchBucket') {
                return redirect('/buckets')
                    ->with('error', "Bucket {$bucket->bucket_name} ada di database, tetapi tidak ada di MiniStack/S3. Hapus record DB atau buat ulang bucket di MiniStack.");
            }

            return redirect('/buckets')
                ->with('error', 'Gagal membuka bucket: ' . $e->getAwsErrorMessage());
        }
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
}
