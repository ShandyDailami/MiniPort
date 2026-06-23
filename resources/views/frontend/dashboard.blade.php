@extends('layout.app')

@section('title', 'Dashboard')

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
    
    /* Animasi hantu melayang naik turun lembut */
    @keyframes float-ghost {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-6px); }
    }
    
    .animate-float-ghost {
        animation: float-ghost 2s ease-in-out infinite;
    }
</style>
@endsection

@section('content')
<div class="flex flex-col gap-10">

    {{-- WIDGET PENGGUNAAN KUOTA BERGAYA LABIRIN PAC-MAN --}}
    <section class="border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none relative overflow-hidden">
        
        <!-- Clyde (Orange Ghost) menghiasi pojok widget penyimpanan -->
        <div class="absolute -top-3 -right-3 opacity-20 pointer-events-none rotate-12">
            <svg class="w-24 h-24 text-[#FFB852]" viewBox="0 0 100 100" fill="currentColor">
                <path d="M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z" stroke="black" stroke-width="4" />
                <circle cx="42" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                <circle cx="68" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                <circle cx="39" cy="48" r="4" fill="blue" />
                <circle cx="65" cy="48" r="4" fill="blue" />
            </svg>
        </div>

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4 relative z-10">
            <div>
                <h2 class="text-2xl font-black uppercase flex items-center gap-2">
                    <span>🕹️ S3 STORAGE ARCADE MAZE</span>
                </h2>
                <p class="font-bold text-slate-700">
                    Paket {{ $planName ?? 'Basic' }} - Sandbox virtual terisolasi
                </p>
            </div>

            <div class="border-4 border-black bg-[#FDE047] px-4 py-2 font-black shadow-[4px_4px_black] w-fit select-none">
                {{ round($usagePercentage ?? 0, 2) }}% FULL
            </div>
        </div>

        <!-- PROGRESS BAR ARCADE SCREEN -->
        <div class="relative h-16 w-full border-4 border-black bg-black rounded-none flex items-center shadow-[inset_0px_4px_0px_rgba(0,0,0,0.3)] select-none">
            
            <!-- Jalur yang sudah dimakan (Eaten Track) -->
            <div class="h-full bg-slate-900 border-r-4 border-dashed border-blue-500" style="width: {{ $usagePercentage ?? 0 }}%;"></div>
            
            <!-- Pac-Man Chomper (Posisinya dinamis mengikuti persentase kapasitas penyimpanan) -->
            <div class="absolute flex items-center z-20" style="left: calc({{ min(95, max(1, $usagePercentage ?? 0)) }}% - 16px); transition: left 0.5s ease-in-out;">
                <div class="relative w-10 h-10 flex flex-col justify-center">
                    <!-- Bagian Mulut Atas -->
                    <div class="absolute w-10 h-5 top-0 bg-[#FDE047] rounded-t-full origin-bottom animate-[chomp-top_0.25s_infinite_linear] border-t-4 border-l-4 border-r-4 border-black"></div>
                    <!-- Bagian Mulut Bawah -->
                    <div class="absolute w-10 h-5 bottom-0 bg-[#FDE047] rounded-b-full origin-top animate-[chomp-bottom_0.25s_infinite_linear] border-b-4 border-l-4 border-r-4 border-black"></div>
                </div>
            </div>

            <!-- Pil Makanan Pac-Man (Akan hilang jika persentase kapasitas penyimpanan melewatinya) -->
            <div class="absolute inset-0 flex items-center justify-between px-8 pointer-events-none z-10">
                @for($i = 0; $i < 12; $i++)
                    @php
                        $dotPercent = ($i / 11) * 100;
                        $isEaten = ($usagePercentage ?? 0) >= $dotPercent;
                    @endphp

                    @if($i == 11)
                        <!-- Power Pellet Berkedip di Ujung Jalur -->
                        <div class="relative w-6 h-6 flex items-center justify-center">
                            <div class="w-6 h-6 rounded-full bg-[#FDE047] border-4 border-black animate-ping absolute"></div>
                            <div class="w-5 h-5 rounded-full bg-[#FDE047] border-4 border-black transition-opacity duration-300 {{ $isEaten ? 'opacity-0' : 'opacity-100' }}"></div>
                        </div>
                    @else
                        <!-- Pil Kecil Biasa -->
                        <div class="w-3 h-3 rounded-full bg-[#FDE047] border-2 border-black transition-opacity duration-300 {{ $isEaten ? 'opacity-0' : 'opacity-100' }}"></div>
                    @endif
                @endfor
            </div>
        </div>

        <!-- STATS CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 relative z-10">
            <div class="border-4 border-black bg-[#E9D5FF] p-4 shadow-[4px_4px_black]">
                <p class="font-black uppercase text-sm">Terpakai</p>
                <p class="text-2xl font-black">{{ $usedStorageText ?? '0 bytes' }}</p>
            </div>

            <div class="border-4 border-black bg-[#67E8F9] p-4 shadow-[4px_4px_black]">
                <p class="font-black uppercase text-sm">Sisa Kuota</p>
                <p class="text-2xl font-black">{{ $remainingStorageText ?? '0 bytes' }}</p>
            </div>

            <div class="border-4 border-black bg-[#FECDD3] p-4 shadow-[4px_4px_black]">
                <p class="font-black uppercase text-sm">Limit Sandbox</p>
                <p class="text-2xl font-black">{{ $storageLimitText ?? '50 MB' }}</p>
            </div>
        </div>
    </section>

    {{-- GLOBAL METRICS (Telah Diperbaiki: Warna Latar Kartu Diselaraskan dengan Maskot Hantunya) --}}
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6 select-none">
        
        <!-- CARD 1: TOTAL BUCKETS (Ditemani Blinky/Hantu Merah - Warna Latar: #FFA3A3) -->
        <div class="border-4 border-black bg-[#FFA3A3] p-6 shadow-[8px_8px_black] flex items-center justify-between overflow-hidden relative">
            <div>
                <p class="font-black uppercase text-sm mb-2 text-black">Total Bucket S3</p>
                <div class="text-5xl font-black text-black">{{ $totalBuckets ?? 0 }}</div>
                <p class="font-bold mt-3 text-xs text-slate-900">Kontainer aktif milik Anda.</p>
            </div>
            <!-- Blinky SVG -->
            <div class="animate-float-ghost shrink-0">
                <svg class="w-16 h-16 text-[#FF3E3E]" viewBox="0 0 100 100" fill="currentColor">
                    <path d="M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z" stroke="black" stroke-width="4" />
                    <circle cx="42" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                    <circle cx="68" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                    <circle cx="39" cy="48" r="4" fill="blue" />
                    <circle cx="65" cy="48" r="4" fill="blue" />
                </svg>
            </div>
        </div>

        <!-- CARD 2: TOTAL OBJECT (Ditemani Pinky/Hantu Pink - Warna Latar: #FFB5DA) -->
        <div class="border-4 border-black bg-[#FFB5DA] p-6 shadow-[8px_8px_black] flex items-center justify-between overflow-hidden relative">
            <div>
                <p class="font-black uppercase text-sm mb-2 text-black">Total Objek</p>
                <div class="text-5xl font-black text-black">{{ $totalObjects ?? 0 }}</div>
                <p class="font-bold mt-3 text-xs text-slate-900">Berkas di seluruh bucket.</p>
            </div>
            <!-- Pinky SVG -->
            <div class="animate-float-ghost shrink-0" style="animation-delay: 0.5s;">
                <svg class="w-16 h-16 text-[#FF97C5]" viewBox="0 0 100 100" fill="currentColor">
                    <path d="M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z" stroke="black" stroke-width="4" />
                    <circle cx="42" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                    <circle cx="68" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                    <circle cx="39" cy="48" r="4" fill="blue" />
                    <circle cx="65" cy="48" r="4" fill="blue" />
                </svg>
            </div>
        </div>

        <!-- CARD 3: CREDENTIAL STATUS (Ditemani Inky/Hantu Biru Muda - Warna Latar: #93EBF2) -->
        <div class="border-4 border-black bg-[#93EBF2] p-6 shadow-[8px_8px_black] flex items-center justify-between overflow-hidden relative">
            <div>
                <p class="font-black uppercase text-sm mb-2 text-black">Status Akses</p>
                <div class="text-3xl font-black text-black">
                    {{ $activeCredential ? 'Aktif' : 'Kosong' }}
                </div>
                <p class="font-bold mt-3 text-xs leading-tight text-slate-900">
                    {{ $activeCredential ? 'Kunci aktif terpasang.' : 'Belum membuat kunci.' }}
                </p>
            </div>
            <!-- Inky SVG -->
            <div class="animate-float-ghost shrink-0" style="animation-delay: 1s;">
                <svg class="w-16 h-16 text-[#4BE1EC]" viewBox="0 0 100 100" fill="currentColor">
                    <path d="M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z" stroke="black" stroke-width="4" />
                    <circle cx="42" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                    <circle cx="68" cy="48" r="10" fill="white" stroke="black" stroke-width="3" />
                    <circle cx="39" cy="48" r="4" fill="blue" />
                    <circle cx="65" cy="48" r="4" fill="blue" />
                </svg>
            </div>
        </div>
    </section>

    {{-- GRID TINDAKAN CEPAT & KREDENSIAL --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {{-- Tindakan Cepat --}}
        <section class="border-4 border-black bg-[#67E8F9] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
            <h2 class="mb-6 text-xl font-black uppercase">⚡ Tindakan Cepat</h2>
            <div class="flex flex-col gap-4">
                <!-- Diperbaiki mengarah ke /bucket/create tunggal -->
                <a href="{{ url('/bucket/create') }}" class="border-4 border-black bg-white text-black px-6 py-4 font-black uppercase text-lg shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all cursor-pointer text-center">
                    + Buat Bucket Baru
                </a>

                <a href="{{ url('/credentials') }}" class="border-4 border-black bg-black text-white px-6 py-4 font-black uppercase text-lg shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all cursor-pointer text-center">
                    🔑 Generate API Key
                </a>
            </div>
        </section>

        {{-- Kredensial Aktif --}}
        <section class="border-4 border-black bg-[#FECDD3] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none flex flex-col justify-between">
            <div>
                <h2 class="mb-6 text-xl font-black uppercase">🔑 Kredensial Aktif</h2>
                @if($activeCredential)
                    <div class="bg-white border-4 border-black p-4 shadow-[4px_4px_black] mb-6">
                        <p class="font-bold uppercase text-sm mb-1 text-slate-600">Access Key ID:</p>
                        <p class="font-mono text-xl font-black text-purple-700 truncate select-all">{{ $activeCredential->access_key }}</p>
                        <span class="inline-block mt-2 bg-[#34D399] border-2 border-black px-2 py-1 text-xs font-black uppercase shadow-[2px_2px_black]">Aktif</span>
                    </div>
                @else
                    <div class="bg-white border-4 border-black p-6 shadow-[4px_4px_black] mb-6 text-center flex flex-col items-center">
                        <div class="text-4xl mb-3">🚫</div>
                        <p class="font-black uppercase text-lg text-slate-900 leading-tight">Tidak Ada Kunci</p>
                        <p class="font-bold text-xs text-slate-700 mt-2">Generate API Key Anda sekarang untuk mulai menghubungkan aplikasi ke MiniStack.</p>
                    </div>
                @endif
            </div>
            <a href="{{ url('/credentials') }}" class="border-4 border-black bg-[#FDE047] text-black px-4 py-3 font-black uppercase shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all cursor-pointer w-full text-center block">
                Lihat Semua Kunci ->
            </a>
        </section>
    </div>

    {{-- BUCKET TERBARU --}}
    <section class="border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <h2 class="text-2xl font-black uppercase">🪣 Bucket Terbaru</h2>

            <a href="{{ url('/buckets') }}" class="border-4 border-black bg-[#FDE047] px-4 py-2 font-black uppercase shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all w-fit">
                Lihat Semua
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($latestBuckets as $bucket)
                <!-- Diperbaiki mengarah ke /bucket/{id} tunggal agar tidak memicu 404 -->
                <a href="{{ url('/bucket/' . $bucket->id) }}"
                class="border-4 border-black bg-[#FDE047] p-4 shadow-[4px_4px_black] hover:-translate-y-2 hover:shadow-[8px_8px_black] transition-all cursor-pointer block">
                    <div class="text-4xl mb-2 select-none">📁</div>
                    <h3 class="font-black text-lg truncate">{{ $bucket->bucket_name }}</h3>
                    <p class="font-bold text-slate-800 text-sm mt-1 border-t-4 border-black pt-2">
                        Region: {{ $bucket->region }}
                    </p>
                    <p class="font-bold text-slate-700 text-xs mt-2">
                        Dibuat: {{ $bucket->created_at->format('d M Y, H:i') }}
                    </p>
                </a>
            @empty
                <div class="col-span-full border-4 border-black bg-[#E9D5FF] p-8 text-center shadow-[4px_4px_black]">
                    <p class="text-2xl font-black uppercase">Belum ada bucket.</p>
                    <p class="font-bold mt-2">Buat bucket pertama untuk mulai memakai Object Storage.</p>

                    <a href="{{ url('/bucket/create') }}" class="inline-block mt-5 border-4 border-black bg-white px-5 py-3 font-black uppercase shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all">
                        + Buat Bucket
                    </a>
                </div>
            @endforelse
        </div>
    </section>

    {{-- LOG AKTIVITAS (AUDIT TRAIL) --}}
    <section class="border-4 border-black bg-[#E2E8F0] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
        <h2 class="mb-6 text-2xl font-black uppercase">📜 Log Aktivitas Terakhir</h2>

        <div class="flex flex-col border-4 border-black divide-y-4 divide-black bg-white font-medium">
            @forelse ($logs as $log)
                <div class="p-4 hover:bg-[#FEF08A] transition-colors flex flex-col sm:flex-row sm:items-start md:items-center justify-between gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <span class="bg-[#1E3A8A] text-white px-2 py-1 font-mono text-sm inline-block w-fit shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] border-2 border-black">
                            {{ $log->created_at->format('H:i A') }}
                        </span>
                        <p class="text-black font-black text-sm">{{ $log->details }}</p>
                    </div>

                    <div class="flex items-center gap-2 self-end sm:self-auto text-xs font-mono font-bold text-slate-600">
                        <span class="bg-slate-200 border-2 border-black px-1.5 py-0.5 rounded-none text-black">
                            🌐 {{ $log->ip_address }}
                        </span>
                        <span class="text-slate-500 font-bold">
                            ({{ $log->created_at->diffForHumans() }})
                        </span>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center bg-white text-slate-500 uppercase font-black tracking-widest">
                    📭 Belum ada riwayat aktivitas di akun Anda.
                </div>
            @endforelse
        </div>
    </section>

</div>
@endsection