@extends('layout.app')

<<<<<<< HEAD
@section('title', 'Billing & Subscriptions')

@section('styles')
<style>
    /* Animasi mengunyah (chomp) mulut Pac-Man atas & bawah */
    @keyframes chomp-top {
        0%, 100% { transform: rotate(0deg); }
        50% { transform: rotate(-40deg); }
    }
    @keyframes chomp-bottom {
        0%, 100% { transform: rotate(0deg); }
        50% { transform: rotate(40deg); }
    }

    /* Animasi hantu melayang retro */
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
<div class="flex flex-col gap-10" x-data="billingSystem()">

    {{-- 1. HEADER BILLING --}}
    <div class="border-4 border-black bg-[#C4B5FD] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black uppercase tracking-tight text-black">💳 Billing & Langganan</h1>
            <p class="font-bold text-slate-800 mt-1">
                Kelola paket penyimpanan S3 Sandbox Anda dan tingkatkan kuota limit MiniStack.
            </p>
        </div>
        
        <div class="border-2 border-black bg-white px-4 py-2 font-black shadow-[3px_3px_black] text-xs uppercase shrink-0">
            Status Akun: <span class="text-emerald-600">Terverifikasi</span>
        </div>
    </div>

    {{-- 2. STATUS LANGGANAN AKTIF SAAT INI --}}
    <div class="border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
        <h2 class="text-xl font-black uppercase mb-4">📊 Paket Aktif Saat Ini</h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-center">
            <!-- Info Utama Paket -->
            <div class="border-4 border-black bg-[#FFB5DA] p-6 shadow-[4px_4px_black] relative overflow-hidden h-full flex flex-col justify-between">
                <div>
                    <span class="border-2 border-black bg-white px-2 py-0.5 text-[9px] font-black uppercase tracking-wider shadow-[2px_2px_black]">
                        Current Plan
                    </span>
                    <h3 class="text-3xl font-black uppercase text-black mt-3">
                        {{ $planName ?? 'Basic' }}
                    </h3>
                    <p class="font-bold text-slate-800 text-xs mt-1">Cocok untuk kebutuhan sandbox developer skala kecil.</p>
                </div>

                <div class="mt-6 border-t-2 border-black pt-4 flex items-center justify-between">
                    <div>
                        <p class="text-[9px] font-black text-slate-600 uppercase">Tarif Langganan</p>
                        <p class="text-lg font-black text-black">Rp 15.000 / bln</p>
                    </div>
                    <span class="bg-[#34D399] border-2 border-black px-2 py-1 text-[10px] font-black uppercase shadow-[2px_2px_black]">
                        Aktif
                    </span>
                </div>
            </div>

            <!-- Detail Alokasi Penyimpanan dengan Pac-Man progress bar -->
            <div class="lg:col-span-2 border-4 border-black bg-slate-50 p-6 shadow-[4px_4px_black] h-full flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-4">Penggunaan Kuota Sandbox Anda</h4>
                    
                    <div class="space-y-4">
                        <!-- Progress Bar Arcade Screen -->
                        <div>
                            <div class="flex items-center justify-between text-xs font-black uppercase mb-1.5">
                                <span>Kapasitas Disk ({{ $usedStorageText ?? '12.5 MB' }} / {{ $storageLimitText ?? '50 MB' }})</span>
                                <span>{{ round($usagePercentage ?? 25, 1) }}% USED</span>
                            </div>

                            <div class="relative h-14 w-full border-4 border-black bg-black rounded-none flex items-center shadow-[inset_0px_4px_0px_rgba(0,0,0,0.3)] select-none overflow-hidden">
                                
                                <!-- Jalur yang sudah dimakan (Eaten Track) -->
                                <div class="h-full bg-slate-900 border-r-4 border-dashed border-blue-500" style="width: {{ $usagePercentage ?? 25 }}%;"></div>
                                
                                <!-- Pac-Man Chomper (Posisinya dinamis mengikuti persentase kapasitas penyimpanan) -->
                                <div class="absolute flex items-center z-20" style="left: calc({{ min(95, max(1, $usagePercentage ?? 25)) }}% - 16px); transition: left 0.5s ease-in-out;">
                                    <div class="relative w-8 h-8 flex flex-col justify-center">
                                        <!-- Bagian Mulut Atas -->
                                        <div class="absolute w-8 h-4 top-0 bg-[#FDE047] rounded-t-full origin-bottom animate-[chomp-top_0.25s_infinite_linear] border-t-2 border-l-2 border-r-2 border-black"></div>
                                        <!-- Bagian Mulut Bawah -->
                                        <div class="absolute w-8 h-4 bottom-0 bg-[#FDE047] rounded-b-full origin-top animate-[chomp-bottom_0.25s_infinite_linear] border-b-2 border-l-2 border-r-2 border-black"></div>
                                    </div>
                                </div>

                                <!-- Pil Makanan Pac-Man (Akan hilang jika dilewati Pac-Man) -->
                                <div class="absolute inset-0 flex items-center justify-between px-8 pointer-events-none z-10">
                                    @for($i = 0; $i < 12; $i++)
                                        @php
                                            $dotPercent = ($i / 11) * 100;
                                            $isEaten = ($usagePercentage ?? 25) >= $dotPercent;
                                        @endphp

                                        @if($i == 11)
                                            <!-- Power Pellet Berkedip di Ujung Jalur -->
                                            <div class="relative w-5 h-5 flex items-center justify-center">
                                                <div class="w-5 h-5 rounded-full bg-[#FDE047] border-2 border-black animate-ping absolute"></div>
                                                <div class="w-4 h-4 rounded-full bg-[#FDE047] border-2 border-black transition-opacity duration-300 {{ $isEaten ? 'opacity-0' : 'opacity-100' }}"></div>
                                            </div>
                                        @else
                                            <!-- Pil Kecil Biasa -->
                                            <div class="w-2.5 h-2.5 rounded-full bg-[#FDE047] border-2 border-black transition-opacity duration-300 {{ $isEaten ? 'opacity-0' : 'opacity-100' }}"></div>
                                        @endif
                                    @endfor
                                </div>

                            </div>
                        </div>

                        <!-- Limit Detil -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="border-2 border-black bg-white p-3 shadow-[2px_2px_black]">
                                <p class="text-[9px] font-black text-slate-400 uppercase">Batas Maksimal S3 Buckets</p>
                                <p class="text-lg font-black text-black">5 Buckets</p>
                            </div>
                            <div class="border-2 border-black bg-white p-3 shadow-[2px_2px_black]">
                                <p class="text-[9px] font-black text-slate-400 uppercase">Ukuran Maksimal Per File</p>
                                <p class="text-lg font-black text-black">10 Megabytes</p>
                            </div>
                        </div>
                    </div>
                </div>

                <p class="text-[10px] font-bold text-slate-500 mt-4 leading-relaxed">
                    * Pembayaran berikutnya akan didebit secara otomatis pada tanggal <span class="text-black font-black">01 Juli 2026</span> melalui saldo dompet virtual sandbox Anda.
                </p>
            </div>
        </div>
    </div>

    {{-- 3. PILIHAN UPGRADE PAKET (UPGRADE PLANS GRID) --}}
    <div class="space-y-6">
        <div class="border-b-4 border-black pb-2">
            <h2 class="text-2xl font-black uppercase text-black">🚀 Tingkatkan Kapasitas Sandbox Anda</h2>
            <p class="font-bold text-slate-600 text-xs">Pilih paket penyimpanan di bawah ini untuk menambah kuota dan fungsionalitas S3.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
            
            <!-- PLAN 1: FREE TIER (Warna: #E2E8F0 - Ditemani Clyde si Hantu Oranye) -->
            <div class="border-4 border-black bg-white shadow-[6px_6px_0px_rgba(0,0,0,1)] flex flex-col justify-between p-6 rounded-none relative overflow-hidden min-h-[380px]">
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <span class="border-2 border-black bg-[#E2E8F0] px-2 py-0.5 text-[9px] font-black uppercase shadow-[2px_2px_black]">
                            Free Tier
                        </span>
                        <div class="animate-float">
                            <!-- Clyde SVG -->
                            <svg class="w-10 h-10 text-[#FFB852]" viewBox="0 0 100 100" fill="currentColor">
                                <path d="M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z" stroke="black" stroke-width="4" />
                                <circle cx="42" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                                <circle cx="68" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                                <circle cx="39" cy="48" r="4" fill="blue" />
                                <circle cx="65" cy="48" r="4" fill="blue" />
                            </svg>
                        </div>
                    </div>
                    
                    <h3 class="text-2xl font-black uppercase text-black">Free Sandbox</h3>
                    <p class="text-xs font-bold text-slate-600 leading-normal">Paket dasar gratisan untuk menguji coba fungsi S3 standar secara kasual.</p>
                    
                    <div class="py-4 border-t-2 border-b-2 border-black border-dashed">
                        <p class="text-3xl font-black text-black">Rp 0 <span class="text-xs font-bold text-slate-500">/ selamanya</span></p>
                    </div>

                    <ul class="space-y-2 text-xs font-bold text-slate-800">
                        <li class="flex items-center gap-1.5">🟢 Batas Kuota: 10 MB</li>
                        <li class="flex items-center gap-1.5">🟢 Maksimal 2 Buckets</li>
                        <li class="flex items-center gap-1.5">❌ No Presigned Share Links</li>
                    </ul>
                </div>

                <button disabled class="w-full mt-6 border-2 border-black bg-slate-200 text-slate-400 p-3 text-xs font-black uppercase cursor-not-allowed">
                    Downgrade Tidak Diperbolehkan
                </button>
            </div>

            <!-- PLAN 2: BASIC TIER (Warna: #FFB5DA - Pinky) -->
            <div class="border-4 border-black bg-white shadow-[6px_6px_0px_rgba(0,0,0,1)] flex flex-col justify-between p-6 rounded-none relative overflow-hidden min-h-[380px] ring-4 ring-[#FFB5DA] ring-offset-2">
                <div class="absolute top-0 right-0 bg-[#FFB5DA] border-b-2 border-l-2 border-black px-3 py-1 text-[8px] font-black uppercase tracking-widest shadow-[2px_2px_rgba(0,0,0,1)]">
                    Paket Aktif
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <span class="border-2 border-black bg-[#FFB5DA] px-2 py-0.5 text-[9px] font-black uppercase shadow-[2px_2px_black]">
                            Basic Tier
                        </span>
                        <div class="animate-float" style="animation-delay: 0.5s;">
                            <!-- Pinky SVG -->
                            <svg class="w-10 h-10 text-[#FF97C5]" viewBox="0 0 100 100" fill="currentColor">
                                <path d="M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z" stroke="black" stroke-width="4" />
                                <circle cx="42" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                                <circle cx="68" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                                <circle cx="39" cy="48" r="4" fill="blue" />
                                <circle cx="65" cy="48" r="4" fill="blue" />
                            </svg>
                        </div>
                    </div>
                    
                    <h3 class="text-2xl font-black uppercase text-black">Standard Basic</h3>
                    <p class="text-xs font-bold text-slate-600 leading-normal">Paket standar yang sedang Anda gunakan saat ini untuk kebutuhan development umum.</p>
                    
                    <div class="py-4 border-t-2 border-b-2 border-black border-dashed">
                        <p class="text-3xl font-black text-black">Rp 15.000 <span class="text-xs font-bold text-slate-500">/ bulan</span></p>
                    </div>

                    <ul class="space-y-2 text-xs font-bold text-slate-800">
                        <li class="flex items-center gap-1.5">🟢 Batas Kuota: 50 MB</li>
                        <li class="flex items-center gap-1.5">🟢 Maksimal 5 Buckets</li>
                        <li class="flex items-center gap-1.5">🟢 Presigned Share Links Aktif</li>
                    </ul>
                </div>

                <button disabled class="w-full mt-6 border-4 border-black bg-[#E2E8F0] text-slate-600 p-3 text-xs font-black uppercase cursor-not-allowed">
                    Paket Anda Saat Ini
                </button>
            </div>

            <!-- PLAN 3: PREMIUM TIER (Warna: #93EBF2 - Inky) -->
            <div class="border-4 border-black bg-white shadow-[6px_6px_0px_rgba(0,0,0,1)] hover:shadow-[10px_10px_0px_rgba(0,0,0,1)] hover:-translate-y-1 transition-all flex flex-col justify-between p-6 rounded-none relative overflow-hidden min-h-[380px]">
                <div class="space-y-4">
                    <div class="flex items-start justify-between">
                        <span class="border-2 border-black bg-[#93EBF2] px-2 py-0.5 text-[9px] font-black uppercase shadow-[2px_2px_black]">
                            Premium Tier
                        </span>
                        <div class="animate-float" style="animation-delay: 1s;">
                            <!-- Inky SVG -->
                            <svg class="w-10 h-10 text-[#4BE1EC]" viewBox="0 0 100 100" fill="currentColor">
                                <path d="M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z" stroke="black" stroke-width="4" />
                                <circle cx="42" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                                <circle cx="68" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                                <circle cx="39" cy="48" r="4" fill="blue" />
                                <circle cx="65" cy="48" r="4" fill="blue" />
                            </svg>
                        </div>
                    </div>
                    
                    <h3 class="text-2xl font-black uppercase text-black">Unlimited Pro</h3>
                    <p class="text-xs font-bold text-slate-600 leading-normal">Tingkatkan performa ke kapasitas penyimpanan tak terbatas demi integrasi sistem masif.</p>
                    
                    <div class="py-4 border-t-2 border-b-2 border-black border-dashed">
                        <p class="text-3xl font-black text-black">Rp 45.000 <span class="text-xs font-bold text-slate-500">/ bulan</span></p>
                    </div>

                    <ul class="space-y-2 text-xs font-bold text-slate-800">
                        <li class="flex items-center gap-1.5">🟢 Batas Kuota: 500 MB</li>
                        <li class="flex items-center gap-1.5">🟢 Unlimited S3 Buckets</li>
                        <li class="flex items-center gap-1.5">🟢 Prioritas Bandwidth API Tinggi</li>
                    </ul>
                </div>

                <button 
                    @click="triggerUpgrade('Unlimited Pro', 'Rp 45.000')"
                    type="button" 
                    class="w-full mt-6 border-4 border-black bg-[#FDE047] text-black p-3 text-xs font-black uppercase shadow-[3px_3px_black] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[2px_2px_black] active:translate-y-0.5 transition-all cursor-pointer"
                >
                    Upgrade Paket Sekarang ->
                </button>
            </div>

        </div>
    </div>

    {{-- 4. SIMULASI MODAL CHECKOUT PEMBAYARAN RETRO (Alpine.js) --}}
    <div x-show="showCheckout" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
        <div class="w-full max-w-md border-4 border-black bg-white p-6 shadow-[10px_10px_0px_rgba(0,0,0,1)] rounded-none relative text-black">
            
            <div class="flex items-center justify-between border-b-4 border-black pb-3 mb-4">
                <h3 class="text-lg font-black uppercase flex items-center gap-2">
                    <span>🛒 Konfirmasi Upgrade</span>
                </h3>
                <button @click="showCheckout = false" class="border-2 border-black bg-[#FF4545] p-1 text-white shadow-[1px_1px_black] hover:translate-y-0.5 transition-all">
                    <i data-lucide="x" class="h-4 w-4 stroke-[3]"></i>
                </button>
            </div>

            <div class="space-y-4">
                <div class="border-2 border-black bg-amber-50 p-4 font-bold text-xs leading-relaxed text-amber-900 mb-4">
                    ⚠️ **Simulasi Sandbox:** Ini adalah modul pembayaran virtual sandbox terintegrasi untuk menguji sistem penagihan MiniStack S3 lokal Anda.
                </div>

                <div class="border-2 border-black p-4 bg-slate-50 space-y-2">
                    <div class="flex justify-between text-xs font-bold text-slate-600">
                        <span>Paket Baru:</span>
                        <span class="text-black font-black" x-text="targetPlan"></span>
                    </div>
                    <div class="flex justify-between text-xs font-bold text-slate-600">
                        <span>Tarif Tagihan:</span>
                        <span class="text-black font-black" x-text="targetPrice + ' / bulan'"></span>
                    </div>
                    <div class="border-t-2 border-black pt-2 flex justify-between text-sm font-black">
                        <span>Total Bayar:</span>
                        <span class="text-emerald-600" x-text="targetPrice"></span>
                    </div>
                </div>

                <div class="flex gap-3 justify-end pt-2">
                    <button @click="showCheckout = false" type="button" class="border-2 border-black bg-white px-4 py-2 text-xs font-black uppercase shadow-[2px_2px_black]">
                        Batal
                    </button>
                    
                    <button 
                        @click="executeUpgrade()" 
                        type="button" 
                        class="border-2 border-black bg-[#34D399] text-black px-4 py-2 text-xs font-black uppercase shadow-[2px_2px_black] hover:translate-y-0.5 transition-all"
                    >
                        Proses Pembayaran 🚀
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. RIWAYAT TRANSAKSI / BILLING HISTORY --}}
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
                    </tr>
                </thead>
                <tbody class="divide-y-4 divide-black">
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 border-r-4 border-black font-mono text-purple-700">INV-MNS-9938</td>
                        <td class="p-4 border-r-4 border-black">Standard Basic</td>
                        <td class="p-4 border-r-4 border-black">Sandbox Wallet</td>
                        <td class="p-4 border-r-4 border-black text-slate-600">01 Juni 2026, 09:30</td>
                        <td class="p-4 border-r-4 border-black font-mono">Rp 15.000</td>
                        <td class="p-4">
                            <span class="bg-[#34D399] border-2 border-black px-2 py-0.5 text-[9px] font-black uppercase shadow-[2px_2px_black] inline-block">
                                LUNAS
                            </span>
                        </td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="p-4 border-r-4 border-black font-mono text-purple-700">INV-MNS-8841</td>
                        <td class="p-4 border-r-4 border-black">Free Sandbox</td>
                        <td class="p-4 border-r-4 border-black">Free Tier Registration</td>
                        <td class="p-4 border-r-4 border-black text-slate-600">12 Mei 2026, 14:15</td>
                        <td class="p-4 border-r-4 border-black font-mono">Rp 0</td>
                        <td class="p-4">
                            <span class="bg-[#34D399] border-2 border-black px-2 py-0.5 text-[9px] font-black uppercase shadow-[2px_2px_black] inline-block">
                                LUNAS
                            </span>
                        </td>
                    </tr>
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
            targetPlan: '',
            targetPrice: '',
            
            triggerUpgrade(planName, price) {
                this.targetPlan = planName;
                this.targetPrice = price;
                this.showCheckout = true;
            },

            executeUpgrade() {
                this.showCheckout = false;
                
                // Trigger global toast sukses dari layout/app.blade.php
                window.dispatchEvent(new CustomEvent('toast-trigger', {
                    detail: {
                        type: 'success',
                        title: 'Pembayaran Sukses!',
                        message: 'Selamat! Akun Anda berhasil di-upgrade ke paket ' + this.targetPlan + ' fiktif.'
                    }
                }));
            }
        }
    }
</script>
=======
@section('title', 'Billing - MiniPort Cloud')

@section('content')
<div class="min-h-screen bg-[#FDFBF7] p-6 md:p-10 text-black">

    <div class="max-w-7xl mx-auto">

        <div class="mb-8">
            <a href="/"
               class="inline-block border-4 border-black bg-white px-4 py-2 font-black uppercase shadow-[4px_4px_black]">
                &larr; Dashboard
            </a>
        </div>

        <div class="mb-10 border-b-4 border-black pb-6">
            <h1 class="text-4xl font-black uppercase">💳 Billing & Subscription</h1>
            <p class="font-bold text-slate-700 mt-2">
                Pilih paket Object Storage dan kelola invoice MiniPort.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 border-4 border-black bg-green-400 p-4 font-black shadow-[4px_4px_black]">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 border-4 border-black bg-red-500 text-white p-4 font-black shadow-[4px_4px_black]">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <section class="mb-10 border-4 border-black bg-[#C4B5FD] p-6 shadow-[8px_8px_black]">
            <h2 class="text-2xl font-black uppercase mb-4">Paket Aktif</h2>

            @if($activeSubscription)
                <div class="bg-white border-4 border-black p-5 shadow-[4px_4px_black]">
                    <p class="text-3xl font-black">
                        {{ $activeSubscription->plan->plan_name }}
                    </p>

                    <p class="font-bold mt-2">
                        Limit:
                        {{ number_format($activeSubscription->plan->storage_limit_mb) }} MB
                    </p>

                    <p class="font-bold">
                        Aktif sampai:
                        {{ $activeSubscription->end_date->format('d M Y') }}
                    </p>

                    <form
                        action="/billing/subscriptions/{{ $activeSubscription->id }}/cancel"
                        method="POST"
                        class="mt-5"
                        onsubmit="return confirm('Batalkan subscription aktif?');"
                    >
                        @csrf
                        @method('PATCH')

                        <button class="border-4 border-black bg-red-500 text-white px-4 py-2 font-black uppercase shadow-[4px_4px_black]">
                            Batalkan Subscription
                        </button>
                    </form>
                </div>
            @else
                <p class="font-black">
                    Belum ada subscription aktif. Limit fallback masih mengikuti konfigurasi default.
                </p>
            @endif
        </section>

        <section class="mb-12">
            <h2 class="text-3xl font-black uppercase mb-6">Pilih Paket</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($plans as $plan)
                    <div class="border-4 border-black bg-white p-6 shadow-[8px_8px_black]">
                        <h3 class="text-3xl font-black uppercase">
                            {{ $plan->plan_name }}
                        </h3>

                        <p class="text-4xl font-black mt-5">
                            @if((float) $plan->price <= 0)
                                Gratis
                            @else
                                Rp{{ number_format($plan->price, 0, ',', '.') }}
                            @endif
                        </p>

                        <p class="font-bold text-lg mt-4">
                            Storage:
                            {{ number_format($plan->storage_limit_mb) }} MB
                        </p>

                        <p class="font-bold text-slate-600 mt-2">
                            Masa aktif 30 hari.
                        </p>

                        <form
                            action="/billing/subscribe/{{ $plan->id }}"
                            method="POST"
                            class="mt-6"
                        >
                            @csrf

                            <button class="w-full border-4 border-black bg-[#34D399] px-5 py-3 font-black uppercase shadow-[4px_4px_black]">
                                Pilih Paket
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="border-4 border-black bg-white p-6 shadow-[8px_8px_black]">
            <h2 class="text-3xl font-black uppercase mb-6">Riwayat Invoice</h2>

            <div class="overflow-x-auto">
                <table class="w-full border-4 border-black">
                    <thead class="bg-[#FDE047]">
                        <tr class="border-b-4 border-black">
                            <th class="p-4 text-left">Invoice</th>
                            <th class="p-4 text-left">Paket</th>
                            <th class="p-4 text-left">Jumlah</th>
                            <th class="p-4 text-left">Status</th>
                            <th class="p-4 text-left">Jatuh Tempo</th>
                            <th class="p-4 text-left">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr class="border-b-2 border-black">
                                <td class="p-4 font-mono font-bold">
                                    {{ $invoice->invoice_number }}
                                </td>

                                <td class="p-4 font-bold">
                                    {{ $invoice->subscription?->plan?->plan_name ?? '-' }}
                                </td>

                                <td class="p-4 font-bold">
                                    Rp{{ number_format($invoice->amount, 0, ',', '.') }}
                                </td>

                                <td class="p-4">
                                    <span class="border-2 border-black px-3 py-1 font-black uppercase
                                        {{ $invoice->status === 'paid'
                                            ? 'bg-green-400'
                                            : ($invoice->status === 'pending'
                                                ? 'bg-yellow-300'
                                                : 'bg-slate-300') }}">
                                        {{ $invoice->status }}
                                    </span>
                                </td>

                                <td class="p-4 font-bold">
                                    {{ $invoice->due_date->format('d M Y') }}
                                </td>

                                <td class="p-4">
                                    @if($invoice->status === 'pending')
                                        <form
                                            action="/billing/invoices/{{ $invoice->id }}/pay"
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
                                <td colspan="6" class="p-8 text-center font-black uppercase">
                                    Belum ada invoice.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</div>
>>>>>>> 831419a3e8d78f62ad59c30c48981e320487cfa0
@endsection