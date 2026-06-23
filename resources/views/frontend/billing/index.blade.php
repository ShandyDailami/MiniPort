@extends('layout.app')

@section('title', 'Billing & Subscriptions')

@section('styles')
<style>
    @keyframes chomp-top {
        0%, 100% { transform: rotate(0deg); }
        50% { transform: rotate(-40deg); }
    }

    @keyframes chomp-bottom {
        0%, 100% { transform: rotate(0deg); }
        50% { transform: rotate(40deg); }
    }

    @keyframes float-ghost {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-6px); }
    }

    .animate-float {
        animation: float-ghost 2s ease-in-out infinite;
    }
</style>
@endsection

@section('content')
@php
    $currentPlanName = $activeSubscription?->plan?->plan_name ?? 'Free Sandbox';
    $currentPlanPrice = (float) ($activeSubscription?->plan?->price ?? 0);
    $currentPlanLimit = (float) ($activeSubscription?->plan?->storage_limit_mb ?? ($storageLimitMb ?? 10));
    $currentUsedStorage = (float) ($usedStorageMb ?? 0);
    $currentUsagePercentage = isset($usagePercentage)
        ? (float) $usagePercentage
        : (($currentPlanLimit > 0) ? min(100, ($currentUsedStorage / $currentPlanLimit) * 100) : 0);

    $nextBillingDate = $activeSubscription?->end_date ?? null;
    $nextBillingText = $nextBillingDate?->format('d M Y');
@endphp

<div class="flex flex-col gap-10">

    {{-- 1. HEADER BILLING --}}
    <div class="border-4 border-black bg-[#C4B5FD] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tight text-black">💳 Billing & Langganan</h1>
            <p class="font-bold text-slate-800 mt-1">
                Kelola paket penyimpanan S3 Sandbox Anda dan tingkatkan kuota limit MiniPort.
            </p>
        </div>

        <div class="border-2 border-black bg-white px-4 py-2 font-black shadow-[3px_3px_black] text-xs uppercase shrink-0">
            Status Akun:
            <span class="{{ $activeSubscription ? 'text-emerald-600' : 'text-slate-600' }}">
                {{ $activeSubscription ? 'Aktif' : 'Belum Berlangganan' }}
            </span>
        </div>
    </div>

    {{-- SESSION MESSAGE --}}
    @if(session('success'))
        <div class="border-4 border-black bg-green-400 p-4 font-black shadow-[4px_4px_black]">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="border-4 border-black bg-red-500 text-white p-4 font-black shadow-[4px_4px_black]">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    {{-- 2. STATUS LANGGANAN AKTIF --}}
    <div class="border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
        <h2 class="text-xl font-black uppercase mb-4">📊 Paket Aktif Saat Ini</h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
            {{-- Info Utama Paket --}}
            <div class="border-4 border-black bg-[#FFB5DA] p-6 shadow-[4px_4px_black] relative overflow-hidden h-full flex flex-col justify-between">
                <div>
                    <span class="border-2 border-black bg-white px-2 py-0.5 text-[9px] font-black uppercase tracking-wider shadow-[2px_2px_black]">
                        Current Plan
                    </span>

                    <h3 class="text-3xl font-black uppercase text-black mt-3">
                        {{ $currentPlanName }}
                    </h3>

                    <p class="font-bold text-slate-800 text-xs mt-1">
                        {{ $activeSubscription ? 'Paket Anda saat ini sedang aktif.' : 'Belum ada paket aktif. Silakan pilih paket.' }}
                    </p>
                </div>

                <div class="mt-6 border-t-2 border-black pt-4 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[9px] font-black text-slate-600 uppercase">Tarif Langganan</p>
                        <p class="text-lg font-black text-black">
                            Rp {{ number_format($currentPlanPrice, 0, ',', '.') }} / bln
                        </p>
                    </div>

                    <span class="bg-[#34D399] border-2 border-black px-2 py-1 text-[10px] font-black uppercase shadow-[2px_2px_black]">
                        {{ $activeSubscription ? 'Aktif' : 'Gratis' }}
                    </span>
                </div>

                @if($activeSubscription)
                    <form
                        action="{{ url('/billing/subscriptions/' . $activeSubscription->id . '/cancel') }}"
                        method="POST"
                        class="mt-5"
                        onsubmit="return confirm('Batalkan subscription aktif?');"
                    >
                        @csrf
                        @method('PATCH')

                        <button class="w-full border-4 border-black bg-red-500 text-white px-4 py-2 font-black uppercase shadow-[4px_4px_black]">
                            Batalkan Subscription
                        </button>
                    </form>
                @endif
            </div>

            {{-- Detail Alokasi Penyimpanan --}}
            <div class="lg:col-span-2 border-4 border-black bg-slate-50 p-6 shadow-[4px_4px_black] h-full flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-4">Penggunaan Kuota Sandbox Anda</h4>

                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between text-xs font-black uppercase mb-1.5">
                                <span>
                                    Kapasitas Disk (
                                    {{ number_format($currentUsedStorage, 2) }} MB /
                                    {{ number_format($currentPlanLimit, 0) }} MB
                                    )
                                </span>
                                <span>{{ round($currentUsagePercentage, 1) }}% USED</span>
                            </div>

                            <div class="relative h-14 w-full border-4 border-black bg-black rounded-none flex items-center shadow-[inset_0px_4px_0px_rgba(0,0,0,0.3)] select-none overflow-hidden">
                                <div
                                    class="h-full bg-slate-900 border-r-4 border-dashed border-blue-500"
                                    style="width: {{ min(100, max(0, $currentUsagePercentage)) }}%;"
                                ></div>

                                <div
                                    class="absolute flex items-center z-20"
                                    style="left: calc({{ min(95, max(1, $currentUsagePercentage)) }}% - 16px); transition: left 0.5s ease-in-out;"
                                >
                                    <div class="relative w-8 h-8 flex flex-col justify-center">
                                        <div class="absolute w-8 h-4 top-0 bg-[#FDE047] rounded-t-full origin-bottom animate-[chomp-top_0.25s_infinite_linear] border-t-2 border-l-2 border-r-2 border-black"></div>
                                        <div class="absolute w-8 h-4 bottom-0 bg-[#FDE047] rounded-b-full origin-top animate-[chomp-bottom_0.25s_infinite_linear] border-b-2 border-l-2 border-r-2 border-black"></div>
                                    </div>
                                </div>

                                <div class="absolute inset-0 flex items-center justify-between px-8 pointer-events-none z-10">
                                    @for($i = 0; $i < 12; $i++)
                                        @php
                                            $dotPercent = ($i / 11) * 100;
                                            $isEaten = $currentUsagePercentage >= $dotPercent;
                                        @endphp

                                        @if($i == 11)
                                            <div class="relative w-5 h-5 flex items-center justify-center">
                                                <div class="w-5 h-5 rounded-full bg-[#FDE047] border-2 border-black animate-ping absolute"></div>
                                                <div class="w-4 h-4 rounded-full bg-[#FDE047] border-2 border-black transition-opacity duration-300 {{ $isEaten ? 'opacity-0' : 'opacity-100' }}"></div>
                                            </div>
                                        @else
                                            <div class="w-2.5 h-2.5 rounded-full bg-[#FDE047] border-2 border-black transition-opacity duration-300 {{ $isEaten ? 'opacity-0' : 'opacity-100' }}"></div>
                                        @endif
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="border-2 border-black bg-white p-3 shadow-[2px_2px_black]">
                                <p class="text-[9px] font-black text-slate-400 uppercase">Batas Maksimal S3 Buckets</p>
                                <p class="text-lg font-black text-black">
                                    {{ $activeSubscription?->plan?->max_buckets ?? 'Default' }} Buckets
                                </p>
                            </div>
                            <div class="border-2 border-black bg-white p-3 shadow-[2px_2px_black]">
                                <p class="text-[9px] font-black text-slate-400 uppercase">Ukuran Maksimal Per File</p>
                                <p class="text-lg font-black text-black">
                                    {{ $activeSubscription?->plan?->max_file_size_mb ?? 'Default' }} MB
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-[10px] font-bold text-slate-500 mt-4 leading-relaxed">
                    * Pembayaran berikutnya akan didebit secara otomatis pada tanggal
                    <span class="text-black font-black">
                        {{ $nextBillingText ?? '-' }}
                    </span>
                    melalui saldo dompet virtual sandbox Anda.
                </p>
            </div>
        </div>
    </div>

    {{-- 3. PILIHAN PAKET --}}
    <div class="space-y-6">
        <div class="border-b-4 border-black pb-2">
            <h2 class="text-2xl font-black uppercase text-black">🚀 Tingkatkan Kapasitas Sandbox Anda</h2>
            <p class="font-bold text-slate-600 text-xs">Pilih paket penyimpanan di bawah ini untuk menambah kuota dan fungsionalitas S3.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
            @forelse($plans as $plan)
                @php
                    $isCurrentPlan = (int) ($activeSubscription?->plan_id ?? 0) === (int) $plan->id;
                    $isFree = (float) $plan->price <= 0;
                    $badgeColor = $isCurrentPlan ? '#FFB5DA' : ($isFree ? '#E2E8F0' : '#93EBF2');
                    $planLabel = $isCurrentPlan ? 'Paket Aktif' : ($isFree ? 'Free Tier' : 'Premium Tier');
                    $buttonDisabled = $isCurrentPlan;
                @endphp

                <div class="border-4 border-black bg-white shadow-[6px_6px_0px_rgba(0,0,0,1)] flex flex-col justify-between p-6 rounded-none relative overflow-hidden min-h-[380px] {{ $isCurrentPlan ? 'ring-4 ring-[#FFB5DA] ring-offset-2' : '' }}">
                    @if($isCurrentPlan)
                        <div class="absolute top-0 right-0 bg-[#FFB5DA] border-b-2 border-l-2 border-black px-3 py-1 text-[8px] font-black uppercase tracking-widest shadow-[2px_2px_rgba(0,0,0,1)]">
                            Paket Aktif
                        </div>
                    @endif

                    <div class="space-y-4">
                        <div class="flex items-start justify-between">
                            <span class="border-2 border-black px-2 py-0.5 text-[9px] font-black uppercase shadow-[2px_2px_black]" style="background-color: {{ $badgeColor }};">
                                {{ $planLabel }}
                            </span>

                            <div class="animate-float" style="animation-delay: {{ $loop->index * 0.5 }}s;">
                                <svg class="w-10 h-10 text-black" viewBox="0 0 100 100" fill="currentColor">
                                    <path d="M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z" stroke="black" stroke-width="4" />
                                    <circle cx="42" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                                    <circle cx="68" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                                    <circle cx="39" cy="48" r="4" fill="blue" />
                                    <circle cx="65" cy="48" r="4" fill="blue" />
                                </svg>
                            </div>
                        </div>

                        <h3 class="text-2xl font-black uppercase text-black">
                            {{ $plan->plan_name }}
                        </h3>

                        <p class="text-xs font-bold text-slate-600 leading-normal">
                            {{ $plan->description ?? 'Paket langganan object storage MiniPort.' }}
                        </p>

                        <div class="py-4 border-t-2 border-b-2 border-black border-dashed">
                            <p class="text-3xl font-black text-black">
                                @if((float) $plan->price <= 0)
                                    Rp 0 <span class="text-xs font-bold text-slate-500">/ selamanya</span>
                                @else
                                    Rp {{ number_format($plan->price, 0, ',', '.') }}
                                    <span class="text-xs font-bold text-slate-500">/ bulan</span>
                                @endif
                            </p>
                        </div>

                        <ul class="space-y-2 text-xs font-bold text-slate-800">
                            <li class="flex items-center gap-1.5">
                                🟢 Batas Kuota: {{ number_format($plan->storage_limit_mb) }} MB
                            </li>
                            <li class="flex items-center gap-1.5">
                                🟢 Maksimal {{ $plan->max_buckets ?? '—' }} Buckets
                            </li>
                            <li class="flex items-center gap-1.5">
                                🟢 Maksimal File {{ $plan->max_file_size_mb ?? '—' }} MB
                            </li>
                        </ul>
                    </div>

                    <form
                        action="{{ url('/billing/subscribe/' . $plan->id) }}"
                        method="POST"
                        class="mt-6"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="w-full border-4 border-black p-3 text-xs font-black uppercase shadow-[3px_3px_black] transition-all {{ $buttonDisabled ? 'bg-slate-200 text-slate-500 cursor-not-allowed' : 'bg-[#FDE047] text-black hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_black] active:translate-y-0.5 cursor-pointer' }}"
                            @if($buttonDisabled) disabled @endif
                        >
                            {{ $buttonDisabled ? 'Paket Anda Saat Ini' : 'Pilih Paket' }}
                        </button>
                    </form>
                </div>
            @empty
                <div class="md:col-span-3 border-4 border-black bg-white p-8 shadow-[8px_8px_black] font-black text-center uppercase">
                    Belum ada paket aktif di database.
                </div>
            @endforelse
        </div>
    </div>

    {{-- 4. RIWAYAT TRANSAKSI --}}
    <div class="border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
        <h2 class="text-xl font-black uppercase mb-6">📜 Riwayat Transaksi Sandbox</h2>

        <div class="overflow-x-auto border-4 border-black shadow-[4px_4px_black]">
            <table class="w-full text-left border-collapse font-bold text-xs">
                <thead>
                    <tr class="bg-[#E2E8F0] border-b-4 border-black uppercase tracking-wider">
                        <th class="p-4 border-r-4 border-black">Invoice ID</th>
                        <th class="p-4 border-r-4 border-black">Jenis Paket</th>
                        <th class="p-4 border-r-4 border-black">Metode Pembayaran</th>
                        <th class="p-4 border-r-4 border-black">Tanggal Transaksi</th>
                        <th class="p-4 border-r-4 border-black">Jumlah</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 border-l-4 border-black">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y-4 divide-black">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 border-r-4 border-black font-mono text-purple-700">
                                {{ $invoice->invoice_number }}
                            </td>

                            <td class="p-4 border-r-4 border-black">
                                {{ $invoice->subscription?->plan?->plan_name ?? '-' }}
                            </td>

                            <td class="p-4 border-r-4 border-black">
                                {{ $invoice->payment_method ?? ($invoice->status === 'paid' ? 'Simulated Payment' : 'Pending Payment') }}
                            </td>

                            <td class="p-4 border-r-4 border-black text-slate-600">
                                {{ $invoice->created_at?->format('d M Y, H:i') ?? '-' }}
                            </td>

                            <td class="p-4 border-r-4 border-black font-mono">
                                Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                            </td>

                            <td class="p-4">
                                @php
                                    $statusClass = match ($invoice->status) {
                                        'paid' => 'bg-[#34D399]',
                                        'pending' => 'bg-yellow-300',
                                        'cancelled' => 'bg-slate-300',
                                        default => 'bg-slate-200',
                                    };
                                @endphp

                                <span class="{{ $statusClass }} border-2 border-black px-2 py-0.5 text-[9px] font-black uppercase shadow-[2px_2px_black] inline-block">
                                    {{ strtoupper($invoice->status) }}
                                </span>
                            </td>

                            <td class="p-4 border-l-4 border-black">
                                @if($invoice->status === 'pending')
                                    <form
                                        action="{{ url('/billing/invoices/' . $invoice->id . '/pay') }}"
                                        method="POST"
                                    >
                                        @csrf

                                        <button class="border-4 border-black bg-[#34D399] px-4 py-2 font-black uppercase shadow-[3px_3px_black]">
                                            Bayar Simulasi
                                        </button>
                                    </form>
                                @else
                                    <span class="font-bold">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center font-black uppercase">
                                Belum ada invoice.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.lucide) {
            lucide.createIcons();
        }
    });
</script>
@endsection