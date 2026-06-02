@extends('layout.app')

@section('title', 'Dashboard')

@section('content')
{{-- LATAR BELAKANG: Sedikit lebih putih tulang agar warna dalam box lebih "pop" --}}
<div class="h-screen bg-[#FDFBF7] font-sans text-black flex flex-col overflow-hidden">

    {{-- HEADER: Ganti ke warna Lavender/Ungu Muda yang mencolok tapi lembut --}}
    <header class="w-full flex items-center justify-between border-b-4 border-black bg-[#C4B5FD] px-6 py-4 shadow-[0px_4px_0px_0px_rgba(0,0,0,1)] relative z-20">
        <h1 class="text-2xl font-extrabold uppercase tracking-tight text-black">📦 Miniport Cloud</h1>
        <div class="flex items-center gap-6 font-medium">
            <p class="hidden sm:block border-2 border-black bg-white px-3 py-1 shadow-[2px_2px_black] font-bold">
                👤 {{ Auth::user()->name ?? 'Profile' }}
            </p>
            <form action="/logout" method="POST" class="m-0">
                @csrf
                {{-- Tombol Logout: Merah terang khas Neobrutalism --}}
                <button class="border-2 border-black bg-[#FF4545] text-white px-4 py-2 font-bold shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all cursor-pointer">
                    Logout
                </button>
            </form>
        </div>
    </header>

    {{-- MAIN LAYOUT --}}
    <div class="flex flex-1 flex-col md:flex-row w-full relative z-10 overflow-hidden">

        {{-- SIDEBAR NAVIGASI --}}
        <aside class="w-full md:w-64 h-full flex-none border-r-4 border-black bg-white p-6 shadow-[4px_0px_0px_0px_rgba(0,0,0,1)] relative z-10 overflow-y-auto">
            <h2 class="mb-6 text-xl font-black uppercase text-slate-900 tracking-widest border-b-4 border-black pb-2">Navigasi</h2>
            <nav class="flex flex-col gap-4">
                {{-- Menu Aktif: Kuning Terang (Yellow 400) --}}
                <a href="#" class="border-4 border-black bg-[#FDE047] px-4 py-3 font-black uppercase shadow-[4px_4px_black] flex items-center justify-between hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all">
                    <span>Overview</span>
                    <span class="text-xl leading-none">></span>
                </a>
                <a href="#" class="border-4 border-transparent px-4 py-3 font-bold uppercase text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50 transition-all">
                    Buckets
                </a>
                <a href="#" class="border-4 border-transparent px-4 py-3 font-bold uppercase text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50 transition-all">
                    API Keys
                </a>
                <a href="#" class="border-4 border-transparent px-4 py-3 font-bold uppercase text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50 transition-all">
                    Billing
                </a>
                <a href="#" class="border-4 border-transparent px-4 py-3 font-bold uppercase text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50 transition-all">
                    Settings
                </a>
            </nav>
        </aside>

        {{-- CONTENT AREA --}}
        <main class="flex-1 h-full p-6 md:p-10 flex flex-col gap-10 overflow-y-auto bg-slate-50">

            {{-- WIDGET PENGGUNAAN KUOTA --}}
            <section class="border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-sm">
                <h2 class="mb-4 text-2xl font-black uppercase">💾 Penggunaan Kuota (Paket {{ $planName ?? 'Free' }})</h2>
                <div class="h-10 w-full border-4 border-black bg-slate-100 rounded-sm overflow-hidden flex shadow-[inset_0px_4px_0px_rgba(0,0,0,0.1)] relative">
                    {{-- Bar Kuota: Hijau Neon (Emerald 400) jika aman, Merah jika penuh --}}
                    <div
                        class="h-full border-r-4 border-black flex items-center justify-end px-2 transition-all duration-500 {{ ($usagePercentage ?? 0) > 80 ? 'bg-[#FF4545]' : 'bg-[#34D399]' }}"
                        style="width: {{ $usagePercentage ?? 0 }}%;"
                    >
                        <span class="text-black font-black text-sm pr-1">
                            {{ round($usagePercentage ?? 0, 1) }}%
                        </span>
                    </div>
                </div>
                <p class="mt-4 font-bold text-slate-800 bg-[#E2E8F0] border-2 border-black inline-block px-3 py-1 shadow-[2px_2px_black]">
                    {{ $usedStorageGB ?? 0 }} GB terpakai dari total {{ $totalStorageGB ?? 0 }} GB
                </p>
            </section>

            {{-- GRID TINDAKAN CEPAT & KREDENSIAL --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

                {{-- Tindakan Cepat: Biru Cyan (Cyan 300) --}}
                <section class="border-4 border-black bg-[#67E8F9] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-sm">
                    <h2 class="mb-6 text-xl font-black uppercase">⚡ Tindakan Cepat</h2>
                    <div class="flex flex-col gap-4">
                        <button id="newBucket" class="border-4 border-black bg-white text-black px-6 py-4 font-black uppercase text-lg shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all cursor-pointer">
                            + Buat Bucket Baru
                        </button>
                        <button id="newCredential" class="border-4 border-black bg-black text-white px-6 py-4 font-black uppercase text-lg shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all cursor-pointer">
                            🔑 Generate API Key
                        </button>
                    </div>
                </section>

                {{-- Kredensial Aktif: Pink/Rose Muda (Rose 200) --}}
                <section class="border-4 border-black bg-[#FECDD3] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-sm flex flex-col justify-between">
                    <div>
                        <h2 class="mb-6 text-xl font-black uppercase">🔑 Kredensial Aktif</h2>
                    @if($activeCredential)
                        <div class="bg-white border-4 border-black p-4 shadow-[4px_4px_black] mb-6">
                            <p class="font-bold uppercase text-sm mb-1 text-slate-600">Access Key ID:</p>
                            <p class="font-mono text-xl font-black text-purple-700 truncate">{{ $activeCredential->access_key }}</p>
                            <span class="inline-block mt-2 bg-[#34D399] border-2 border-black px-2 py-1 text-xs font-black uppercase shadow-[2px_2px_black]">Aktif</span>
                        </div>
                    @else
                        <div class="bg-white border-4 border-black p-6 shadow-[4px_4px_black] mb-6 text-center flex flex-col items-center">
                            <div class="text-4xl mb-3">🚫</div>
                            <p class="font-black uppercase text-lg text-slate-900 leading-tight">Tidak Ada Kunci</p>
                            <p class="font-bold text-sm text-slate-700 mt-2">Generate API Key Anda sekarang untuk mulai menghubungkan aplikasi ke MiniStack.</p>
                        </div>
                    @endif
                    </div>
                    <button class="border-4 border-black bg-[#FDE047] text-black px-4 py-3 font-black uppercase shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all cursor-pointer w-full text-center">
                        Lihat Semua Kunci ->
                    </button>
                </section>

            </div>

            {{-- BUCKET TERBARU --}}
            <section class="border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-sm">
                <h2 class="mb-6 text-2xl font-black uppercase">🪣 Bucket Terbaru</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Kartu Bucket 1: Kuning --}}
                    <div class="border-4 border-black bg-[#FDE047] p-4 shadow-[4px_4px_black] hover:-translate-y-2 hover:shadow-[8px_8px_black] transition-all cursor-pointer">
                        <div class="text-4xl mb-2">📁</div>
                        <h3 class="font-black text-lg truncate">aset-gambar</h3>
                        <p class="font-bold text-slate-800 text-sm mt-1 border-t-4 border-black pt-2">Region: us-east-1</p>
                    </div>
                    {{-- Kartu Bucket 2: Putih --}}
                    <div class="border-4 border-black bg-white p-4 shadow-[4px_4px_black] hover:-translate-y-2 hover:shadow-[8px_8px_black] transition-all cursor-pointer">
                        <div class="text-4xl mb-2">📁</div>
                        <h3 class="font-black text-lg truncate">bucket-db</h3>
                        <p class="font-bold text-slate-800 text-sm mt-1 border-t-4 border-black pt-2">Region: ap-south-1</p>
                    </div>
                </div>
            </section>

            {{-- LOG AKTIVITAS (AUDIT TRAIL) --}}
            <section class="border-4 border-black bg-[#E2E8F0] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-sm">
                <h2 class="mb-6 text-2xl font-black uppercase">📜 Log Aktivitas Terakhir</h2>

                <div class="flex flex-col border-4 border-black divide-y-4 divide-black bg-white font-medium">
                    @forelse ($logs as $log)
                        <div class="p-4 hover:bg-[#FEF08A] transition-colors flex flex-col sm:flex-row sm:items-start md:items-center justify-between gap-4">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                {{-- Waktu Log: Biru Tua --}}
                                <span class="bg-[#1E3A8A] text-white px-2 py-1 font-mono text-sm inline-block w-fit shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] border-2 border-black">
                                    {{ $log->created_at->format('H:i A') }}
                                </span>
                                <p class="text-black font-black">{{ $log->details }}</p>
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

        </main>
    </div>
</div>
@endsection
