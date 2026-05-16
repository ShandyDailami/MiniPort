@props(['message'])

<div x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => window.location.href = '/login', 3000)"
    class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center"
    x-transition.opacity.duration.500ms>

        <div class="bg-white border-2 border-black px-8 py-10 rounded-md shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] w-1/3 text-center flex flex-col items-center">

            <!-- Icon Checklist Animasi -->
            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h2 class="text-3xl font-bold mb-2">Berhasil!</h2>
            <p class="text-slate-600 font-medium mb-6">
                {{ $message }}
            </p>

            <!-- Loading Spinner -->
            <div class="w-6 h-6 border-4 border-slate-300 border-t-[#D72F19] rounded-full animate-spin"></div>
        </div>
    </div>
