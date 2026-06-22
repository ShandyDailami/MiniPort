@extends('layout.app')

@section('title', 'Billing & Subscriptions')

@section('styles')
<style>
    @keyframes chomp-top { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(-40deg); } }
    @keyframes chomp-bottom { 0%, 100% { transform: rotate(0deg); } 50% { transform: rotate(40deg); } }
    @keyframes float-ghost { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-6px); } }
    .animate-float { animation: float-ghost 2s ease-in-out infinite; }
</style>
@endsection

@section('content')
<div class="flex flex-col gap-10" x-data="billingSystem()">

    {{-- 1. HEADER BILLING --}}
    <div class="border-4 border-black bg-[#C4B5FD] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tight text-black">💳 Billing & Langganan</h1>
            <p class="font-bold text-slate-800 mt-1">Kelola paket penyimpanan S3 Sandbox Anda dan tingkatkan kuota limit MiniStack.</p>
        </div>
        <div class="border-2 border-black bg-white px-4 py-2 font-black shadow-[3px_3px_black] text-xs uppercase shrink-0">
            Status Akun: <span class="text-emerald-600">Terverifikasi</span>
        </div>
    </div>

    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <div class="border-4 border-black bg-[#34D399] p-4 text-black font-black uppercase text-xs shadow-[4px_4px_black]">
            🎉 {{ session('success') }}
        </div>
    @endif

    {{-- 2. STATUS LANGGANAN AKTIF SAAT INI --}}
    <div class="border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
        <h2 class="text-xl font-black uppercase mb-4">📊 Paket Aktif Saat Ini</h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
            
            <div class="border-4 border-black bg-[#FFB5DA] p-6 shadow-[4px_4px_black] relative overflow-hidden h-full flex flex-col justify-between min-h-[220px]">
                <div>
                    <span class="border-2 border-black bg-white px-2 py-0.5 text-[9px] font-black uppercase tracking-wider shadow-[2px_2px_black]">Current Plan</span>
                    <h3 class="text-3xl font-black uppercase text-black mt-3">{{ $planName }} Sandbox</h3>
                    <p class="font-bold text-slate-800 text-xs mt-1">Rp {{ number_format($planPrice, 0, ',', '.') }} / bln</p>
                </div>
                
                <div class="mt-4 flex items-center justify-between gap-2">
                    <span class="bg-[#34D399] border-2 border-black px-2 py-1 text-[10px] font-black uppercase shadow-[2px_2px_black]">Aktif</span>
                    
                    {{-- TOMBOL CANCEL LANGGANAN (Hanya muncul jika bukan Free) --}}
                    @if($planName !== 'Free' && $activeSubscription)
                        <form action="{{ url('/billing/subscriptions/' . $activeSubscription->id . '/cancel') }}" method="POST" class="m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin membatalkan langganan dan kembali ke Free Tier?')" class="bg-[#FF4545] text-white border-2 border-black px-2 py-1 text-[10px] font-black uppercase shadow-[2px_2px_black] hover:translate-y-0.5 transition-all">
                                ❌ Batalkan Paket
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-2 border-4 border-black bg-slate-50 p-6 shadow-[4px_4px_black] h-full flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-4">Penggunaan Kuota Sandbox Anda</h4>
                    <div class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between text-xs font-black uppercase mb-1.5">
                                <span>Kapasitas Disk ({{ $usedStorageText }} / {{ $storageLimitText }})</span>
                                <span>{{ round($usagePercentage, 1) }}% USED</span>
                            </div>
                            <div class="relative h-14 w-full border-4 border-black bg-black rounded-none flex items-center overflow-hidden">
                                <div class="h-full bg-slate-900 border-r-4 border-dashed border-blue-500" style="width: {{ $usagePercentage }}%;"></div>
                                <div class="absolute flex items-center z-20" style="left: calc({{ min(95, max(1, $usagePercentage)) }}% - 16px); transition: left 0.5s ease-in-out;">
                                    <div class="relative w-8 h-8 flex flex-col justify-center">
                                        <div class="absolute w-8 h-4 top-0 bg-[#FDE047] rounded-t-full origin-bottom animate-[chomp-top_0.25s_infinite_linear] border-t-2 border-l-2 border-r-2 border-black"></div>
                                        <div class="absolute w-8 h-4 bottom-0 bg-[#FDE047] rounded-b-full origin-top animate-[chomp-bottom_0.25s_infinite_linear] border-b-2 border-l-2 border-r-2 border-black"></div>
                                    </div>
                                </div>
                                <div class="absolute inset-0 flex items-center justify-between px-8 pointer-events-none z-10">
                                    @for($i = 0; $i < 12; $i++)
                                        @php $isEaten = $usagePercentage >= (($i / 11) * 100); @endphp
                                        <div class="{{ $i == 11 ? 'w-4 h-4 bg-[#FDE047] animate-ping' : 'w-2.5 h-2.5 bg-[#FDE047]' }} rounded-full border-2 border-black {{ $isEaten ? 'opacity-0' : 'opacity-100' }}"></div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. PILIHAN UPGRADE PAKET (STATIC ARCADES WITH GHOSTS) --}}
    <div class="space-y-6">
        <div class="border-b-4 border-black pb-2">
            <h2 class="text-2xl font-black uppercase text-black">🚀 Tingkatkan Kapasitas Sandbox Anda</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
            
            <div class="border-4 border-black bg-white shadow-[6px_6px_0px_rgba(0,0,0,1)] flex flex-col justify-between p-6 min-h-[360px] {{ $planName === 'Free' ? 'ring-4 ring-[#FFB852] ring-offset-2' : '' }}">
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <span class="border-2 border-black bg-slate-100 px-2 py-0.5 text-[9px] font-black uppercase shadow-[2px_2px_black]">Free Tier</span>
                        <div class="animate-float">
                            <svg class="w-10 h-10 text-[#FFB852]" viewBox="0 0 100 100" fill="currentColor">
                                <path d="M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z" stroke="black" stroke-width="4" /><circle cx="42" cy="48" r="10" fill="white" stroke="black" stroke-width="3" /><circle cx="68" cy="48" r="10" fill="white" stroke="black" stroke-width="3" /><circle cx="39" cy="48" r="4" fill="blue" /><circle cx="65" cy="48" r="4" fill="blue" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-black uppercase text-black">Free Sandbox</h3>
                    <p class="text-xs font-bold text-slate-600">Batas kuota penyimpanan objek dasar Sandbox.</p>
                    <div class="py-4 border-t-2 border-b-2 border-black border-dashed">
                        <p class="text-3xl font-black text-black">Rp 0 <span class="text-xs font-bold text-slate-500">/ selamanya</span></p>
                    </div>
                    <ul class="text-xs font-bold space-y-1 text-slate-700">
                        <li>• Limit Storage: 50 MB</li>
                        <li>• Fitur S3 Sederhana</li>
                    </ul>
                </div>
                <button disabled class="w-full mt-6 border-4 border-black p-3 text-xs font-black uppercase {{ $planName === 'Free' ? 'bg-[#E2E8F0] text-slate-600 cursor-not-allowed' : 'bg-slate-200 text-slate-400 cursor-not-allowed' }}">
                    {{ $planName === 'Free' ? 'Paket Saat Ini' : 'Batas Dasar' }}
                </button>
            </div>

            <div class="border-4 border-black bg-white shadow-[6px_6px_0px_rgba(0,0,0,1)] flex flex-col justify-between p-6 min-h-[360px] {{ $planName === 'Basic' ? 'ring-4 ring-[#FF97C5] ring-offset-2' : '' }}">
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <span class="border-2 border-black bg-slate-100 px-2 py-0.5 text-[9px] font-black uppercase shadow-[2px_2px_black]">Basic Tier</span>
                        <div class="animate-float" style="animation-delay: 0.4s">
                            <svg class="w-10 h-10 text-[#FF97C5]" viewBox="0 0 100 100" fill="currentColor">
                                <path d="M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z" stroke="black" stroke-width="4" /><circle cx="42" cy="48" r="10" fill="white" stroke="black" stroke-width="3" /><circle cx="68" cy="48" r="10" fill="white" stroke="black" stroke-width="3" /><circle cx="39" cy="48" r="4" fill="blue" /><circle cx="65" cy="48" r="4" fill="blue" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-black uppercase text-black">Standard Basic</h3>
                    <p class="text-xs font-bold text-slate-600">Batas kuota penyimpanan bertambah hingga 200 MB.</p>
                    <div class="py-4 border-t-2 border-b-2 border-black border-dashed">
                        <p class="text-3xl font-black text-black">Rp 25.000 <span class="text-xs font-bold text-slate-500">/ bulan</span></p>
                    </div>
                    <ul class="text-xs font-bold space-y-1 text-slate-700">
                        <li>• Limit Storage: 200 MB</li>
                        <li>• Akses Share Presigned Link</li>
                    </ul>
                </div>
                @if($planName === 'Basic')
                    <button disabled class="w-full mt-6 border-4 border-black bg-[#E2E8F0] text-slate-600 p-3 text-xs font-black uppercase cursor-not-allowed">Paket Anda Saat Ini</button>
                @elseif($planName === 'Pro')
                    <button disabled class="w-full mt-6 border-2 border-black bg-slate-200 text-slate-400 p-3 text-xs font-black uppercase cursor-not-allowed">Downgrade Tidak Diperbolehkan</button>
                @else
                    {{-- DIBYPASS: Form submit langsung tanpa modal konfirmasi --}}
                    <form action="{{ url('/billing/subscribe/2') }}" method="POST" class="m-0 w-full mt-6">
                        @csrf
                        <button type="submit" class="w-full border-4 border-black bg-[#FDE047] text-black p-3 text-xs font-black uppercase shadow-[3px_3px_black] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_black] active:translate-y-0.5 transition-all cursor-pointer">
                            Upgrade Ke Basic Instan ⚡
                        </button>
                    </form>
              @endif
            </div>

            <div class="border-4 border-black bg-white shadow-[6px_6px_0px_rgba(0,0,0,1)] flex flex-col justify-between p-6 min-h-[360px] {{ $planName === 'Pro' ? 'ring-4 ring-[#4BE1EC] ring-offset-2' : '' }}">
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <span class="border-2 border-black bg-slate-100 px-2 py-0.5 text-[9px] font-black uppercase shadow-[2px_2px_black]">Premium Tier</span>
                        <div class="animate-float" style="animation-delay: 0.8s">
                            <svg class="w-10 h-10 text-[#4BE1EC]" viewBox="0 0 100 100" fill="currentColor">
                                <path d="M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z" stroke="black" stroke-width="4" /><circle cx="42" cy="48" r="10" fill="white" stroke="black" stroke-width="3" /><circle cx="68" cy="48" r="10" fill="white" stroke="black" stroke-width="3" /><circle cx="39" cy="48" r="4" fill="blue" /><circle cx="65" cy="48" r="4" fill="blue" />
                            </svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-black uppercase text-black">Unlimited Pro</h3>
                    <p class="text-xs font-bold text-slate-600">Batas kapasitas maksimal penampungan berkas Sandbox Developer.</p>
                    <div class="py-4 border-t-2 border-b-2 border-black border-dashed">
                        <p class="text-3xl font-black text-black">Rp 75.000 <span class="text-xs font-bold text-slate-500">/ bulan</span></p>
                    </div>
                    <ul class="text-xs font-bold space-y-1 text-slate-700">
                        <li>• Limit Storage: 1024 MB (1 GB)</li>
                        <li>• Unlimited S3 Bucket Slots</li>
                    </ul>
                </div>
                @if($planName === 'Pro')
                     <button disabled class="w-full mt-6 border-4 border-black bg-[#E2E8F0] text-slate-600 p-3 text-xs font-black uppercase cursor-not-allowed">Paket Anda Saat Ini</button>
                 @else
                <form action="{{ url('/billing/subscribe/3') }}" method="POST" class="m-0 w-full mt-6">
                    @csrf
                    <button type="submit" class="w-full border-4 border-black bg-[#FDE047] text-black p-3 text-xs font-black uppercase shadow-[3px_3px_black] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_black] active:translate-y-0.5 transition-all cursor-pointer">
                        Upgrade Ke Pro Instan ⚡
                    </button>
                </form>
              @endif
            </div>

        </div>
    </div>

    {{-- 4. MODAL CHECKOUT PEMBAYARAN INSTAN --}}
    <div x-show="showCheckout" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
        <div class="w-full max-w-md border-4 border-black bg-white p-6 shadow-[10px_10px_0px_rgba(0,0,0,1)] text-black">
            <div class="flex items-center justify-between border-b-4 border-black pb-3 mb-4">
                <h3 class="text-lg font-black uppercase">🕹️ Sandbox Instant Pay</h3>
                <button @click="showCheckout = false" class="border-2 border-black bg-[#FF4545] p-1 text-white shadow-[1px_1px_black]"><i data-lucide="x" class="h-4 w-4"></i></button>
            </div>
            <div class="space-y-4">
                <div class="border-2 border-black p-4 bg-slate-50 space-y-2">
                    <div class="flex justify-between text-xs font-bold"><span class="text-slate-600">Paket Dituju:</span><span class="font-black animate-pulse" x-text="targetPlan"></span></div>
                    <div class="flex justify-between text-xs font-bold"><span class="text-slate-600">Biaya Simulasi:</span><span class="text-emerald-600 font-black" x-text="targetPrice"></span></div>
                </div>
                <div class="flex gap-3 justify-end">
                    <button @click="showCheckout = false" type="button" class="border-2 border-black bg-white px-4 py-2 text-xs font-black uppercase shadow-[2px_2px_black]">Batal</button>
                    
                    {{-- FORM SUBMIT REAL KE BACKEND UNTUK AKTIVASI INSTAN --}}
                    <form :action="'{{ url('/billing/subscribe') }}/' + targetPlanId" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="border-2 border-black bg-[#34D399] text-black px-4 py-2 text-xs font-black uppercase shadow-[2px_2px_black] hover:translate-y-0.5 transition-all">
                            Bayar & Aktifkan Instan 🚀
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. RIWAYAT TRANSAKSI --}}
    <div class="border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
        <h2 class="text-xl font-black uppercase mb-6">📜 Riwayat Transaksi Sandbox</h2>
        <div class="overflow-x-auto border-4 border-black shadow-[4px_4px_black]">
            <table class="w-full text-left border-collapse font-bold text-xs">
                <thead>
                    <tr class="bg-[#E2E8F0] border-b-4 border-black uppercase tracking-wider">
                        <th class="p-4 border-r-4 border-black">Invoice ID</th>
                        <th class="p-4 border-r-4 border-black">Jenis Paket</th>
                        <th class="p-4 border-r-4 border-black">Tanggal Transaksi</th>
                        <th class="p-4 border-r-4 border-black">Jumlah</th>
                        <th class="p-4">Status / Metode</th>
                    </tr>
                </thead>
                <tbody class="divide-y-4 divide-black">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 border-r-4 border-black font-mono text-purple-700">{{ $inv->invoice_number }}</td>
                            <td class="p-4 border-r-4 border-black">{{ $inv->subscription->plan->plan_name ?? 'N/A' }}</td>
                            <td class="p-4 border-r-4 border-black text-slate-600">{{ $inv->created_at->format('d M Y, H:i') }}</td>
                            <td class="p-4 border-r-4 border-black font-mono">Rp {{ number_format($inv->amount, 0, ',', '.') }}</td>
                            <td class="p-4">
                                @if($inv->status === 'paid')
                                    <span class="bg-[#34D399] border-2 border-black px-2 py-0.5 text-[9px] font-black uppercase shadow-[2px_2px_black] inline-block">LUNAS</span>
                                    <span class="text-[10px] text-slate-500 ml-1 font-mono">({{ $inv->payment_method }})</span>
                                @else
                                    <span class="bg-slate-300 text-slate-600 border-2 border-black px-2 py-0.5 text-[9px] font-black uppercase">BATAL</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-4 text-center text-slate-400">Belum ada riwayat transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function billingSystem() {
        return {
            showCheckout: false,
            targetPlanId: '',
            targetPlan: '',
            targetPrice: '',
            triggerUpgrade(planId, planName, price) {
                this.targetPlanId = planId;
                this.targetPlan = planName;
                this.targetPrice = price;
                this.showCheckout = true;
            }
        }
    }
</script>
@endsection