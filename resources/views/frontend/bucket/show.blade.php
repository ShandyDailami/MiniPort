@extends('layout.app')

@section('title', 'Detail Bucket - Miniport Cloud')

@section('content')
<div class="min-h-screen bg-[#Fdfcf7] font-sans text-black p-6 md:p-10">

    <div class="mb-6">
        <a href="/buckets"
           class="inline-block border-4 border-black bg-white px-4 py-2 font-black uppercase shadow-[4px_4px_black]">
            &larr; Kembali ke Buckets
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 border-4 border-black bg-green-400 p-4 font-black text-lg shadow-[4px_4px_black]">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 border-4 border-black bg-[#D72F19] text-white p-4 font-black text-lg shadow-[4px_4px_black]">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    @if (session('share_url'))
        <div class="mb-6 border-4 border-black bg-cyan-300 p-5 shadow-[4px_4px_black]">
            <h3 class="font-black uppercase text-xl mb-3">🔗 Temporary Share Link</h3>

            <p class="font-bold mb-2">
                Object: <span class="font-mono">{{ session('share_key') }}</span>
            </p>

            <p class="font-bold mb-4">
                Berlaku: {{ session('share_expires') }} menit
            </p>

            <input
                type="text"
                readonly
                value="{{ session('share_url') }}"
                onclick="this.select()"
                class="w-full border-4 border-black bg-white px-4 py-3 font-mono text-sm shadow-[3px_3px_black]"
            >

            <p class="font-bold text-sm mt-3">
                Klik input di atas lalu tekan CTRL + C untuk copy link.
            </p>
        </div>
    @endif

    <section class="border-4 border-black bg-[#F9C25B] p-8 shadow-[8px_8px_black] mb-8">
        <h1 class="text-4xl font-black uppercase mb-4">🪣 {{ $bucket->bucket_name }}</h1>
        <p class="font-bold text-xl">Region: {{ $bucket->region }}</p>
        <p class="font-bold text-xl">Dibuat: {{ $bucket->created_at->format('d M Y, H:i') }}</p>
    </section>

    <section class="border-4 border-black bg-[#E9D5FF] p-8 shadow-[8px_8px_black] mb-8">
        <h2 class="text-3xl font-black uppercase mb-6">⬆️ Upload File</h2>

        <form action="/bucket/{{ $bucket->id }}/objects" method="POST" enctype="multipart/form-data" class="flex flex-col gap-6">
            @csrf

            <div>
                <label for="object_file" class="block font-black uppercase text-xl mb-2">
                    Pilih File
                </label>

                <input
                    type="file"
                    id="object_file"
                    name="object_file"
                    required
                    class="w-full border-4 border-black bg-white px-4 py-4 font-bold shadow-[4px_4px_black]"
                >

                @error('object_file')
                    <div class="mt-3 font-bold text-white bg-[#D72F19] border-2 border-black inline-block px-3 py-1 shadow-[2px_2px_black]">
                        {{ $message }}
                    </div>
                @enderror

                <p class="font-bold text-sm text-slate-700 mt-3">
                    Maksimal file: {{ $maxUploadMb ?? 500 }} MB.
                </p>
            </div>

            <button
                type="submit"
                class="w-fit border-4 border-black bg-[#34D399] text-black px-6 py-4 font-black uppercase text-xl shadow-[6px_6px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[4px_4px_black] transition-all"
            >
                Upload ke Bucket
            </button>
        </form>
    </section>

    <section class="border-4 border-black bg-white p-8 shadow-[8px_8px_black]">
        <h2 class="text-3xl font-black uppercase mb-6">📦 Isi Bucket</h2>

        @if(isset($objects) && $objects->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full border-4 border-black">
                    <thead class="bg-[#E9D5FF] border-b-4 border-black">
                        <tr>
                            <th class="p-4 text-left font-black uppercase">Object Key</th>
                            <th class="p-4 text-left font-black uppercase">Size</th>
                            <th class="p-4 text-left font-black uppercase">Last Modified</th>
                            <th class="p-4 text-left font-black uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($objects as $object)
                            <tr class="border-b-2 border-black">
                                <td class="p-4 font-mono">{{ $object['key'] }}</td>
                                <td class="p-4 font-bold">{{ $object['size'] }} bytes</td>
                                <td class="p-4 font-bold">
                                    {{ $object['last_modified'] ?? '-' }}
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col md:flex-row gap-3">
                                        <a href="/bucket/{{ $bucket->id }}/objects/download?key={{ urlencode($object['key']) }}"
                                        class="inline-block border-4 border-black bg-[#34D399] px-4 py-2 font-black uppercase text-center shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all">
                                            ⬇️ Download
                                        </a>

                                        <a href="/bucket/{{ $bucket->id }}/objects/share?key={{ urlencode($object['key']) }}&expires=5"class="inline-block border-4 border-black bg-[#E9D5FF] px-4 py-2 font-black uppercase text-center shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all">
                                            🔗 Share 5 Menit
                                        </a>

                                        <form action="/bucket/{{ $bucket->id }}/objects" method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus file ini dari bucket?');">
                                            @csrf
                                            @method('DELETE')

                                            <input type="hidden" name="key" value="{{ $object['key'] }}">

                                            <button type="submit"
                                                    class="w-full border-4 border-black bg-[#D72F19] text-white px-4 py-2 font-black uppercase shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="border-4 border-black bg-[#E9D5FF] p-8 text-center shadow-[4px_4px_black]">
                <p class="text-2xl font-black uppercase">Bucket masih kosong.</p>
                <p class="font-bold mt-2">Belum ada object/file yang diupload ke bucket ini.</p>
            </div>
        @endif
    </section>

    <section class="border-4 border-black bg-cyan-300 p-6 shadow-[8px_8px_black] mb-8">
        <h2 class="text-2xl font-black uppercase mb-4">📊 Storage Quota</h2>

        <div class="font-black text-xl mb-3">
            Terpakai: {{ $usedStorageText ?? '0 bytes' }} / {{ $storageLimitText ?? '50 MB' }}
        </div>

        <div class="w-full border-4 border-black bg-white h-8 shadow-[4px_4px_black]">
            <div
                class="h-full bg-[#34D399]"
                style="width: {{ $usagePercentage ?? 0 }}%">
            </div>
        </div>

        <p class="font-bold mt-3">
            Penggunaan: {{ round($usagePercentage ?? 0, 2) }}%
        </p>
    </section>

</div>
@endsection