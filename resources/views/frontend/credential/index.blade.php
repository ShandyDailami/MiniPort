@extends('layout.app')

@section('title', 'API Keys')

@section('content')
<div class="flex flex-col gap-10">

    {{-- 1. HEADER HALAMAN --}}
    <div class="border-4 border-black bg-[#C4B5FD] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
        <h1 class="text-3xl font-black uppercase tracking-tight text-black">🔑 API Keys & Kredensial</h1>
        <p class="font-bold text-slate-800 mt-1">
            Gunakan API Key aktif untuk menghubungkan client, SDK, atau aplikasi pihak ketiga Anda ke MiniStack S3 Engine.
        </p>
    </div>

    {{-- 2. NOTIFIKASI KUNCI BARU (ONE-TIME SHOW MODAL RETRO) --}}
    @if(session('new_secret_key'))
    <div x-data="{ openModal: true }" x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="w-full max-w-2xl border-4 border-black bg-[#FDE047] p-8 shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] relative rounded-none">
            
            <div class="flex items-start justify-between border-b-4 border-black pb-4 mb-6">
                <div class="flex items-center gap-3">
                    <span class="text-4xl">⚠️</span>
                    <h2 class="text-2xl font-black uppercase tracking-tight text-black">Simpan Secret Key Anda!</h2>
                </div>
                <button @click="openModal = false" class="border-2 border-black bg-[#FF4545] p-1.5 shadow-[2px_2px_black] hover:translate-y-0.5 transition-all">
                    <i data-lucide="x" class="h-5 w-5 text-white stroke-[3]"></i>
                </button>
            </div>

            <p class="font-bold text-slate-900 text-sm mb-4">
                Aturan keamanan standar S3: Demi keamanan, **Secret Key hanya akan ditampilkan SATU KALI ini**. Jika Anda me-refresh halaman atau menutup kotak ini, kunci rahasia tidak akan bisa dipulihkan kembali.
            </p>

            <div class="space-y-4 bg-white border-4 border-black p-5 shadow-[6px_6px_black] mb-6">
                <!-- Access Key Display -->
                <div>
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">Access Key ID:</label>
                    <div class="flex items-center gap-2 bg-slate-100 border-2 border-black p-2 font-mono text-sm font-bold truncate">
                        <span class="flex-1 select-all text-black">{{ session('new_access_key') }}</span>
                    </div>
                </div>

                <!-- Secret Key Display -->
                <div x-data="{ copied: false, secret: '{{ session('new_secret_key') }}' }">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-1">Secret Access Key:</label>
                    <div class="flex items-center gap-2 bg-slate-100 border-2 border-black p-2 font-mono text-sm font-bold text-purple-800">
                        <span class="flex-1 select-all tracking-wider">{{ session('new_secret_key') }}</span>
                        <button 
                            @click="
                                navigator.clipboard.writeText(secret);
                                copied = true;
                                setTimeout(() => copied = false, 2000);
                            " 
                            type="button" 
                            class="border-2 border-black bg-[#34D399] px-3 py-1 font-black text-xs uppercase shadow-[2px_2px_black] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all flex items-center gap-1 text-black"
                        >
                            <i data-lucide="copy" class="h-3 w-3"></i>
                            <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button @click="openModal = false" class="border-4 border-black bg-black text-white px-6 py-3 font-black uppercase shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all">
                    Saya Sudah Menyimpannya ->
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- 3. UTAMA: GRID GENERATE KEY & DAFTAR KEY --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">
        
        <!-- KOLOM KIRI: GENERATE NEW KEY CARD -->
        <div class="border-4 border-black bg-[#67E8F9] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
            <h2 class="text-xl font-black uppercase mb-4">🔑 Buat Kredensial</h2>
            <p class="font-bold text-slate-800 text-sm mb-6 leading-relaxed">
                Butuh API key baru? Setiap user disarankan hanya memiliki maksimal 2 kunci aktif demi keamanan rotasi berkala.
            </p>

            <form action="/credentials" method="POST" class="m-0">
                @csrf
                <button type="submit" class="w-full border-4 border-black bg-[#FDE047] text-black px-6 py-4 font-black uppercase text-center shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all cursor-pointer">
                    + Generate API Key Baru
                </button>
            </form>
        </div>

        <!-- KOLOM KANAN: DAFTAR API KEYS TERSEDIA -->
        <div class="lg:col-span-2 border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
            <h2 class="text-xl font-black uppercase mb-6">📜 Daftar Kredensial Anda</h2>

            <div class="overflow-x-auto border-4 border-black shadow-[4px_4px_black]">
                <table class="w-full text-left border-collapse font-bold">
                    <thead>
                        <tr class="bg-[#E2E8F0] border-b-4 border-black text-xs uppercase tracking-wider">
                            <th class="p-4 border-r-4 border-black">Access Key ID</th>
                            <th class="p-4 border-r-4 border-black">Dibuat Pada</th>
                            <th class="p-4 border-r-4 border-black">Status</th>
                            <th class="p-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-black text-sm">
                        @forelse($credentials as $cred)
                        <tr class="hover:bg-[#FDFBF7] transition-colors">
                            <!-- Access Key -->
                            <td class="p-4 border-r-4 border-black font-mono text-purple-700 break-all select-all">
                                {{ $cred->access_key }}
                            </td>
                            <!-- Tanggal Pembuatan -->
                            <td class="p-4 border-r-4 border-black text-xs text-slate-600">
                                {{ $cred->created_at->format('d M Y, H:i') }}
                                <span class="block text-[10px] text-slate-400">({{ $cred->created_at->diffForHumans() }})</span>
                            </td>
                            <!-- Status Badges -->
                            <td class="p-4 border-r-4 border-black">
                                @if($cred->status == 'active')
                                <span class="bg-[#34D399] border-2 border-black px-2 py-0.5 text-xs font-black uppercase shadow-[2px_2px_black] inline-block">
                                    Active
                                </span>
                                @else
                                <span class="bg-[#FF4545] text-white border-2 border-black px-2 py-0.5 text-xs font-black uppercase shadow-[2px_2px_black] inline-block">
                                    Inactive
                                </span>
                                @endif
                            </td>
                            <!-- Tindakan Revoke -->
                            <td class="p-4">
                                @if($cred->status == 'active')
                                <div x-data="{ confirmRevoke: false }">
                                    <!-- Button Revoke Triger Modal Konfirmasi -->
                                    <button 
                                        @click="confirmRevoke = true" 
                                        type="button" 
                                        class="border-2 border-black bg-[#FF4545] text-white px-3 py-1 text-xs font-black uppercase shadow-[2px_2px_black] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[1px_1px_black] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all cursor-pointer"
                                    >
                                        Cabut Akses
                                    </button>

                                    <!-- Retro Danger Modal Konfirmasi -->
                                    <div x-show="confirmRevoke" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
                                        <div class="w-full max-w-md border-4 border-black bg-[#FF4545] text-white p-6 shadow-[8px_8px_black] rounded-none">
                                            <h3 class="text-lg font-black uppercase border-b-2 border-black pb-2 mb-3">⚠️ Cabut Akses Kredensial?</h3>
                                            <p class="text-xs font-bold leading-relaxed mb-4 text-white/90">
                                                Tindakan ini tidak bisa dibatalkan. Aplikasi yang menggunakan Access Key <span class="font-mono bg-black/20 px-1 py-0.5 rounded">{{ $cred->access_key }}</span> akan kehilangan akses ke MiniStack secara instan!
                                            </p>
                                            <div class="flex justify-end gap-3">
                                                <button @click="confirmRevoke = false" type="button" class="border-2 border-black bg-white text-black px-3 py-1.5 text-xs font-black uppercase shadow-[2px_2px_black]">
                                                    Batal
                                                </button>
                                                <form action="/credentials/{{ $cred->id }}/revoke" method="POST" class="m-0">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="border-2 border-black bg-black text-white px-3 py-1.5 text-xs font-black uppercase shadow-[2px_2px_black] hover:translate-y-0.5 transition-all">
                                                        Ya, Cabut Akses
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <span class="text-xs text-slate-400 italic">No Action</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center bg-slate-50 text-slate-500 uppercase font-black">
                                📭 Anda belum memiliki API Key aktif. Buat satu di kolom sebelah kiri!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection