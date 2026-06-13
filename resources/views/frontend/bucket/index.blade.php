@extends('layout.app')

@section('title', 'S3 Buckets')

@section('styles')
<style>
    /* Animasi hantu melayang naik turun lembut */
    @keyframes float-ghost {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-8px) rotate(2deg); }
    }
    
    .animate-float-ghost {
        animation: float-ghost 2.5s ease-in-out infinite;
    }

    /* Scanline overlay retro */
    .scanlines {
        background: linear-gradient(
            rgba(18, 16, 16, 0) 50%, 
            rgba(0, 0, 0, 0.15) 50%
        ), linear-gradient(
            90deg, 
            rgba(255, 0, 0, 0.03), 
            rgba(0, 255, 0, 0.01), 
            rgba(0, 0, 255, 0.03)
        );
        background-size: 100% 4px, 3px 100%;
    }
</style>
@endsection

@section('content')
<div class="flex flex-col gap-10">

    {{-- 1. RETRO ARCADE HEADER HALAMAN --}}
    <div class="border-4 border-black bg-[#C4B5FD] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none flex flex-col md:flex-row md:items-center justify-between gap-6 relative overflow-hidden">
        
        <!-- Background Grid Retro Accent -->
        <div class="absolute inset-0 opacity-10 pointer-events-none scanlines"></div>

        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-1">
                <span class="bg-black text-[#FDE047] px-2 py-0.5 text-[10px] font-black uppercase tracking-widest border-2 border-black">
                    S3 STAGE
                </span>
            </div>
            <h1 class="text-3xl font-black uppercase tracking-tight text-black">🪣 S3 Buckets Storage</h1>
            <p class="font-bold text-slate-800 mt-1 max-w-2xl">
                Kelola kontainer objek virtual Anda di server MiniStack S3. Setiap bucket bertindak sebagai partisi terisolasi.
            </p>
        </div>
        
        <a href="{{ url('/bucket/create') }}" class="border-4 border-black bg-[#FDE047] text-black px-6 py-4 font-black uppercase shadow-[6px_6px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[4px_4px_0px_rgba(0,0,0,1)] active:translate-x-[4px] active:translate-y-[4px] active:shadow-none transition-all text-center block w-full md:w-fit shrink-0 relative z-10">
            + Buat Bucket Baru
        </a>
    </div>

    {{-- 2. GRID BUCKETS (Retro Folder 3D isometric cards coordinate with ghosts) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($buckets as $index => $bucket)
            @php
                // Tentukan data tema berdasarkan index untuk keselarasan dengan para hantu Pac-Man
                $themes = [
                    [
                        'name' => 'Blinky',
                        'bg' => '#FFA3A3',
                        'text' => '#000000',
                        'accent' => '#FF3E3E',
                        'path' => 'M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z'
                    ],
                    [
                        'name' => 'Pinky',
                        'bg' => '#FFB5DA',
                        'text' => '#000000',
                        'accent' => '#FF97C5',
                        'path' => 'M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z'
                    ],
                    [
                        'name' => 'Inky',
                        'bg' => '#93EBF2',
                        'text' => '#000000',
                        'accent' => '#4BE1EC',
                        'path' => 'M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z'
                    ],
                    [
                        'name' => 'Clyde',
                        'bg' => '#FFD29D',
                        'text' => '#000000',
                        'accent' => '#FFB852',
                        'path' => 'M20 50 C20 25, 80 25, 80 50 L80 85 L70 75 L60 85 L50 75 L40 85 L30 75 L20 85 Z'
                    ],
                    [
                        'name' => 'Pacman',
                        'bg' => '#FDE047',
                        'text' => '#000000',
                        'accent' => '#EAB308',
                        'path' => 'pacman' // Khusus untuk Pac-Man SVG
                    ]
                ];
                
                $currentTheme = $themes[$index % count($themes)];
            @endphp

            <div class="border-4 border-black bg-white shadow-[8px_8px_0px_rgba(0,0,0,1)] hover:-translate-y-2 hover:shadow-[12px_12px_0px_rgba(0,0,0,1)] transition-all flex flex-col justify-between min-h-[280px] rounded-none relative overflow-hidden group">
                
                {{-- Retro Folder Tab Accent --}}
                <div class="absolute top-0 left-0 w-28 h-7 border-b-4 border-r-4 border-black flex items-center justify-center font-black text-[9px] uppercase tracking-wider" style="background-color: {{ $currentTheme['bg'] }};">
                    {{ $currentTheme['name'] }} Zone
                </div>
                
                <div class="p-6 pt-12">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="text-5xl select-none group-hover:scale-110 transition-transform">📁</div>
                        
                        <!-- Floating Ghost Mascot inside each bucket card -->
                        <div class="animate-float-ghost shrink-0" style="animation-delay: {{ $index * 0.3 }}s;">
                            @if($currentTheme['path'] === 'pacman')
                                <!-- Pac-Man SVG -->
                                <svg class="w-12 h-12" viewBox="0 0 100 100" fill="{{ $currentTheme['accent'] }}">
                                    <circle cx="50" cy="50" r="40" stroke="black" stroke-width="5" />
                                    <!-- Pacman mouth line -->
                                    <path d="M50 50 L85 30 A40 40 0 0 1 85 70 Z" fill="white" stroke="black" stroke-width="4" />
                                    <circle cx="45" cy="28" r="5" fill="black" />
                                </svg>
                            @else
                                <!-- Ghost SVG -->
                                <svg class="w-12 h-12" style="color: {{ $currentTheme['accent'] }};" viewBox="0 0 100 100" fill="currentColor">
                                    <path d="{{ $currentTheme['path'] }}" stroke="black" stroke-width="4" />
                                    <circle cx="42" cy="48" r="8" fill="white" stroke="black" stroke-width="2" />
                                    <circle cx="68" cy="48" r="8" fill="white" stroke="black" stroke-width="2" />
                                    <circle cx="39" cy="48" r="3.5" fill="blue" />
                                    <circle cx="65" cy="48" r="3.5" fill="blue" />
                                </svg>
                            @endif
                        </div>
                    </div>

                    <h3 class="font-black text-xl text-black truncate select-all" title="{{ $bucket->bucket_name }}">
                        {{ $bucket->bucket_name }}
                    </h3>
                    
                    <div class="flex items-center gap-2 mt-2">
                        <span class="border-2 border-black bg-slate-100 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider">
                            Region: {{ $bucket->region }}
                        </span>
                    </div>
                </div>

                {{-- Action Panel at the Bottom of Card --}}
                <div class="border-t-4 border-black bg-slate-50 p-4 flex flex-col gap-3">
                    <div class="flex items-center justify-between text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        <span>MiniStack Console</span>
                        <span>{{ $bucket->created_at->format('d M Y') }}</span>
                    </div>

                    <div class="flex gap-3">
                        <!-- Diperbaiki mengarah ke /bucket/{id} (tunggal) sesuai routes/web.php -->
                        <a href="{{ url('/bucket/' . $bucket->id) }}" class="flex-1 border-2 border-black bg-[#34D399] text-black text-center py-2 text-xs font-black uppercase shadow-[2px_2px_black] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[1px_1px_black] active:translate-y-0.5 active:shadow-none transition-all">
                            Buka File Manager ->
                        </a>

                        {{-- Hapus Bucket dengan Konfirmasi Alpine.js --}}
                        <div x-data="{ openConfirm: false }">
                            <button @click="openConfirm = true" type="button" class="border-2 border-black bg-[#FF4545] text-white p-2 text-xs font-black shadow-[2px_2px_black] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[1px_1px_black] active:translate-y-0.5 active:shadow-none transition-all">
                                <i data-lucide="trash-2" class="h-4 w-4 stroke-[2.5]"></i>
                            </button>

                            <!-- Danger Modal Konfirmasi Retro -->
                            <div x-show="openConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
                                <div class="w-full max-w-md border-4 border-black bg-[#FF4545] text-white p-6 shadow-[8px_8px_black] rounded-none">
                                    <h3 class="text-lg font-black uppercase border-b-2 border-black pb-2 mb-3 text-white">🚨 Hapus S3 Bucket?</h3>
                                    <p class="text-xs font-bold leading-relaxed mb-4 text-white/90 text-left">
                                        Tindakan ini akan menghapus bucket <span class="font-mono bg-black/20 px-1 py-0.5 rounded text-yellow-200 font-bold">{{ $bucket->bucket_name }}</span> beserta seluruh data file di dalamnya secara permanen dari server MiniStack!
                                    </p>
                                    <div class="flex justify-end gap-3">
                                        <button @click="openConfirm = false" type="button" class="border-2 border-black bg-white text-black px-4 py-2 text-xs font-black uppercase shadow-[2px_2px_black]">
                                            Batal
                                        </button>
                                        <!-- Diperbaiki mengarah ke /bucket/{id} (tunggal) sesuai routes/web.php -->
                                        <form action="{{ url('/bucket/' . $bucket->id) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="border-2 border-black bg-black text-white px-4 py-2 text-xs font-black uppercase shadow-[2px_2px_black] hover:translate-y-0.5 transition-all">
                                                Ya, Hapus Semua
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @empty
            {{-- TAMPILAN JIKA BELUM ADA BUCKET --}}
            <div class="col-span-full border-4 border-black bg-[#FECDD3] p-12 text-center shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none relative overflow-hidden">
                <div class="absolute inset-0 opacity-5 pointer-events-none scanlines"></div>
                
                <span class="text-6xl block mb-4 animate-bounce">📭</span>
                <h3 class="text-2xl font-black uppercase">Belum Ada S3 Bucket</h3>
                <p class="font-bold text-slate-800 mt-2 max-w-lg mx-auto">
                    Wadah penyimpanan Anda masih kosong. Buat bucket pertama Anda untuk mengaktifkan fitur upload data/file ke MiniStack S3.
                </p>
                <a href="{{ url('/bucket/create') }}" class="inline-block mt-6 border-4 border-black bg-white text-black px-6 py-3 font-black uppercase shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all">
                    + Buat Bucket Sekarang
                </a>
            </div>
        @endforelse
    </div>

</div>
@endsection