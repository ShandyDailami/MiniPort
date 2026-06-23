@extends('layout.app')

@section('title', 'Bucket ' . $bucket->bucket_name)

@section('content')
<div class="flex flex-col gap-10" x-data="fileManager()">

    {{-- 1. BREADCRUMBS & RETRO HEADER --}}
    <div class="border-4 border-black bg-[#C4B5FD] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-slate-800 mb-1.5">
                <a href="{{ url('/buckets') }}" class="hover:underline">Buckets</a>
                <span>/</span>
                <span class="text-black">{{ $bucket->bucket_name }}</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black uppercase tracking-tight text-black flex items-center gap-2.5">
                <span>🪣 {{ $bucket->bucket_name }}</span>
            </h1>
            <div class="flex flex-wrap gap-2 mt-3">
                <span class="border-2 border-black bg-[#FDE047] px-2.5 py-0.5 text-xs font-black uppercase shadow-[2px_2px_black]">
                    🌐 {{ $bucket->region }}
                </span>
                <span class="border-2 border-black bg-white px-2.5 py-0.5 text-xs font-black uppercase shadow-[2px_2px_black]">
                    📂 {{ count($objects) }} Berkas Tersimpan
                </span>
            </div>
        </div>

        <a href="{{ url('/buckets') }}" class="border-4 border-black bg-white text-black px-5 py-2.5 font-black uppercase text-xs shadow-[4px_4px_black] hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_black] transition-all text-center shrink-0">
            <- Kembali ke Daftar
        </a>
    </div>

    {{-- 2. MODAL PRESIGNED SHARE URL (RETRO ONE-TIME POP-UP) --}}
    @if(session('share_url'))
    <div x-data="{ openShareModal: true }" x-show="openShareModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="w-full max-w-xl border-4 border-black bg-[#FDE047] p-6 shadow-[10px_10px_0px_rgba(0,0,0,1)] rounded-none relative">
            <div class="flex items-center justify-between border-b-2 border-black pb-3 mb-4">
                <h3 class="text-lg font-black uppercase text-black flex items-center gap-2">
                    <span>🔗 Bagikan Tautan Berkas</span>
                </h3>
                <button @click="openShareModal = false" class="border-2 border-black bg-[#FF4545] p-1 text-white shadow-[1px_1px_black] hover:translate-y-0.5 transition-all">
                    <i data-lucide="x" class="h-4 w-4 stroke-[3]"></i>
                </button>
            </div>

            <p class="text-xs font-bold text-slate-800 mb-4 leading-relaxed">
                Tautan unduhan sementara di bawah ini aman dan akan kedaluwarsa secara otomatis dalam beberapa menit sesuai kebijakan proteksi MiniStack S3.
            </p>

            <div x-data="{ copied: false, shareLink: '{{ session('share_url') }}' }" class="bg-white border-4 border-black p-4 shadow-[4px_4px_black] mb-6 flex items-center gap-3">
                <input 
                    type="text" 
                    readonly 
                    :value="shareLink"
                    class="flex-1 font-mono text-xs font-bold text-slate-800 bg-slate-100 p-2 border-2 border-black rounded-none outline-none select-all truncate"
                >
                <button 
                    @click="
                        navigator.clipboard.writeText(shareLink);
                        copied = true;
                        setTimeout(() => copied = false, 2500);
                    "
                    class="border-2 border-black bg-[#34D399] px-4 py-2 font-black text-xs uppercase shadow-[2px_2px_black] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none transition-all flex items-center gap-1.5 shrink-0"
                >
                    <i data-lucide="copy" class="h-3.5 w-3.5"></i>
                    <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                </button>
            </div>

            <div class="flex justify-end">
                <button @click="openShareModal = false" class="border-2 border-black bg-black text-white px-4 py-2 text-xs font-black uppercase shadow-[2px_2px_black] hover:translate-y-0.5 transition-all">
                    Tutup Dialog
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- 3. GRID UTAMA: UPLOAD & FILE LIST --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-10 items-start">
        
        {{-- KOLOM KIRI: DROPZONE FILE UPLOAD (Neobrutalist Box) --}}
        <div class="lg:col-span-1 border-4 border-black bg-[#67E8F9] p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none">
            <h2 class="text-xl font-black uppercase mb-4">📤 Unggah Berkas</h2>
            <p class="font-bold text-slate-800 text-xs mb-6 leading-relaxed">
                Pilih atau seret berkas Anda di bawah ini. Maksimal kapasitas unggah lokal Sandbox dibatasi hingga 10 Megabytes.
            </p>

            <form 
                action="{{ url('/bucket/' . $bucket->id . '/objects') }}" 
                method="POST" 
                enctype="multipart/form-data" 
                class="m-0 space-y-4"
                @submit="uploading = true"
            >
                @csrf
                <div 
                    class="border-4 border-dashed border-black bg-white hover:bg-slate-50 transition-colors p-6 text-center cursor-pointer relative"
                    @click="$refs.fileInput.click()"
                >
                    <input 
                        type="file" 
                        name="object_file" 
                        x-ref="fileInput" 
                        @change="fileSelected"
                        class="hidden"
                        required
                    >
                    <div class="text-4xl mb-2 select-none">📁</div>
                    <p class="font-black text-xs uppercase text-black" x-text="selectedFileName || 'Pilih Berkas'"></p>
                    <p class="text-[10px] font-bold text-slate-500 mt-1" x-show="!selectedFileName">Klik untuk mencari berkas di disk lokal</p>
                </div>

                {{-- Progress Bar Unggah Simulasi --}}
                <div x-show="uploading" class="space-y-1.5" style="display: none;">
                    <div class="flex items-center justify-between text-[10px] font-black uppercase">
                        <span class="text-black">Sedang Mengirim berkas...</span>
                        <span class="text-purple-800">Local S3 Upload</span>
                    </div>
                    <div class="h-4 w-full border-2 border-black bg-slate-100 overflow-hidden relative">
                        <div class="h-full bg-[#C4B5FD] animate-pulse" style="width: 75%"></div>
                    </div>
                </div>

                <button 
                    type="submit" 
                    :disabled="!hasFile || uploading"
                    class="w-full border-4 border-black bg-[#FDE047] text-black px-4 py-3 font-black uppercase text-xs text-center shadow-[2px_2px_black] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[1px_1px_black] active:translate-y-0.5 active:shadow-none transition-all disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    🚀 Unggah ke S3 Bucket
                </button>
            </form>
        </div>

        {{-- KOLOM KANAN: TABEL BERKAS S3 (File Manager Grid) --}}
        <div class="lg:col-span-3 border-4 border-black bg-white p-6 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] rounded-none flex flex-col gap-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b-4 border-black pb-4">
                <h2 class="text-xl font-black uppercase">📁 Berkas Tersimpan</h2>
                
                {{-- Input Pencarian Lokal Berkas --}}
                <div class="relative w-full sm:w-64">
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        placeholder="Cari nama berkas..."
                        class="w-full border-2 border-black p-2 pr-10 text-xs font-bold text-black focus:outline-none focus:bg-slate-50 rounded-none shadow-[2px_2px_black]"
                    >
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <i data-lucide="search" class="h-4 w-4 text-black stroke-[2.5]"></i>
                    </div>
                </div>
            </div>

            {{-- TABEL BERKAS --}}
           
            <div class="overflow-x-auto border-4 border-black shadow-[4px_4px_black]">
                <table class="w-full text-left border-collapse font-bold table-fixed md:table-auto">
                    <thead>
                        <tr class="bg-[#E2E8F0] border-b-4 border-black text-xs uppercase tracking-wider">
                            <th class="p-4 border-r-4 border-black w-1/2 md:w-auto">Nama Berkas (Key)</th>
                            <th class="p-4 border-r-4 border-black hidden sm:table-cell w-24">Ukuran</th>
                            <th class="p-4 border-r-4 border-black hidden md:table-cell w-48">Terakhir Diubah</th>
                            <th class="p-4 text-center w-40">Aksi / Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-4 divide-black text-xs">
                        @forelse($objects as $obj)
                            <tr 
                                x-show="matchesSearch('{{ $obj['key'] }}')"
                                class="hover:bg-[#FDFBF7] transition-colors"
                            >
                                <td class="p-4 border-r-4 border-black truncate max-w-[200px] sm:max-w-xs select-all text-black">
                                    <div class="flex items-center gap-2">
                                        <span class="p-1 border-2 border-black shrink-0" style="background-color: #FECDD3">
                                            <i :data-lucide="getFileIcon('{{ $obj['key'] }}')" class="h-4 w-4 text-black stroke-[2.5]"></i>
                                        </span>
                                        <span class="font-bold truncate" title="{{ $obj['key'] }}">{{ $obj['key'] }}</span>
                                    </div>
                                </td>

                                <td class="p-4 border-r-4 border-black hidden sm:table-cell font-mono text-slate-700 whitespace-nowrap">
                                    {{ $obj['size_text'] ?? number_format($obj['size'] / 1024, 2) . ' KB' }}
                                </td>

                                <td class="p-4 border-r-4 border-black hidden md:table-cell text-slate-500 font-semibold whitespace-nowrap">
                                    {{ is_numeric($obj['last_modified']) ? date('d M Y, H:i', $obj['last_modified']) : $obj['last_modified'] }}
                                </td>

                                <td class="p-3">
                                    <div class="flex items-center justify-center gap-2 flex-wrap">
                                        <a 
                                            href="{{ url('/bucket/' . $bucket->id . '/objects/download') }}?key={{ urlencode($obj['key']) }}"
                                            class="border-2 border-black bg-[#34D399] p-1.5 shadow-[2px_2px_black] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[1px_1px_black] transition-all text-black"
                                            title="Unduh Berkas"
                                        >
                                            <i data-lucide="download" class="h-3.5 w-3.5 stroke-[2.5]"></i>
                                        </a>

                                        <a 
                                            href="{{ url('/bucket/' . $bucket->id . '/objects/share') }}?key={{ urlencode($obj['key']) }}"
                                            class="border-2 border-black bg-[#FDE047] p-1.5 shadow-[2px_2px_black] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[1px_1px_black] transition-all text-black"
                                            title="Bagikan Berkas"
                                        >
                                            <i data-lucide="share-2" class="h-3.5 w-3.5 stroke-[2.5]"></i>
                                        </a>

                                        <div x-data="{ openDeleteConfirm: false }">
                                            <button 
                                                @click="openDeleteConfirm = true" 
                                                type="button" 
                                                class="border-2 border-black bg-[#FF4545] p-1.5 text-white shadow-[2px_2px_black] hover:translate-x-[1px] hover:translate-y-[1px] hover:shadow-[1px_1px_black] transition-all"
                                                title="Hapus Berkas"
                                            >
                                                <i data-lucide="trash-2" class="h-3.5 w-3.5 stroke-[2.5]"></i>
                                            </button>

                                            <div x-show="openDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
                                                <div class="w-full max-w-sm border-4 border-black bg-[#FF4545] text-white p-6 shadow-[8px_8px_black] rounded-none">
                                                    <h3 class="text-sm uppercase font-black tracking-wider border-b-2 border-black pb-2 mb-3">⚠️ Hapus Berkas dari S3?</h3>
                                                    <p class="text-xs font-bold leading-normal mb-5 text-white/95 text-left">
                                                        Apakah Anda yakin ingin menghapus berkas <span class="font-mono bg-black/25 px-1 py-0.5 rounded text-yellow-200 break-all inline-block">{{ $obj['key'] }}</span> secara permanen dari bucket ini?
                                                    </p>
                                                    <div class="flex justify-end gap-3">
                                                        <button @click="openDeleteConfirm = false" type="button" class="border-2 border-black bg-white text-black px-3 py-1.5 text-[10px] font-black uppercase shadow-[2px_2px_black]">
                                                            Batal
                                                        </button>
                                                        <form action="{{ url('/bucket/' . $bucket->id . '/objects') }}" method="POST" class="m-0">
                                                            @csrf
                                                            @method('DELETE')
                                                            <input type="hidden" name="key" value="{{ $obj['key'] }}">
                                                            <button type="submit" class="border-2 border-black bg-black text-white px-3 py-1.5 text-[10px] font-black uppercase shadow-[2px_2px_black] hover:translate-y-0.5 transition-all">
                                                                Ya, Hapus!
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-8 text-center bg-slate-50 text-slate-500 uppercase font-black">
                                    📭 Bucket ini kosong. Unggah berkas pertama Anda menggunakan panel di sebelah kiri!
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

@section('scripts')
<script>
    function fileManager() {
        return {
            selectedFileName: '',
            hasFile: false,
            uploading: false,
            searchQuery: '',
            
            fileSelected(event) {
                const file = event.target.files[0];
                if (file) {
                    this.selectedFileName = file.name;
                    this.hasFile = true;
                } else {
                    this.selectedFileName = '';
                    this.hasFile = false;
                }
            },
            
            matchesSearch(key) {
                if (!this.searchQuery) return true;
                return key.toLowerCase().includes(this.searchQuery.toLowerCase());
            },

            getFileIcon(filename) {
                const ext = filename.split('.').pop().toLowerCase();
                const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp'];
                const textExtensions = ['txt', 'md', 'html', 'css', 'json', 'js', 'php', 'py'];
                const archiveExtensions = ['zip', 'rar', 'tar', 'gz', '7z'];
                const docExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

                if (imageExtensions.includes(ext)) {
                    return 'file-image';
                } else if (textExtensions.includes(ext)) {
                    return 'file-code';
                } else if (archiveExtensions.includes(ext)) {
                    return 'file-archive';
                } else if (docExtensions.includes(ext)) {
                    return 'file-text';
                }
                return 'file';
            }
        }
    }
</script>
@endsection