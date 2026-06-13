<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Credential;
use App\Models\Log;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. Ambil Kredensial Aktif milik user yang sedang login
        $activeCredential = Credential::where('user_id', $userId)
                                      ->where('status', 'active')
                                      ->first();

        // 2. Ambil 5 Log Aktivitas Terakhir
        $logs = Log::where('user_id', $userId)
                   ->latest()
                   ->take(5)
                   ->get();

        // 3. Data Kuota (Simulasi sementara)
        $planName = 'Basic';
        $totalStorageGB = 50;
        $usedStorageGB = 12.5;
        $usagePercentage = ($usedStorageGB / $totalStorageGB) * 100;

        // 4. PENTING: Kirim SEMUA variabel tersebut ke View menggunakan compact()
        return view('frontend.dashboard', compact(
            'activeCredential',
            'logs',
            'planName',
            'totalStorageGB',
            'usedStorageGB',
            'usagePercentage'
        ));
    }
}
