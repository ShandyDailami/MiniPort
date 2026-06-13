<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Log;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    public function index()
    {
        $plans = Plan::with('service')
            ->whereHas('service', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('price')
            ->get();

        $activeSubscription = Subscription::with('plan')
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now()->toDateString())
            ->latest()
            ->first();

        $invoices = Invoice::with('subscription.plan')
            ->where('user_id', Auth::id())
            ->latest()
            ->take(10)
            ->get();

        return view('frontend.billing.index', compact(
            'plans',
            'activeSubscription',
            'invoices'
        ));
    }

    public function subscribe(Request $request, Plan $plan)
    {
        if (!$plan->service || !$plan->service->is_active) {
            return redirect()
                ->back()
                ->with('error', 'Paket tidak tersedia.');
        }

        $userId = Auth::id();

        $currentSubscription = Subscription::where('user_id', $userId)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now()->toDateString())
            ->latest()
            ->first();

        if ($currentSubscription?->plan_id === $plan->id) {
            return redirect()
                ->back()
                ->with('error', 'Paket tersebut sudah aktif.');
        }

        DB::beginTransaction();

        try {
            // Batalkan checkout lama yang belum dibayar.
            $pendingSubscriptions = Subscription::where('user_id', $userId)
                ->where('status', 'pending')
                ->pluck('id');

            Invoice::where('user_id', $userId)
                ->whereIn('subscription_id', $pendingSubscriptions)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            Subscription::whereIn('id', $pendingSubscriptions)
                ->update(['status' => 'cancelled']);

            $isFree = (float) $plan->price <= 0;

            $subscription = Subscription::create([
                'user_id' => $userId,
                'plan_id' => $plan->id,
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
                'status' => $isFree ? 'active' : 'pending',
            ]);

            $invoice = Invoice::create([
                'user_id' => $userId,
                'subscription_id' => $subscription->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'amount' => $plan->price,
                'status' => $isFree ? 'paid' : 'pending',
                'due_date' => now()->addDay()->toDateString(),
                'paid_at' => $isFree ? now() : null,
                'payment_method' => $isFree ? 'free-plan' : null,
            ]);

            if ($isFree) {
                Subscription::where('user_id', $userId)
                    ->where('status', 'active')
                    ->where('id', '!=', $subscription->id)
                    ->update([
                        'status' => 'cancelled',
                        'end_date' => now()->toDateString(),
                    ]);
            }

            Log::create([
                'user_id' => $userId,
                'action' => 'CREATE_SUBSCRIPTION',
                'ip_address' => $request->ip(),
                'details' => "Memilih paket {$plan->plan_name} dengan invoice {$invoice->invoice_number}.",
            ]);

            DB::commit();

            if ($isFree) {
                return redirect('/billing')
                    ->with('success', "Paket {$plan->plan_name} berhasil diaktifkan.");
            }

            return redirect('/billing')
                ->with('success', 'Invoice berhasil dibuat. Lakukan pembayaran simulasi.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Gagal membuat subscription: ' . $e->getMessage());
        }
    }

    public function pay(Request $request, Invoice $invoice)
    {
        if ($invoice->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke invoice ini.');
        }

        if ($invoice->status === 'paid') {
            return redirect()
                ->back()
                ->with('error', 'Invoice sudah dibayar.');
        }

        if ($invoice->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Invoice tidak dapat dibayar.');
        }

        DB::beginTransaction();

        try {
            $subscription = $invoice->subscription;

            if (!$subscription) {
                throw new \RuntimeException('Subscription invoice tidak ditemukan.');
            }

            Subscription::where('user_id', Auth::id())
                ->where('status', 'active')
                ->where('id', '!=', $subscription->id)
                ->update([
                    'status' => 'cancelled',
                    'end_date' => now()->toDateString(),
                ]);

            $subscription->update([
                'status' => 'active',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(30)->toDateString(),
            ]);

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'simulated-payment',
            ]);

            Log::create([
                'user_id' => Auth::id(),
                'action' => 'PAY_INVOICE',
                'ip_address' => $request->ip(),
                'details' => "Membayar invoice {$invoice->invoice_number} secara simulasi.",
            ]);

            DB::commit();

            return redirect('/billing')
                ->with('success', 'Pembayaran berhasil. Paket baru sudah aktif.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Pembayaran gagal: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, Subscription $subscription)
    {
        if ($subscription->user_id !== Auth::id()) {
            abort(403, 'Anda tidak punya akses ke subscription ini.');
        }

        if (!in_array($subscription->status, ['active', 'pending'], true)) {
            return redirect()
                ->back()
                ->with('error', 'Subscription sudah tidak aktif.');
        }

        DB::transaction(function () use ($subscription, $request) {
            $subscription->update([
                'status' => 'cancelled',
                'end_date' => now()->toDateString(),
            ]);

            Invoice::where('subscription_id', $subscription->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            Log::create([
                'user_id' => Auth::id(),
                'action' => 'CANCEL_SUBSCRIPTION',
                'ip_address' => $request->ip(),
                'details' => "Membatalkan subscription ID {$subscription->id}.",
            ]);
        });

        return redirect('/billing')
            ->with('success', 'Subscription berhasil dibatalkan.');
    }

    private function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV-'
                . now()->format('YmdHis')
                . '-'
                . strtoupper(Str::random(6));
        } while (Invoice::where('invoice_number', $number)->exists());

        return $number;
    }
}