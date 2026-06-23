<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Log;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Bucket;
use App\Models\Credential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $plans = Plan::with('service')
            ->whereHas('service', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('price')
            ->get();

        // Menggunakan filter tanggal yang sama persis dengan UserController agar sinkron
        $activeSubscription = Subscription::with('plan')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereDate('start_date', '<=', now()->toDateString())
            ->whereDate('end_date', '>=', now()->toDateString())
            ->latest()
            ->first();

        $invoices = Invoice::with('subscription.plan')
            ->where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        // === AMBIL UKURAN PENYIMPANAN RIIL DARI MINISTACK S3 ===
        $activeCredential = Credential::where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        $buckets = Bucket::where('user_id', $user->id)->get();
        $usedStorageBytes = 0;

        if ($activeCredential) {
            foreach ($buckets as $bucket) {
                try {
                    $s3Client = new \Aws\S3\S3Client([
                        'version' => 'latest',
                        'region' => $bucket->region,
                        'endpoint' => env('MINISTACK_ENDPOINT', 'http://ministack:4566'),
                        'use_path_style_endpoint' => true,
                        'credentials' => [
                            'key' => $activeCredential->access_key,
                            'secret' => $activeCredential->secret_key,
                        ],
                    ]);

                    $pages = $s3Client->getPaginator('ListObjectsV2', [
                        'Bucket' => $bucket->bucket_name,
                    ]);

                    foreach ($pages as $page) {
                        foreach ($page['Contents'] ?? [] as $object) {
                            $usedStorageBytes += (int) $object['Size'];
                        }
                    }
                } catch (\Throwable $e) {
                    // Berjaga-jaga jika localstack mati
                }
            }
        }

        // Ambil batasan limit dari database secara dinamis
        $defaultStorageLimitMb = (int) env('MINIPORT_DEFAULT_STORAGE_LIMIT_MB', 50);
        $planName = $activeSubscription ? $activeSubscription->plan->plan_name : 'Free';
        $planPrice = $activeSubscription ? $activeSubscription->plan->price : 0;
        $storageLimitMb = $activeSubscription ? $activeSubscription->plan->storage_limit_mb : $defaultStorageLimitMb;

        $storageLimitBytes = $storageLimitMb * 1024 * 1024;
        
        $usagePercentage = 0;
        if ($storageLimitBytes > 0) {
            $usagePercentage = min(100, ($usedStorageBytes / $storageLimitBytes) * 100);
        }

        $usedStorageText = $this->formatBytes($usedStorageBytes);
        $storageLimitText = $storageLimitMb >= 1024 ? ($storageLimitMb / 1024) . ' GB' : $storageLimitMb . ' MB';

        return view('frontend.billing.index', compact(
            'plans',
            'activeSubscription',
            'invoices',
            'planName',
            'planPrice',
            'usedStorageText',
            'storageLimitText',
            'usagePercentage'
        ));
    }

    public function subscribe(Request $request, Plan $plan)
    {
        if (!$plan->service || !$plan->service->is_active) {
            return redirect()->back()->with('error', 'Paket tidak tersedia.');
        }

        $userId = Auth::id();

        DB::beginTransaction();
        try {
            // Bersihkan semua instansi paket pending/active yang lama
            Subscription::where('user_id', $userId)
                ->whereIn('status', ['active', 'pending'])
                ->update([
                    'status' => 'cancelled',
                    'end_date' => now()->toDateString()
                ]);

            // BYPASS UTAMA: Langsung buat status 'active' tanpa pending
            $subscription = Subscription::create([
                'user_id' => $userId,
                'plan_id' => $plan->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'status' => 'active', 
            ]);

            // Invoice langsung berstatus 'paid' (Lunas)
            $invoice = Invoice::create([
                'user_id' => $userId,
                'subscription_id' => $subscription->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'amount' => $plan->price,
                'status' => 'paid', 
                'due_date' => now()->toDateString(),
                'paid_at' => now(),
                'payment_method' => 'sandbox-instant',
            ]);

            Log::create([
                'user_id' => $userId,
                'action' => 'UPGRADE_SUBSCRIPTION',
                'ip_address' => $request->ip(),
                'details' => "Instant upgrade ke paket {$plan->plan_name} (Lunas otomatis).",
            ]);

            DB::commit();
            return redirect('/billing')->with('success', "Sukses! Paket {$plan->plan_name} Anda telah aktif.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses upgrade: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($subscription, $request) {
            $subscription->update([
                'status' => 'cancelled',
                'end_date' => now()->toDateString(),
            ]);

            Log::create([
                'user_id' => Auth::id(),
                'action' => 'CANCEL_SUBSCRIPTION',
                'ip_address' => $request->ip(),
                'details' => "Membatalkan langganan paket ID {$subscription->id}.",
            ]);
        });

        return redirect('/billing')->with('success', 'Langganan berhasil dihentikan.');
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
        } while (Invoice::where('invoice_number', $number)->exists());
        return $number;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' bytes';
    }
}