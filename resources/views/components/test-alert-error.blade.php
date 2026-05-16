@props(['message'])

<div x-data="{ isModalOpen: true }">

    <div x-show="isModalOpen"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">

        <div class="bg-white border-2 border-black px-6 py-8 rounded-md shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] w-1/3 text-center relative"
            @click.away="isModalOpen = false" >
            <h2 class="text-2xl font-bold mb-4">Pengumuman</h2>
            <p class="text-slate-600 font-medium mb-6">
                Ini adalah contoh pop-up. Saat ini muncul, Anda tidak bisa mengklik form login atau elemen apa pun di belakangnya.
            </p>

            <!-- Tombol Tutup -->
            <button @click="isModalOpen = false" class="border-2 border-black px-8 py-2 rounded-md bg-[#F9C25B] text-black font-bold shadow-[4px_4px_black] hover:-translate-y-1 hover:shadow-[6px_6px_black] transition-all">
                Mengerti
            </button>
        </div>

    </div>

</div>
