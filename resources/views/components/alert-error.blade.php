@props(['message'])

<div x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 4000)"
    x-transition:leave="transition ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="translate-y-4 opacity-0"
    class="border-2 border-black px-4 py-4 rounded-md bg-[#FF6F61] text-white font-medium outline-none shadow-[4px_4px_black] w-full flex justify-between items-center relative z-10 mt-2 mb-5" role="alert">
    <div>
        <strong class="font-bold text-sm">Fail!</strong>
        <p>{{ $message }}</p>
    </div>
</div>
