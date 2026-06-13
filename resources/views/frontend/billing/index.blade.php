@extends('layout.app')

@section('title', 'Billing - MiniPort Cloud')

@section('content')
<div class="min-h-screen bg-[#FDFBF7] p-6 md:p-10 text-black">

    <div class="max-w-7xl mx-auto">

        <div class="mb-8">
            <a href="/"
               class="inline-block border-4 border-black bg-white px-4 py-2 font-black uppercase shadow-[4px_4px_black]">
                &larr; Dashboard
            </a>
        </div>

        <div class="mb-10 border-b-4 border-black pb-6">
            <h1 class="text-4xl font-black uppercase">💳 Billing & Subscription</h1>
            <p class="font-bold text-slate-700 mt-2">
                Pilih paket Object Storage dan kelola invoice MiniPort.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 border-4 border-black bg-green-400 p-4 font-black shadow-[4px_4px_black]">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 border-4 border-black bg-red-500 text-white p-4 font-black shadow-[4px_4px_black]">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <section class="mb-10 border-4 border-black bg-[#C4B5FD] p-6 shadow-[8px_8px_black]">
            <h2 class="text-2xl font-black uppercase mb-4">Paket Aktif</h2>

            @if($activeSubscription)
                <div class="bg-white border-4 border-black p-5 shadow-[4px_4px_black]">
                    <p class="text-3xl font-black">
                        {{ $activeSubscription->plan->plan_name }}
                    </p>

                    <p class="font-bold mt-2">
                        Limit:
                        {{ number_format($activeSubscription->plan->storage_limit_mb) }} MB
                    </p>

                    <p class="font-bold">
                        Aktif sampai:
                        {{ $activeSubscription->end_date->format('d M Y') }}
                    </p>

                    <form
                        action="/billing/subscriptions/{{ $activeSubscription->id }}/cancel"
                        method="POST"
                        class="mt-5"
                        onsubmit="return confirm('Batalkan subscription aktif?');"
                    >
                        @csrf
                        @method('PATCH')

                        <button class="border-4 border-black bg-red-500 text-white px-4 py-2 font-black uppercase shadow-[4px_4px_black]">
                            Batalkan Subscription
                        </button>
                    </form>
                </div>
            @else
                <p class="font-black">
                    Belum ada subscription aktif. Limit fallback masih mengikuti konfigurasi default.
                </p>
            @endif
        </section>

        <section class="mb-12">
            <h2 class="text-3xl font-black uppercase mb-6">Pilih Paket</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach($plans as $plan)
                    <div class="border-4 border-black bg-white p-6 shadow-[8px_8px_black]">
                        <h3 class="text-3xl font-black uppercase">
                            {{ $plan->plan_name }}
                        </h3>

                        <p class="text-4xl font-black mt-5">
                            @if((float) $plan->price <= 0)
                                Gratis
                            @else
                                Rp{{ number_format($plan->price, 0, ',', '.') }}
                            @endif
                        </p>

                        <p class="font-bold text-lg mt-4">
                            Storage:
                            {{ number_format($plan->storage_limit_mb) }} MB
                        </p>

                        <p class="font-bold text-slate-600 mt-2">
                            Masa aktif 30 hari.
                        </p>

                        <form
                            action="/billing/subscribe/{{ $plan->id }}"
                            method="POST"
                            class="mt-6"
                        >
                            @csrf

                            <button class="w-full border-4 border-black bg-[#34D399] px-5 py-3 font-black uppercase shadow-[4px_4px_black]">
                                Pilih Paket
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="border-4 border-black bg-white p-6 shadow-[8px_8px_black]">
            <h2 class="text-3xl font-black uppercase mb-6">Riwayat Invoice</h2>

            <div class="overflow-x-auto">
                <table class="w-full border-4 border-black">
                    <thead class="bg-[#FDE047]">
                        <tr class="border-b-4 border-black">
                            <th class="p-4 text-left">Invoice</th>
                            <th class="p-4 text-left">Paket</th>
                            <th class="p-4 text-left">Jumlah</th>
                            <th class="p-4 text-left">Status</th>
                            <th class="p-4 text-left">Jatuh Tempo</th>
                            <th class="p-4 text-left">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr class="border-b-2 border-black">
                                <td class="p-4 font-mono font-bold">
                                    {{ $invoice->invoice_number }}
                                </td>

                                <td class="p-4 font-bold">
                                    {{ $invoice->subscription?->plan?->plan_name ?? '-' }}
                                </td>

                                <td class="p-4 font-bold">
                                    Rp{{ number_format($invoice->amount, 0, ',', '.') }}
                                </td>

                                <td class="p-4">
                                    <span class="border-2 border-black px-3 py-1 font-black uppercase
                                        {{ $invoice->status === 'paid'
                                            ? 'bg-green-400'
                                            : ($invoice->status === 'pending'
                                                ? 'bg-yellow-300'
                                                : 'bg-slate-300') }}">
                                        {{ $invoice->status }}
                                    </span>
                                </td>

                                <td class="p-4 font-bold">
                                    {{ $invoice->due_date->format('d M Y') }}
                                </td>

                                <td class="p-4">
                                    @if($invoice->status === 'pending')
                                        <form
                                            action="/billing/invoices/{{ $invoice->id }}/pay"
                                            method="POST"
                                        >
                                            @csrf

                                            <button class="border-4 border-black bg-[#34D399] px-4 py-2 font-black uppercase shadow-[3px_3px_black]">
                                                Bayar Simulasi
                                            </button>
                                        </form>
                                    @else
                                        <span class="font-bold">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center font-black uppercase">
                                    Belum ada invoice.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</div>
@endsection