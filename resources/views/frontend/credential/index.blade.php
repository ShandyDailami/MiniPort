@extends('layout.app')

@section('title', 'API Keys - Miniport Cloud')

@section('content')
<div class="min-h-screen bg-[#Fdfcf7] font-sans text-black flex flex-col p-6 md:p-10 relative z-10">

    {{-- TOMBOL KEMBALI --}}
    <div class="mb-6 max-w-7xl mx-auto w-full">
        <a href="/" class="inline-block border-4 border-black bg-white px-4 py-2 font-black uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all">
            &larr; Kembali
        </a>
    </div>
    {{-- HEADER & TOMBOL GENERATE --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 max-w-5xl mx-auto w-full border-b-4 border-black pb-6">
        <div>
            <h1 class="text-4xl font-black uppercase mb-2">🔑 Kredensial S3</h1>
            <p class="font-bold text-slate-700">Kelola kunci akses (API Keys) untuk menghubungkan aplikasi Anda ke MiniStack.</p>
        </div>

        <form action="/credentials" method="POST" class="m-0" onsubmit="return confirm('Anda yakin ingin membuat sepasang kunci S3 baru?');">
            @csrf
            <button type="submit" class="border-4 border-black bg-[#F9C25B] text-black px-6 py-4 font-black uppercase text-xl shadow-[6px_6px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[4px_4px_black] active:translate-x-[6px] active:translate-y-[6px] active:shadow-none transition-all text-center flex items-center gap-2 cursor-pointer">
                <span class="text-2xl">⚡</span> Generate Kunci Baru
            </button>
        </form>
    </div>

    <div class="max-w-5xl mx-auto w-full">

        {{-- BLOK KHUSUS: MENAMPILKAN SECRET KEY (HANYA SEKALI) --}}
        @if (session('new_secret_key'))
        <div class="mb-10 border-4 border-black bg-[#a7f3d0] p-6 shadow-[8px_8px_black] animate-pulse relative">
            <div class="absolute -top-5 -right-5 text-5xl">⚠️</div>
            <h2 class="text-2xl font-black uppercase mb-2 text-[#D72F19]">Peringatan Keamanan!</h2>
            <p class="font-bold text-lg mb-6">Kredensial berhasil dibuat. Simpan <span class="bg-black text-[#a7f3d0] px-2 py-1">Secret Key</span> di bawah ini sekarang. <strong class="underline">Kunci ini tidak akan ditampilkan lagi</strong> setelah Anda meninggalkan halaman ini.</p>

            <div class="bg-white border-4 border-black p-6 flex flex-col gap-4 shadow-[4px_4px_black]">
                <div>
                    <p class="font-bold uppercase text-sm text-slate-500 mb-1">Access Key ID</p>
                    <code class="text-2xl font-black text-purple-700 bg-purple-100 px-3 py-1 border-2 border-purple-700 select-all">{{ session('new_access_key') }}</code>
                </div>
                <div>
                    <p class="font-bold uppercase text-sm text-slate-500 mb-1">Secret Access Key</p>
                    <code class="text-2xl font-black text-[#D72F19] bg-red-100 px-3 py-1 border-2 border-[#D72F19] select-all">{{ session('new_secret_key') }}</code>
                </div>
            </div>
        </div>
        @endif

        {{-- ALERT ERROR UMUM --}}
        @if (session('error'))
        <div class="mb-8 border-4 border-black bg-[#D72F19] text-white p-4 font-black text-lg shadow-[4px_4px_black]">
            ⚠️ {{ session('error') }}
        </div>
        @endif

        @if (session('success') && !session('new_secret_key'))
        <div class="mb-8 border-4 border-black bg-green-400 p-4 font-black text-lg shadow-[4px_4px_black]">
            ✅ {{ session('success') }}
        </div>
        @endif

        {{-- TABEL DAFTAR KREDENSIAL --}}
        <section class="border-4 border-black bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] overflow-hidden">
            <div class="bg-black text-white p-4 border-b-4 border-black">
                <h3 class="text-xl font-black uppercase">Daftar Kunci Aktif & Inaktif</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-max">
                    <thead>
                        <tr class="bg-gray-200 border-b-4 border-black">
                            <th class="p-4 font-black uppercase border-r-4 border-black">Access Key ID</th>
                            <th class="p-4 font-black uppercase border-r-4 border-black text-center">Status</th>
                            <th class="p-4 font-black uppercase border-r-4 border-black">Dibuat Pada</th>
                            <th class="p-4 font-black uppercase text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($credentials as $cred)
                            <tr class="border-b-4 border-black last:border-b-0 hover:bg-yellow-50 transition-colors">
                                <td class="p-4 font-mono font-bold text-lg border-r-4 border-black">
                                    {{ $cred->access_key }}
                                </td>
                                <td class="p-4 border-r-4 border-black text-center">
                                    @if($cred->status === 'active')
                                        <span class="bg-green-400 border-2 border-black px-3 py-1 font-black text-sm uppercase shadow-[2px_2px_black]">Aktif</span>
                                    @else
                                        <span class="bg-slate-300 text-slate-600 border-2 border-black px-3 py-1 font-black text-sm uppercase shadow-[2px_2px_black]">Inaktif</span>
                                    @endif
                                </td>
                                <td class="p-4 font-bold text-slate-700 border-r-4 border-black">
                                    {{ $cred->created_at->format('d M Y') }}
                                </td>
                                <td class="p-4 text-center">
                                    @if($cred->status === 'active')
                                        <form action="/credentials/{{ $cred->id }}/revoke" method="POST" class="m-0" onsubmit="return confirm('Cabut akses kunci ini? Aplikasi yang menggunakannya akan terputus.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="bg-[#D72F19] text-white border-2 border-black px-3 py-2 font-bold uppercase text-xs shadow-[2px_2px_black] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-none transition-all cursor-pointer">
                                                Cabut Akses
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 font-bold text-sm uppercase">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center bg-slate-50">
                                    <p class="font-black text-xl text-slate-500 uppercase">Belum Ada Kredensial</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</div>
@endsection
