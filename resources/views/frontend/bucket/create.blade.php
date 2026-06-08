@extends('layout.app')

@section('title', 'Buat Bucket Baru - Miniport Cloud')

@section('content')
<div class="min-h-screen bg-[#Fdfcf7] font-sans text-black flex flex-col p-6 md:p-10">

    {{-- Tombol Kembali --}}
    <div class="mb-8 w-full max-w-2xl mx-auto">
        <a href="/" class="inline-block border-4 border-black bg-white px-4 py-2 font-black uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all">
            &larr; Kembali ke Dashboard
        </a>
    </div>

    {{-- Container Utama Formulir --}}
    <div class="max-w-2xl mx-auto w-full">
        <section class="border-4 border-black bg-[#E9D5FF] p-6 md:p-10 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-md relative z-10">
            <h1 class="text-4xl font-black uppercase mb-2">🪣 Buat Bucket Baru</h1>
            <p class="font-bold text-slate-800 mb-8 border-b-4 border-black pb-4">Tentukan nama unik dan lokasi server untuk bucket penyimpanan S3 Anda.</p>

            {{-- Alert Sukses (Jika redirect back with success) --}}
            @if (session('success'))
            <div class="mb-6 border-4 border-black bg-green-400 p-4 font-black text-lg shadow-[4px_4px_black]">
                ✅ {{ session('success') }}
            </div>
            @endif

            {{-- Alert Error Umum (Misal dari AWS SDK) --}}
            @if (session('error'))
            <div class="mb-6 border-4 border-black bg-[#D72F19] text-white p-4 font-black text-lg shadow-[4px_4px_black]">
                ⚠️ {{ session('error') }}
            </div>
            @endif

            <form action="" method="POST" class="flex flex-col gap-8">
                @csrf

                {{-- Input Nama Bucket --}}
                <div class="flex flex-col gap-2">
                    <label for="bucket_name" class="font-black uppercase text-xl">Nama Bucket</label>
                    <input
                        type="text"
                        id="bucket_name"
                        name="bucket_name"
                        placeholder="contoh: aset-website-2026"
                        value="{{ old('bucket_name') }}"
                        class="w-full border-4 border-black bg-white px-4 py-4 font-mono text-xl shadow-[4px_4px_black] outline-none focus:bg-yellow-50 focus:translate-x-[2px] focus:translate-y-[2px] focus:shadow-[2px_2px_black] transition-all @error('bucket_name') border-[#D72F19] bg-red-50 @enderror"
                        required
                    >

                    {{-- Pesan Error Validasi --}}
                    @error('bucket_name')
                        <span class="font-bold text-white bg-[#D72F19] border-2 border-black inline-block px-3 py-1 mt-2 w-fit shadow-[2px_2px_black]">{{ $message }}</span>
                    @enderror

                    <p class="font-bold text-sm text-slate-700 mt-2">Aturan: Gunakan huruf kecil, angka, dan strip (-). Nama harus unik secara global.</p>
                </div>

                {{-- Input Pilihan Region --}}
                <div class="flex flex-col gap-2">
                    <label for="region" class="font-black uppercase text-xl">Region Server</label>
                    <div class="relative">
                        <select
                            id="region"
                            name="region"
                            class="w-full border-4 border-black bg-white px-4 py-4 font-bold text-lg shadow-[4px_4px_black] outline-none focus:bg-yellow-50 focus:translate-x-[2px] focus:translate-y-[2px] focus:shadow-[2px_2px_black] transition-all cursor-pointer appearance-none"
                            required
                        >
                            <option value="" disabled {{ old('region') ? '' : 'selected' }}>Pilih Lokasi Server...</option>
                            <option value="us-east-1" {{ old('region') == 'us-east-1' ? 'selected' : '' }}>🇺🇸 US East (N. Virginia)</option>
                            <option value="ap-southeast-1" {{ old('region') == 'ap-southeast-1' ? 'selected' : '' }}>🇸🇬 Asia Pacific (Singapore)</option>
                            <option value="ap-southeast-3" {{ old('region') == 'ap-southeast-3' ? 'selected' : '' }}>🇮🇩 Asia Pacific (Jakarta)</option>
                        </select>
                        {{-- Ikon panah kustom agar serasi dengan gaya brutal --}}
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 font-black text-2xl border-l-4 border-black bg-[#F9C25B]">
                            &darr;
                        </div>
                    </div>

                    @error('region')
                        <span class="font-bold text-white bg-[#D72F19] border-2 border-black inline-block px-3 py-1 mt-2 w-fit shadow-[2px_2px_black]">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Tombol Submit --}}
                <button
                    type="submit"
                    class="mt-4 border-4 border-black bg-[#34D399] text-black px-6 py-5 font-black uppercase text-2xl tracking-wide shadow-[8px_8px_black] hover:translate-x-[4px] hover:translate-y-[4px] hover:shadow-[4px_4px_black] active:translate-x-[8px] active:translate-y-[8px] active:shadow-none transition-all cursor-pointer text-center"
                >
                    + Ciptakan Bucket 🚀
                </button>
            </form>
        </section>

        {{-- Elemen Dekorasi Latar (Opsional, menambah kesan kasar) --}}
        <div class="hidden md:block absolute top-20 right-20 w-32 h-32 border-4 border-black bg-yellow-300 z-0 rotate-12 shadow-[4px_4px_black]"></div>
        <div class="hidden md:block absolute bottom-20 left-20 w-24 h-24 border-4 border-black bg-cyan-300 z-0 -rotate-6 shadow-[4px_4px_black] rounded-full"></div>
    </div>
</div>
@endsection
