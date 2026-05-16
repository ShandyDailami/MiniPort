@extends('layout.app')

@section('title', '- Register')

@section('content')

    <div class="flex h-screen w-full items-center justify-center">
        @if (session('success'))
    <div x-data="{ show: true }"
        x-show="show"
        x-init="setTimeout(() => window.location.href = '/login', 3000)"
        class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center"
        x-transition.opacity.duration.500ms>

        <div class="bg-white border-2 border-black px-8 py-10 rounded-md shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] w-1/3 text-center flex flex-col items-center">

            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-3xl font-bold mb-2">Berhasil!</h2>
            <p class="text-slate-600 font-medium mb-6">
                {{ session('success') }}
            </p>

            <div class="w-6 h-6 border-4 border-slate-300 border-t-[#D72F19] rounded-full animate-spin"></div>
        </div>
    </div>
@endif
        <div class="w-1/2 h-full flex flex-col justify-center items-center bg-[#F9C25B]">
            <form action="" method="POST" class="w-full px-30">
                @csrf
                <h1 class="text-5xl font-bold mb-2">Create your account</h1>
                <p class="text-slate-600 font-medium mb-5">Let's get up set up with a new account in just a few steps</p>
                <label for="name" class="block mb-5">
                    <p class="font-medium">Username</p>
                    <input
                        class="border-2 w-full px-4 py-4 rounded-md bg-white outline-none shadow-[4px_4px] placeholder:font-medium"
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Enter your name">
                </label>
                <label for="email" class="block mb-5">
                    <p class="font-medium">Email</p>
                    <input
                        class="border-2 w-full px-4 py-4 rounded-md bg-white outline-none shadow-[4px_4px] placeholder:font-medium @error('email') border-red-500 shadow-red-500 @else border-black shadow-black @enderror"
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        value="{{ old('email') }}">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1 font-medium">{{ $message }}</p>
                        @enderror
                </label>
                <label for="password" class="block mb-5">
                    <p class="font-medium">Password</p>
                    <input
                        class="border-2 w-full px-4 py-4 rounded-md bg-white outline-none shadow-[4px_4px] placeholder:font-medium @error('password') border-red-500 shadow-red-500 @else border-black shadow-black @enderror"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password">
                        @error('password')
                            <p class="text-red-500 text-sm mt-1 font-medium">{{ $message }}</p>
                        @enderror
                </label>
                <label for="confirm-password" class="block mb-7">
                    <p class="font-medium">Confirm Password</p>
                    <input
                        class="border-2 w-full px-4 py-4 rounded-md bg-white outline-none shadow-[4px_4px] placeholder:font-medium"
                        type="password"
                        id="confirm-password"
                        name="password_confirmation"
                        placeholder="Confirm your password">
                </label>
                <button class="cursor-pointer mb-5 border-2 border-black w-full px-4 py-4 rounded-md bg-[#D72F19] text-white font-medium shadow-[4px_4px_black]">Sign Up</button>
                {{-- <div class="flex justify-between items-center mb-5">
                    <hr class="w-1/3 text-gray-600">
                    <p class="text-center font-medium text-slate-600">Or continue with</p>
                    <hr class="w-1/3 text-gray-600">
                </div>
                <button class="cursor-pointer flex items-center justify-center gap-2 mb-5 border-2 border-black w-full px-4 py-4 rounded-md bg-white text-black font-medium shadow-[4px_4px_black]">
                    <svg class="inline-block" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 16 16"><g fill="none" fill-rule="evenodd" clip-rule="evenodd"><path fill="#f44336" d="M7.209 1.061c.725-.081 1.154-.081 1.933 0a6.57 6.57 0 0 1 3.65 1.82a100 100 0 0 0-1.986 1.93q-1.876-1.59-4.188-.734q-1.696.78-2.362 2.528a78 78 0 0 1-2.148-1.658a.26.26 0 0 0-.16-.027q1.683-3.245 5.26-3.86" opacity="0.987"/><path fill="#ffc107" d="M1.946 4.92q.085-.013.161.027a78 78 0 0 0 2.148 1.658A7.6 7.6 0 0 0 4.04 7.99q.037.678.215 1.331L2 11.116Q.527 8.038 1.946 4.92" opacity="0.997"/><path fill="#448aff" d="M12.685 13.29a26 26 0 0 0-2.202-1.74q1.15-.812 1.396-2.228H8.122V6.713q3.25-.027 6.497.055q.616 3.345-1.423 6.032a7 7 0 0 1-.51.49" opacity="0.999"/><path fill="#43a047" d="M4.255 9.322q1.23 3.057 4.51 2.854a3.94 3.94 0 0 0 1.718-.626q1.148.812 2.202 1.74a6.62 6.62 0 0 1-4.027 1.684a6.4 6.4 0 0 1-1.02 0Q3.82 14.524 2 11.116z" opacity="0.993"/></g></svg>
                    <span>Continue with Google</span> --}}
                </button>
                <p class="font-medium text-slate-600">Already have an account? <a href="/login" class="text-slate-800">Sign in here</a> </p>
            </form>
        </div>
        <div class="w-1/2 h-full bg-[url('/public/img/auth-icon.jpg')] bg-cover bg-center"></div>
    </div>

@endsection
