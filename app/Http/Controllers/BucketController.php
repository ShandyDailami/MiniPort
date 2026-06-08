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
            $s3Client->createBucket([
                'Bucket' => $bucketName
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

        return view('frontend.bucket.show', compact('bucket'));
    }
}
