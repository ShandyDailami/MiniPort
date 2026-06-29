<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Credential;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginView()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // 1. CEK APAKAH INI ATTEMPT LOGIN MENGGUNAKAN API KEY (KEYBEARER)
        if ($request->has('access_key') && $request->has('secret_key')) {
            $request->validate([
                'access_key' => 'required|string',
                'secret_key' => 'required|string',
            ]);

            // Cari kredensial aktif yang cocok di database
            $credential = Credential::where('access_key', $request->access_key)
                ->where('secret_key', $request->secret_key)
                ->where('status', 'active')
                ->first();

            if ($credential) {
                // Ambil user pemilik kredensial
                $user = User::find($credential->user_id);
                
                if ($user) {
                    Auth::login($user);
                    $request->session()->regenerate();

                    // Catat Log Aktivitas masuk via API Key
                    Log::create([
                        'user_id' => $user->id,
                        'action' => 'LOGIN_VIA_API_KEY',
                        'ip_address' => $request->ip(),
                        'details' => "Berhasil login ke konsol menggunakan API Key: {$credential->access_key}"
                    ]);

                    return redirect()->intended('/');
                }
            }

            return back()->withErrors([
                'error' => 'Kombinasi Access Key atau Secret Key salah, atau status kunci telah dicabut (Revoked).'
            ])->onlyInput('access_key');
        }

        // 2. JIKA LOGIN BIASA MENGGUNAKAN EMAIL & PASSWORD
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'error' => 'Email atau password salah.'
        ])->onlyInput('email');
    }

    public function registView()
    {
        return view('auth.register');
    }

    public function regist(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect('/login')->with('success', 'Pendaftaran berhasil! Silakan masuk untuk melanjutkan.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}