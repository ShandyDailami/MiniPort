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

    <section class="border-4 border-black bg-[#F9C25B] p-8 shadow-[8px_8px_black]">
        <h1 class="text-4xl font-black uppercase mb-4">🪣 {{ $bucket->bucket_name }}</h1>

        <p class="font-bold text-xl">Region: {{ $bucket->region }}</p>
        <p class="font-bold text-xl">Dibuat: {{ $bucket->created_at->format('d M Y, H:i') }}</p>
        <p class="font-bold text-xl">Owner ID: {{ $bucket->user_id }}</p>
    </section>

</div>
@endsection
