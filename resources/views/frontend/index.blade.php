@extends('layout.app')

@section('title', 'home')

@section('content')
<div class="w-full h-screen flex flex-col justify-center items-center ">
    <h1 class="text-5xl font-bold">hebat {{ $name }} sudah done berhasil masuk ya</h1>
    <form action="/logout" method="POST" class="inline">
        @csrf
        <button class="mb-5 border-2 border-black w-full px-4 py-4 rounded-md bg-[#D72F19] text-white font-medium shadow-[4px_4px_black] cursor-pointer">Logout</button>
    </form>
</div>
@endsection
