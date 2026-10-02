@extends('layouts.member')

@section('title', 'Detail Transaksi')

@section('content')
    @php
        $steps = ['ordered' => 'Dipesan', 'processing' => 'Diproses', 'ready' => 'Siap Diambil', 'completed' => 'Selesai'];
        $current = array_search($transaction->status, array_keys($steps), true);
    @endphp

    <a href="{{ route('portal.transactions') }}" class="mb-4 inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500">
        <i data-lucide="arrow-left" class="h-4 w-4"></i> Riwayat Transaksi
    </a>

    <!-- Invoice -->
    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-red-900 p-5 text-white shadow-xl">
        <p class="text-[10px] uppercase tracking-[0.25em] text-red-400">Nomor Nota</p>
        <p class="mt-2 font-mono text-lg font-extrabold tracking-wide">{{ $transaction->invoice_number }}</p>
        <div class="mt-1 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-400">
            <span>{{ optional($transaction->transaction_date)->format('d M Y, H:i') }}</span>
            <span>{{ $transaction->store?->name ?? '-' }}</span>
        </div>

        @if ($current !== false)
            <div class="mt-5 flex items-center">
                @foreach ($steps as $key => $label)
                    @php($done = array_search($key, array_keys($steps), true) <= $current)
                    <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                        <div class="flex flex-col items-center gap-1">
                            <span class="grid h-7 w-7 place-items-center rounded-full text-[10px] font-bold {{ $done ? 'bg-red-500 text-white' : 'bg-white/10 text-slate-400' }}">✓</span>
                            <span class="text-[9px] font-semibold {{ $done ? 'text-red-300' : 'text-slate-500' }}">{{ $label }}</span>
                        </div>
                        @unless ($loop->last)
                            <span class="mx-1 mb-4 h-1 flex-1 rounded {{ $done ? 'bg-red-400/70' : 'bg-white/10' }}"></span>
                        @endunless
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-4 rounded-lg bg-white/10 px-3 py-2 text-center text-xs font-bold uppercase">{{ $transaction->status }}</p>
        @endif
    </div>

    <!-- Ringkasan -->
    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Ringkasan Pembayaran</h2>
            <span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase {{ $transaction->payment_status === 'paid' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                {{ $transaction->payment_status === 'paid' ? 'Lunas' : 'DP' }}
            </span>
        </div>

        <dl class="space-y-2.5 text-sm">
            <div class="flex justify-between">
                <dt class="text-slate-500">Subtotal</dt>
                <dd class="font-semibold text-slate-800">@idr($transaction->subtotal)</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Diskon {!! $transaction->promotion ? '('.e($transaction->promotion->name).')' : '' !!}</dt>
                <dd class="font-semibold text-rose-600">-@idr($transaction->discount_amount)</dd>
            </div>
            <div class="flex justify-between border-t border-slate-100 pt-2.5 text-base font-extrabold">
                <dt class="text-slate-900">Total</dt>
                <dd class="text-slate-900">@idr($transaction->total_amount)</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Sudah Dibayar</dt>
                <dd class="font-semibold text-red-600">@idr($transaction->amountPaid())</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Sisa Tagihan</dt>
                <dd class="font-semibold text-rose-600">@idr($transaction->balanceDue())</dd>
            </div>
        </dl>

        <p class="mt-4 text-xs capitalize text-slate-500">Metode: <b class="text-slate-700">{{ str_replace('_', ' ', $transaction->payment_method) }}</b></p>
    </section>

    <!-- Item per pemakai -->
    <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-sm font-bold text-slate-900">Item Transaksi</h2>
        <p class="mb-4 text-xs text-slate-500">Dipisah per pemakai (user)</p>

        <div class="space-y-4">
            @foreach ($groups as $group)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-red-700">
                            Pemakai: {{ $group['label'] }}
                        </p>
                        <span class="flex items-center gap-1.5 text-[11px] text-slate-500">
                            <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-red-700 shadow-sm">{{ $group['status_label'] }}</span>
                            <span>{{ $group['items']->count() }} item</span>
                        </span>
                    </div>

                    @if ($group['ro1'] || $group['ro2'])
                        <p class="mb-3 rounded-lg bg-white px-3 py-2 text-[11px] text-slate-500">
                            RO1 (Periksa Mata): <b class="text-slate-700">{{ $group['ro1'] ?: '-' }}</b>
                            · RO2 (Potong Lensa): <b class="text-slate-700">{{ $group['ro2'] ?: '-' }}</b>
                        </p>
                    @endif

                    <ul class="divide-y divide-slate-200/70">
                        @forelse ($group['items'] as $item)
                            <li class="flex items-start justify-between gap-3 py-2.5">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-slate-900">{{ $item->name }}</span>
                                    <span class="block text-[11px] uppercase tracking-wide text-slate-400">{{ $item->item_type }} · {{ $item->quantity }} × @idr($item->price)</span>
                                </span>
                                <span class="shrink-0 text-sm font-bold text-slate-900">@idr($item->subtotal)</span>
                            </li>
                        @empty
                            <li class="py-2.5 text-xs text-slate-400">Tidak ada item.</li>
                        @endforelse
                    </ul>

                    @if ($group['rx'])
                        <div class="mt-3 rounded-xl border border-slate-200 bg-white p-3">
                            <div class="mb-2 flex items-center justify-between text-[11px]">
                                <span class="font-bold uppercase tracking-wider text-slate-700">Resep Kacamata</span>
                                <span class="text-slate-400">{{ optional($group['rx']->examination_date)->format('d M Y') }}</span>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                @foreach (['od' => 'OD (Kanan)', 'os' => 'OS (Kiri)'] as $eye => $label)
                                    <div class="rounded-lg bg-slate-50 p-2.5">
                                        <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                                        <div class="grid grid-cols-2 gap-y-1 text-[11px]">
                                            @foreach (['sph' => 'SPH', 'cyl' => 'CYL', 'axis' => 'AXIS', 'add' => 'ADD'] as $k => $lbl)
                                                <span class="text-slate-500">{{ $lbl }}</span>
                                                <span class="text-right font-mono font-bold text-slate-900">{{ $group['rx']->{$eye.'_'.$k} ?: '—' }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-2 flex justify-between text-[11px] text-slate-500">
                                <span>PD Total: <b class="text-slate-800">{{ $group['rx']->pd_total ?: '-' }} mm</b></span>
                                <span>Fitting: <b class="text-slate-800">{{ $group['rx']->fitting_height ?: '-' }}</b></span>
                            </div>
                            <p class="mt-1 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
                                <span>{{ $group['rx']->doctor_or_optician }}</span>
                                <span class="rounded-full bg-slate-50 px-2 py-0.5 font-semibold text-slate-500">{{ $group['rx']->sourceLabel() }}</span>
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <!-- Riwayat pembayaran -->
    @if ($transaction->payments->isNotEmpty())
        <section class="mt-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-sm font-bold text-slate-900">Riwayat Pembayaran</h2>
            <ul class="divide-y divide-slate-100">
                @foreach ($transaction->payments as $payment)
                    <li class="flex items-center justify-between py-2.5 text-sm">
                        <span>
                            <span class="block font-semibold capitalize text-slate-900">{{ $payment->payment_method }}</span>
                            <span class="block text-xs text-slate-500">{{ $payment->paid_at?->format('d/m/Y H:i') }} {{ $payment->note ? '· '.$payment->note : '' }}</span>
                        </span>
                        <span class="font-bold text-slate-900">@idr($payment->amount)</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <a href="{{ route('portal.transactions') }}" class="mt-5 block rounded-xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">
        Kembali ke Riwayat Transaksi
    </a>
@endsection
