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

    <section class="border-4 border-black bg-[#F9C25B] p-8 shadow-[8px_8px_black] mb-8">
        <h1 class="text-4xl font-black uppercase mb-4">🪣 {{ $bucket->bucket_name }}</h1>
        <p class="font-bold text-xl">Region: {{ $bucket->region }}</p>
        <p class="font-bold text-xl">Dibuat: {{ $bucket->created_at->format('d M Y, H:i') }}</p>
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

</div>
@endsection