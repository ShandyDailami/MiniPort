<!DOCTYPE html>
<html lang="id" class="h-full bg-[#FDFBF7]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MiniPort') - MiniStack Cloud Console</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        }
                    }
                }
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Custom retro scrollbar */
        ::-webkit-scrollbar {
            width: 12px;
        }
        ::-webkit-scrollbar-track {
            background: #FDFBF7;
            border-left: 4px solid #000000;
        }
        ::-webkit-scrollbar-thumb {
            background-color: #000000;
            border: 2px solid #FDFBF7;
        }
    </style>
    @yield('styles')
</head>
<body class="min-h-screen text-black bg-[#FDFBF7] antialiased flex flex-col" x-data="{ mobileSidebarOpen: false }">

    <header class="w-full flex items-center justify-between border-b-4 border-black bg-[#C4B5FD] px-6 py-4 shadow-[0px_4px_0px_0px_rgba(0,0,0,1)] relative z-40">
        <div class="flex items-center gap-4">
            <button type="button" @click="mobileSidebarOpen = true" class="flex items-center justify-center border-2 border-black bg-[#FDE047] p-2 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,1)] transition-all lg:hidden">
                <i data-lucide="menu" class="h-5 w-5 text-black"></i>
            </button>
            
            <h1 class="text-xl sm:text-2xl font-extrabold uppercase tracking-tight text-black flex items-center gap-2">
                <span>📦 MiniPort Cloud</span>
            </h1>
        </div>

        <div class="flex items-center gap-4 font-medium">
            <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 border-2 border-black bg-[#34D399] font-black text-xs uppercase shadow-[2px_2px_black]">
                <i data-lucide="cpu" class="h-3.5 w-3.5"></i> Sandbox Mode
            </span>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 border-2 border-black bg-white px-3 py-1.5 shadow-[2px_2px_black] font-bold text-sm text-black hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[1px_1px_black] transition-all">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-none bg-[#C4B5FD] border border-black text-black font-black text-xs uppercase">
                        {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                    </span>
                    <span class="hidden sm:inline">{{ Auth::user()->name ?? 'Profile' }}</span>
                    <i data-lucide="chevron-down" class="h-4 w-4"></i>
                </button>

                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 z-50 mt-2 w-52 border-4 border-black bg-white p-2 shadow-[4px_4px_black] rounded-none" style="display: none;">
                    
                    <div class="px-3 py-2 border-b-2 border-black mb-2">
                        <p class="text-[10px] uppercase font-black text-slate-500">Masuk sebagai</p>
                        <p class="text-xs font-bold text-black truncate">{{ Auth::user()->email ?? 'user@email.com' }}</p>
                    </div>
                    
                    <form action="{{ url('/logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="w-full text-left flex items-center gap-2 border-2 border-transparent hover:border-black hover:bg-[#FF4545] hover:text-white px-3 py-2 text-xs font-black uppercase transition-all">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            Logout / Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="flex-1 flex flex-col lg:flex-row w-full relative z-10">
        
        <div x-show="mobileSidebarOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true" style="display: none;">
            <div x-show="mobileSidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition-opacity ease-linear duration-300" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

            <div class="fixed inset-0 flex">
                <div x-show="mobileSidebarOpen" 
                     x-transition:enter="transition ease-in-out duration-300 transform" 
                     x-transition:enter-start="-translate-x-full" 
                     x-transition:enter-end="translate-x-0" 
                     x-transition:leave="transition ease-in-out duration-300 transform" 
                     x-transition:leave-start="translate-x-0" 
                     x-transition:leave-end="-translate-x-full" 
                     class="relative mr-16 flex w-full max-w-xs flex-1 flex-col border-r-4 border-black bg-white pb-4 pt-5 shadow-[4px_0px_0px_0px_rgba(0,0,0,1)]"
                     @click.away="mobileSidebarOpen = false">
                    
                    <div class="absolute right-0 top-0 -mr-12 pt-2">
                        <button type="button" @click="mobileSidebarOpen = false" class="ml-1 flex h-10 w-10 items-center justify-center border-2 border-black bg-[#FF4545] text-white shadow-[2px_2px_black] focus:outline-none">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>

                    <div class="flex flex-shrink-0 items-center px-4 border-b-4 border-black pb-4">
                        <span class="text-lg font-black uppercase text-black flex items-center gap-2">
                            <i data-lucide="cloud-lightning" class="text-black h-5 w-5 fill-[#FDE047]"></i>
                            MiniPort Navigation
                        </span>
                    </div>
                    
                    <nav class="mt-6 h-full overflow-y-auto px-4 space-y-3">
                        <a href="{{ url('/') }}" class="border-4 px-4 py-3 font-black uppercase flex items-center justify-between transition-all duration-200 {{ Request::is('/') ? 'border-black bg-[#FDE047] text-black shadow-[4px_4px_black]' : 'border-transparent text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50' }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="layout-dashboard" class="h-5 w-5"></i>
                                Overview
                            </span>
                            <span class="text-xl leading-none">&gt;</span>
                        </a>
                        <a href="{{ url('/buckets') }}" class="border-4 px-4 py-3 font-black uppercase flex items-center justify-between transition-all duration-200 {{ Request::is('buckets*') || Request::is('bucket*') ? 'border-black bg-[#FDE047] text-black shadow-[4px_4px_black]' : 'border-transparent text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50' }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="database" class="h-5 w-5"></i>
                                S3 Buckets
                            </span>
                            <span class="text-xl leading-none">&gt;</span>
                        </a>
                        <a href="{{ url('/credentials') }}" class="border-4 px-4 py-3 font-black uppercase flex items-center justify-between transition-all duration-200 {{ Request::is('credentials*') ? 'border-black bg-[#FDE047] text-black shadow-[4px_4px_black]' : 'border-transparent text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50' }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="key-round" class="h-5 w-5"></i>
                                API Keys / Kredensial
                            </span>
                            <span class="text-xl leading-none">&gt;</span>
                        </a>
                        <a href="{{ url('/billing') }}" class="border-4 px-4 py-3 font-black uppercase flex items-center justify-between transition-all duration-200 {{ Request::is('billing*') ? 'border-black bg-[#FDE047] text-black shadow-[4px_4px_black]' : 'border-transparent text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50' }}">
                            <span class="flex items-center gap-3">
                                <i data-lucide="credit-card" class="h-5 w-5"></i>
                                Billing / Langganan
                            </span>
                            <span class="text-xl leading-none">&gt;</span>
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <aside class="hidden lg:flex lg:flex-col w-64 h-auto flex-none border-r-4 border-black bg-white p-6 shadow-[4px_0px_0px_0px_rgba(0,0,0,1)] relative z-20">
            <h2 class="mb-6 text-lg font-black uppercase text-black tracking-widest border-b-4 border-black pb-2">Navigasi</h2>
            
            <nav class="flex flex-col gap-4">
                <a href="{{ url('/') }}" class="border-4 px-4 py-3 font-black uppercase flex items-center justify-between hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all duration-200 {{ Request::is('/') ? 'border-black bg-[#FDE047] text-black shadow-[4px_4px_black]' : 'border-transparent text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50' }}">
                    <span class="flex items-center gap-2.5">
                        <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                        Overview
                    </span>
                    <span class="text-lg leading-none font-bold">&gt;</span>
                </a>

                <a href="{{ url('/buckets') }}" class="border-4 px-4 py-3 font-black uppercase flex items-center justify-between hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all duration-200 {{ Request::is('buckets*') || Request::is('bucket*') ? 'border-black bg-[#FDE047] text-black shadow-[4px_4px_black]' : 'border-transparent text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50' }}">
                    <span class="flex items-center gap-2.5">
                        <i data-lucide="database" class="h-4 w-4"></i>
                        Buckets
                    </span>
                    <span class="text-lg leading-none font-bold">&gt;</span>
                </a>

                <a href="{{ url('/credentials') }}" class="border-4 px-4 py-3 font-black uppercase flex items-center justify-between hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all duration-200 {{ Request::is('credentials*') ? 'border-black bg-[#FDE047] text-black shadow-[4px_4px_black]' : 'border-transparent text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50' }}">
                    <span class="flex items-center gap-2.5">
                        <i data-lucide="key-round" class="h-4 w-4"></i>
                        API Keys
                    </span>
                    <span class="text-lg leading-none font-bold">&gt;</span>
                </a>

                <a href="{{ url('/billing') }}" class="border-4 px-4 py-3 font-black uppercase flex items-center justify-between hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all duration-200 {{ Request::is('billing*') ? 'border-black bg-[#FDE047] text-black shadow-[4px_4px_black]' : 'border-transparent text-slate-600 hover:text-black hover:border-black hover:shadow-[4px_4px_black] hover:bg-slate-50' }}">
                    <span class="flex items-center gap-2.5">
                        <i data-lucide="credit-card" class="h-4 w-4"></i>
                        Billing
                    </span>
                    <span class="text-lg leading-none font-bold">&gt;</span>
                </a>
            </nav>

            <div class="mt-auto pt-6 border-t-4 border-black">
                <div class="border-4 border-black bg-white p-4 shadow-[4px_4px_black]">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-none bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-none h-2 w-2 bg-[#34D399] border border-black"></span>
                        </span>
                        <span class="text-xs font-black uppercase tracking-wider text-black">MiniStack Engine</span>
                    </div>
                    <p class="text-[10px] font-bold text-slate-700 leading-tight">Terhubung pada jaringan virtual Docker.</p>
                </div>
            </div>
        </aside>

        <main class="flex-1 p-6 md:p-10 flex flex-col gap-10 bg-[#FDFBF7]">
            @yield('content')
        </main>
    </div>

    <div x-data="toastManager" 
         @toast-trigger.window="addToast($event.detail)" 
         class="fixed bottom-0 right-0 z-50 flex flex-col items-end justify-end gap-4 p-6 pointer-events-none sm:p-8"
         style="max-width: 450px; margin-left: auto;">
        
        <template x-for="toast in toasts" :key="toast.id">
            <div x-show="toast.visible"
                 x-transition:enter="transition ease-out duration-200 transform"
                 x-transition:enter-start="translate-y-4 opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-150 transform"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="w-full max-w-sm overflow-hidden border-4 border-black p-4 pointer-events-auto flex items-stretch shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] hover:shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-[2px] hover:translate-y-[2px] transition-all"
                 :class="{
                     'bg-[#34D399] text-black': toast.type === 'success',
                     'bg-[#FF4545] text-white': toast.type === 'error',
                     'bg-[#FDE047] text-black': toast.type === 'warning'
                 }">
                
                <div class="flex items-center justify-center mr-3 shrink-0">
                    <template x-if="toast.type === 'success'">
                        <i data-lucide="check-square" class="h-6 w-6 stroke-[3]"></i>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <i data-lucide="alert-octagon" class="h-6 w-6 stroke-[3]"></i>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <i data-lucide="alert-triangle" class="h-6 w-6 stroke-[3]"></i>
                    </template>
                </div>

                <div class="flex-1">
                    <p class="text-xs uppercase font-black tracking-wider" x-text="toast.title" :class="toast.type === 'error' ? 'text-white' : 'text-black'"></p>
                    <p class="mt-0.5 text-xs font-bold leading-tight" x-text="toast.message" :class="toast.type === 'error' ? 'text-white/90' : 'text-slate-800'"></p>
                </div>

                <div class="flex items-start ml-2">
                    <button @click="removeToast(toast.id)" class="transition-colors hover:scale-110" :class="toast.type === 'error' ? 'text-white' : 'text-black'">
                        <i data-lucide="x" class="h-4 w-4 stroke-[3]"></i>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('toastManager', () => ({
                toasts: [],
                addToast({ type, title, message, timeout = 5000 }) {
                    const id = Date.now();
                    this.toasts.push({ id, type, title, message, visible: true });
                    
                    setTimeout(() => lucide.createIcons(), 50);

                    setTimeout(() => {
                        this.removeToast(id);
                    }, timeout);
                },
                removeToast(id) {
                    const index = this.toasts.findIndex(t => t.id === id);
                    if (index !== -1) {
                        this.toasts[index].visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 200);
                    }
                }
            }));
        });

        window.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            @if(session('success'))
                window.dispatchEvent(new CustomEvent('toast-trigger', {
                    detail: {
                        type: 'success',
                        title: 'Sukses!',
                        message: @json(session('success'))
                    }
                }));
            @endif

            @if(session('error'))
                window.dispatchEvent(new CustomEvent('toast-trigger', {
                    detail: {
                        type: 'error',
                        title: 'Gagal!',
                        message: @json(session('error'))
                    }
                }));
            @endif

            @if($errors->any())
                window.dispatchEvent(new CustomEvent('toast-trigger', {
                    detail: {
                        type: 'error',
                        title: 'Validasi Gagal!',
                        message: @json($errors->first())
                    }
                }));
            @endif
        });
    </script>
    @yield('scripts')
</body>
</html>