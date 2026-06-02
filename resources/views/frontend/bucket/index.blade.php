@extends('layout.app')

@section('title', 'Semua Buckets - Miniport Cloud')

@section('content')
<div class="min-h-screen bg-[#Fdfcf7] font-sans text-black flex flex-col p-6 md:p-10 relative z-10">

    {{-- TOMBOL KEMBALI --}}
    <div class="mb-6 max-w-7xl mx-auto w-full">
        <a href="/" class="inline-block border-4 border-black bg-white px-4 py-2 font-black uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all">
            &larr; Kembali
        </a>
    </div>

    {{-- HEADER HALAMAN & TOMBOL TAMBAH --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10 max-w-7xl mx-auto w-full border-b-4 border-black pb-6">
        <div>
            <h1 class="text-4xl font-black uppercase mb-2">🪣 Daftar Bucket S3</h1>
            <p class="font-bold text-slate-700">Kelola semua ruang penyimpanan cloud Anda di sini.</p>
        </div>

        <a href="/bucket/create" class="border-4 border-black bg-[#34D399] text-black px-6 py-3 font-black uppercase text-xl shadow-[6px_6px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[4px_4px_black] active:translate-x-[6px] active:translate-y-[6px] active:shadow-none transition-all text-center">
            + Buat Bucket Baru
        </a>
    </div>

    {{-- ALERT PESAN SUKSES/ERROR --}}
    <div class="max-w-7xl mx-auto w-full mb-6">
        @if (session('success'))
        <div class="border-4 border-black bg-green-400 p-4 font-black text-lg shadow-[4px_4px_black]">
            ✅ {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="border-4 border-black bg-[#D72F19] text-white p-4 font-black text-lg shadow-[4px_4px_black]">
            ⚠️ {{ session('error') }}
        </div>
        @endif
    </div>

    {{-- DAFTAR BUCKET (GRID) --}}
    <div class="max-w-7xl mx-auto w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            {{-- Loop Data Buckets dari Controller --}}
            @forelse ($buckets as $bucket)
                <div class="border-4 border-black bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:-translate-y-2 hover:shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] transition-all flex flex-col group">

                    {{-- Bagian Atas Card (Info Utama) --}}
                    <div class="p-6 flex-1 bg-[#F9C25B] border-b-4 border-black">
                        <div class="flex justify-between items-start mb-4">
                            <span class="text-5xl">📁</span>
                            <span class="border-2 border-black bg-white px-2 py-1 text-xs font-bold uppercase shadow-[2px_2px_black]">
                                {{ $bucket->region }}
                            </span>
                        </div>
                        <h2 class="text-2xl font-black truncate" title="{{ $bucket->bucket_name }}">
                            {{ $bucket->bucket_name }}
                        </h2>
                        <p class="font-bold text-sm text-slate-800 mt-2">
                            Dibuat: {{ $bucket->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>

                    {{-- Bagian Bawah Card (Aksi) --}}
                    <div class="flex bg-white">
                        {{-- Tombol Buka/Lihat Isi (Opsional jika Anda membuat fitur File Explorer) --}}
                        <a href="/bucket/{{ $bucket->id }}" class="flex-1 border-r-4 border-black p-4 text-center font-black uppercase hover:bg-cyan-300 transition-colors">
                            🔍 Buka
                        </a>

                        {{-- Tombol Hapus --}}
                        <form action="/bucket/{{ $bucket->id }}" method="POST" class="flex-1 m-0" onsubmit="return confirm('Peringatan Brutal: Anda yakin ingin menghapus bucket ini beserta SELURUH isinya? Tindakan ini tidak bisa dibatalkan!');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full h-full p-4 text-center font-black uppercase bg-white text-[#D72F19] hover:bg-[#D72F19] hover:text-white transition-colors cursor-pointer">
                                🗑️ Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                {{-- Tampilan Jika Belum Ada Bucket (Empty State) --}}
                <div class="col-span-full border-4 border-black bg-[#E9D5FF] p-12 text-center shadow-[8px_8px_black]">
                    <div class="text-7xl mb-6">🏜️</div>
                    <h2 class="text-3xl font-black uppercase mb-4">Ruang Penyimpanan Masih Kosong!</h2>
                    <p class="font-bold text-xl text-slate-800 mb-8">Anda belum menciptakan satu pun bucket di server MiniStack.</p>
                    <a href="/buckets/create" class="inline-block border-4 border-black bg-white px-6 py-4 font-black uppercase text-xl shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all">
                        🚀 Ciptakan Bucket Pertamamu
                    </a>
                </div>
            @endforelse

        </div>
    </div>
</div>
@endsection
