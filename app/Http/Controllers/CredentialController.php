<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Credential;
use App\Models\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CredentialController extends Controller
{
    // Menampilkan halaman Manajemen API Keys
    public function index()
    {
        $credentials = Credential::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend.credential.index', compact('credentials'));
    }

    // Memproses pembuatan kunci baru (Generate)
    public function store(Request $request)
    {
        $user = Auth::user();

        DB::beginTransaction();
        try {
            // Generate Access Key (20 karakter) & Secret Key (40 karakter)
            $accessKey = strtoupper(Str::random(20));
            $secretKey = Str::random(40);

            $credential = Credential::create([
                'user_id' => $user->id,
                'access_key' => $accessKey,
                'secret_key' => $secretKey,
                'status' => 'active'
            ]);

            Log::create([
                'user_id' => $user->id,
                'action' => 'GENERATE_CREDENTIAL',
                'ip_address' => $request->ip(),
                'details' => "User men-generate kredensial S3 baru dengan Access Key: {$accessKey}"
            ]);

            DB::commit();

            // Flash Secret Key ke session agar bisa ditampilkan SEKALI di Blade
            return redirect()->back()
                ->with('success', 'Kredensial berhasil dibuat!')
                ->with('new_access_key', $accessKey)
                ->with('new_secret_key', $secretKey);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal membuat kredensial: ' . $e->getMessage());
        }
    }

    // Memproses pencabutan kunci (Revoke)
    public function revoke(Request $request, $id)
    {
        $credential = Credential::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        $credential->update(['status' => 'inactive']);

        Log::create([
            'user_id' => Auth::id(),
            'action' => 'REVOKE_CREDENTIAL',
            'ip_address' => $request->ip(),
            'details' => "Menonaktifkan kredensial dengan Access Key: {$credential->access_key}"
        ]);

        return redirect()->back()->with('success', 'Akses kredensial berhasil dicabut.');
    }
}
