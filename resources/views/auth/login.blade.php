<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FDFBF7]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MiniPort Cloud Console</title>
    
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
        }
    </style>
</head>
<body class="h-full overflow-hidden bg-[#FDFBF7] text-black" x-data="{ activeTab: 'email' }">

    {{-- WRAPPER UTAMA SPLIT PANEL --}}
    <div class="h-full w-full flex flex-col md:flex-row">
        
        {{-- PANEL KIRI: FORMULIR AUTENTIKASI (Kuning Neobrutalist `#F9C25B`) --}}
        <div class="w-full md:w-1/2 h-full bg-[#F9C25B] flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-20 xl:px-24 overflow-y-auto relative">
            
            {{-- Alert Notifikasi Sukses --}}
            @if (session('success'))
            <div x-data="{ show: true }"
                 x-show="show"
                 class="mb-6 border-4 border-black bg-[#34D399] p-4 font-black shadow-[4px_4px_black] text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="font-black hover:scale-110">&times;</button>
            </div>
            @endif

            {{-- Alert Notifikasi Error --}}
            @if($errors->any() || session('error'))
            <div class="mb-6 border-4 border-black bg-[#FF4545] text-white p-4 font-black shadow-[4px_4px_black] text-sm">
                <div class="flex items-center gap-2 mb-1">
                    <i data-lucide="alert-octagon" class="h-4 w-4 stroke-[3]"></i>
                    <span class="uppercase">Gagal Masuk!</span>
                </div>
                <p class="font-semibold text-xs leading-tight text-white/95">
                    {{ $errors->first() ?? session('error') }}
                </p>
            </div>
            @endif

            <div class="mx-auto w-full max-w-sm lg:w-96">
                {{-- LOGO --}}
                <div class="inline-flex items-center gap-2 border-4 border-black bg-white p-2.5 px-5 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] mb-8">
                    <span class="text-2xl">🍞</span>
                    <span class="text-lg font-black uppercase tracking-tight text-black">TOASTING</span>
                </div>

                <h1 class="text-4xl font-black uppercase tracking-tight text-black mb-2">Log in to Account</h1>
                <p class="text-slate-800 font-bold text-xs mb-6">Senang melihat Anda kembali! Silakan pilih metode autentikasi Anda.</p>

                {{-- SWITCHER TAB NEOBRUTALIST (Alpine.js) --}}
                <div class="grid grid-cols-2 gap-3 mb-6 border-4 border-black bg-black p-1.5 shadow-[4px_4px_black]">
                    <button 
                        type="button"
                        @click="activeTab = 'email'" 
                        :class="activeTab === 'email' ? 'bg-[#FDE047] text-black shadow-[2px_2px_0px_black]' : 'bg-zinc-800 text-slate-400 hover:text-white'"
                        class="py-2.5 text-xs font-black uppercase tracking-wider transition-all select-none border-2 border-transparent focus:outline-none"
                    >
                        📧 Email & Pass
                    </button>
                    <button 
                        type="button"
                        @click="activeTab = 'apikey'" 
                        :class="activeTab === 'apikey' ? 'bg-[#FDE047] text-black shadow-[2px_2px_0px_black]' : 'bg-zinc-800 text-slate-400 hover:text-white'"
                        class="py-2.5 text-xs font-black uppercase tracking-wider transition-all select-none border-2 border-transparent focus:outline-none"
                    >
                        🔑 S3 API Key
                    </button>
                </div>

                {{-- OPSI 1: FORM LOGIN TRADISIONAL (EMAIL & PASSWORD) --}}
                <form x-show="activeTab === 'email'" action="{{ url('/login') }}" method="POST" class="space-y-6 m-0">
                    @csrf

                    <!-- Input Email -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-black uppercase tracking-wider text-black">Alamat Email</label>
                        <div class="relative">
                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Masukkan email Anda"
                                value="{{ old('email') }}"
                                :required="activeTab === 'email'"
                                class="w-full border-4 border-black bg-white p-4 pl-12 font-bold text-sm shadow-[4px_4px_black] focus:shadow-[2px_2px_black] focus:translate-x-[2px] focus:translate-y-[2px] focus:outline-none transition-all rounded-none placeholder-slate-400 @error('email') border-red-500 @enderror"
                            >
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <i data-lucide="mail" class="h-5 w-5 text-black stroke-[2.5]"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Input Password -->
                    <div class="space-y-1.5" x-data="{ show: false }">
                        <label for="password" class="block text-xs font-black uppercase tracking-wider text-black">Kata Sandi</label>
                        <div class="relative">
                            <input
                                :type="show ? 'text' : 'password'"
                                id="password"
                                name="password"
                                placeholder="Masukkan kata sandi"
                                :required="activeTab === 'email'"
                                class="w-full border-4 border-black bg-white p-4 pl-12 pr-12 font-bold text-sm shadow-[4px_4px_black] focus:shadow-[2px_2px_black] focus:translate-x-[2px] focus:translate-y-[2px] focus:outline-none transition-all rounded-none placeholder-slate-400 @error('password') border-red-500 @enderror"
                            >
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <i data-lucide="lock" class="h-5 w-5 text-black stroke-[2.5]"></i>
                            </div>
                            <button 
                                type="button" 
                                @click="show = !show" 
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-500 hover:text-black transition-colors"
                            >
                                <i :data-lucide="show ? 'eye-off' : 'eye'" class="h-4 w-4 stroke-[2.5]"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Submit Email -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full border-4 border-black bg-[#D72F19] text-white p-4 font-black uppercase tracking-wider text-sm shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] active:translate-x-[4px] active:translate-y-[4px] active:shadow-none transition-all cursor-pointer text-center"
                        >
                            Masuk Konsol ->
                        </button>
                    </div>
                </form>

                {{-- OPSI 2: FORM LOGIN VIA S3 API KEY --}}
                <form x-show="activeTab === 'apikey'" action="{{ url('/login') }}" method="POST" class="space-y-6 m-0" style="display: none;">
                    @csrf

                    <!-- Input Access Key ID -->
                    <div class="space-y-1.5">
                        <label for="access_key" class="block text-xs font-black uppercase tracking-wider text-black">Access Key ID</label>
                        <div class="relative">
                            <input
                                type="text"
                                id="access_key"
                                name="access_key"
                                placeholder="Masukkan Access Key ID"
                                value="{{ old('access_key') }}"
                                :required="activeTab === 'apikey'"
                                class="w-full border-4 border-black bg-white p-4 pl-12 font-mono font-bold text-xs shadow-[4px_4px_black] focus:shadow-[2px_2px_black] focus:translate-x-[2px] focus:translate-y-[2px] focus:outline-none transition-all rounded-none placeholder-slate-400"
                            >
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <i data-lucide="key-round" class="h-5 w-5 text-black stroke-[2.5]"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Input Secret Access Key -->
                    <div class="space-y-1.5" x-data="{ show: false }">
                        <label for="secret_key" class="block text-xs font-black uppercase tracking-wider text-black">Secret Access Key</label>
                        <div class="relative">
                            <input
                                :type="show ? 'text' : 'password'"
                                id="secret_key"
                                name="secret_key"
                                placeholder="Masukkan Secret Access Key"
                                :required="activeTab === 'apikey'"
                                class="w-full border-4 border-black bg-white p-4 pl-12 pr-12 font-mono font-bold text-xs shadow-[4px_4px_black] focus:shadow-[2px_2px_black] focus:translate-x-[2px] focus:translate-y-[2px] focus:outline-none transition-all rounded-none placeholder-slate-400"
                            >
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <i data-lucide="shield-check" class="h-5 w-5 text-black stroke-[2.5]"></i>
                            </div>
                            <button 
                                type="button" 
                                @click="show = !show" 
                                class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-500 hover:text-black transition-colors"
                            >
                                <i :data-lucide="show ? 'eye-off' : 'eye'" class="h-4 w-4 stroke-[2.5]"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tombol Submit API Key -->
                    <div class="pt-2">
                        <button 
                            type="submit" 
                            class="w-full border-4 border-black bg-black text-white p-4 font-black uppercase tracking-wider text-sm shadow-[4px_4px_#34D399] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_#34D399] active:translate-x-[4px] active:translate-y-[4px] active:shadow-none transition-all cursor-pointer text-center"
                        >
                            Otorisasi Kredensial ->
                        </button>
                    </div>
                </form>

                {{-- NAVIGASI DAFTAR --}}
                <div class="mt-8 border-t-2 border-black pt-4 text-center">
                    <p class="text-xs font-bold text-slate-800">
                        Belum memiliki akun MiniPort? 
                        <a href="{{ url('/register') }}" class="text-black font-black underline hover:text-purple-900 transition-colors">Daftar Akun Baru</a>
                    </p>
                </div>
            </div>
        </div>

        {{-- PANEL KANAN: GAMBAR PEMANIS RETRO (Sembunyi di mobile) --}}
        <div class="hidden md:block md:w-1/2 h-full relative overflow-hidden">
            <div 
                class="absolute inset-0 bg-cover bg-center transition-all duration-700 hover:scale-105"
                style="background-image: url('{{ asset('img/auth-icon.jpg') }}');"
            ></div>
            
            <!-- Overlay Gelap Retro Semitransparan -->
            <div class="absolute inset-0 bg-black/20"></div>

            <!-- Tumpukan Badge Neobrutalism Floating di Atas Gambar -->
            <div class="absolute bottom-10 left-10 z-20 space-y-4">
                <div class="border-4 border-black bg-[#34D399] p-4 shadow-[6px_6px_0px_rgba(0,0,0,1)] max-w-sm rounded-none">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xl">🛡️</span>
                        <h4 class="text-xs font-black uppercase tracking-wider text-black">MiniStack S3 Sandbox</h4>
                    </div>
                    <p class="text-[10px] font-bold text-slate-800 leading-tight">
                        Mengakses jaringan file manager lokal virtualisasi Docker yang terenkripsi dan aman.
                    </p>
                </div>

                <span class="inline-flex items-center gap-1.5 px-3 py-1 border-2 border-black bg-[#FDE047] font-black text-[10px] uppercase shadow-[3px_3px_black]">
                    <i data-lucide="cpu" class="h-3.5 w-3.5 stroke-[2.5]"></i> Sandbox Demo Mode
                </span>
            </div>
        </div>

    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>