<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FDFBF7]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - MiniPort Cloud Console</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            /* Pola grid latar belakang retro */
            background-color: #FDFBF7;
            background-image: 
                linear-gradient(#e5e7eb 1.5px, transparent 1.5px),
                linear-gradient(90deg, #e5e7eb 1.5px, transparent 1.5px);
            background-size: 30px 30px;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 antialiased">

    <div class="w-full max-w-md">
        
        {{-- LOGO / BRANDING RETRO --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-2 border-4 border-black bg-[#C4B5FD] p-3 px-6 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all">
                <span class="text-3xl">🍞</span>
                <span class="text-2xl font-black uppercase tracking-tight text-black">TOASTING</span>
            </div>
            <p class="font-bold text-slate-700 text-xs mt-3 uppercase tracking-widest">MiniStack Sandbox Console</p>
        </div>

        {{-- CONTAINER UTAMA FORMULIR --}}
        <main class="border-4 border-black bg-white p-6 sm:p-8 shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] rounded-none relative z-10">
            <h2 class="text-2xl font-black uppercase mb-2 text-black">Daftar Akun Baru</h2>
            <p class="font-bold text-slate-600 text-xs mb-6 border-b-4 border-black pb-4">Registrasikan akun developer sandbox Anda secara gratis.</p>

            {{-- ALERT ERROR VALIDASI GLOBAL --}}
            @if($errors->any())
                <div class="mb-6 border-4 border-black bg-[#FF4545] text-white p-4 font-bold text-xs shadow-[4px_4px_black]">
                    <div class="flex items-center gap-2 mb-1">
                        <i data-lucide="alert-octagon" class="h-4 w-4 stroke-[3]"></i>
                        <span class="font-black uppercase">Pendaftaran Gagal!</span>
                    </div>
                    <p class="font-semibold leading-tight text-white/95">{{ $errors->first() }}</p>
                </div>
            @endif

            {{-- FORM REGISTER --}}
            <form action="{{ url('/register') }}" method="POST" class="space-y-5 m-0">
                @csrf

                <!-- Input Nama Lengkap -->
                <div>
                    <label for="name" class="block text-xs font-black uppercase tracking-wider text-black mb-1.5">Nama Lengkap:</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            value="{{ old('name') }}"
                            required 
                            placeholder="Contoh: John Doe"
                            class="w-full border-4 border-black p-3 pl-11 font-bold text-sm text-black focus:outline-none focus:ring-0 focus:bg-[#FDFBF7] shadow-[4px_4px_black] focus:shadow-[2px_2px_black] focus:translate-x-[2px] focus:translate-y-[2px] transition-all rounded-none placeholder-slate-400"
                        >
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i data-lucide="user" class="h-4 w-4 text-black stroke-[2.5]"></i>
                        </div>
                    </div>
                </div>

                <!-- Input Email -->
                <div>
                    <label for="email" class="block text-xs font-black uppercase tracking-wider text-black mb-1.5">Alamat Email:</label>
                    <div class="relative">
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email') }}"
                            required 
                            placeholder="nama@domain.com"
                            class="w-full border-4 border-black p-3 pl-11 font-bold text-sm text-black focus:outline-none focus:ring-0 focus:bg-[#FDFBF7] shadow-[4px_4px_black] focus:shadow-[2px_2px_black] focus:translate-x-[2px] focus:translate-y-[2px] transition-all rounded-none placeholder-slate-400"
                        >
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i data-lucide="mail" class="h-4 w-4 text-black stroke-[2.5]"></i>
                        </div>
                    </div>
                </div>

                <!-- Input Password -->
                <div>
                    <label for="password" class="block text-xs font-black uppercase tracking-wider text-black mb-1.5">Kata Sandi:</label>
                    <div class="relative" x-data="{ show: false }">
                        <input 
                            :type="show ? 'text' : 'password'" 
                            name="password" 
                            id="password" 
                            required 
                            placeholder="Minimal 8 karakter"
                            class="w-full border-4 border-black p-3 pl-11 pr-10 font-bold text-sm text-black focus:outline-none focus:ring-0 focus:bg-[#FDFBF7] shadow-[4px_4px_black] focus:shadow-[2px_2px_black] focus:translate-x-[2px] focus:translate-y-[2px] transition-all rounded-none placeholder-slate-400"
                        >
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i data-lucide="lock" class="h-4 w-4 text-black stroke-[2.5]"></i>
                        </div>
                        <button 
                            type="button" 
                            @click="show = !show" 
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-black transition-colors"
                        >
                            <i :data-lucide="show ? 'eye-off' : 'eye'" class="h-4 w-4 stroke-[2.5]"></i>
                        </button>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full border-4 border-black bg-[#34D399] text-black px-6 py-3.5 font-black uppercase text-sm text-center shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] active:translate-x-[4px] active:translate-y-[4px] active:shadow-none transition-all cursor-pointer"
                    >
                        Buat Akun Sekarang ->
                    </button>
                </div>
            </form>

            {{-- MENU LOGIN LINK --}}
            <div class="mt-6 border-t-2 border-black pt-4 text-center">
                <p class="text-xs font-bold text-slate-600">
                    Sudah memiliki akun MiniPort? 
                    <a href="{{ url('/login') }}" class="text-black font-black underline hover:text-purple-800 transition-colors">Login Sekarang</a>
                </p>
            </div>
        </main>

        {{-- Info Sandbox --}}
        <div class="mt-6 text-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 border-2 border-black bg-[#67E8F9] font-black text-[10px] uppercase shadow-[2px_2px_black]">
                <i data-lucide="unlock" class="h-3.5 w-3.5 stroke-[2.5]"></i> Sandbox Demo Mode
            </span>
        </div>

    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>