@extends('layouts.erp')

@section('title', 'Detail Transaksi')
@section('subtitle', $transaction->invoice_number)

@section('content')
    @php
        $steps = ['ordered' => 'Dipesan', 'processing' => 'Diproses', 'ready' => 'Siap Diambil', 'completed' => 'Selesai'];
        $current = array_search($transaction->status, array_keys($steps), true);
    @endphp

    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            @if ($current !== false)
                <div class="flex flex-1 items-center">
                    @foreach ($steps as $key => $label)
                        @php($done = array_search($key, array_keys($steps), true) <= $current)
                        <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                            <div class="flex flex-col items-center gap-1">
                                <span class="grid h-8 w-8 place-items-center rounded-full text-xs font-bold {{ $done ? 'bg-red-600 text-white' : 'bg-slate-200 text-slate-500' }}">✓</span>
                                <span class="text-[10px] font-semibold {{ $done ? 'text-red-600' : 'text-slate-400' }}">{{ $label }}</span>
                            </div>
                            @unless ($loop->last)
                                <span class="mx-1 mb-4 h-1 flex-1 rounded {{ $done ? 'bg-red-400' : 'bg-slate-200' }}"></span>
                            @endunless
                        </div>
                    @endforeach
                </div>
            @else
                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold uppercase text-rose-700">{{ $transaction->status }}</span>
            @endif

            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('transactions.status', $transaction) }}" class="flex items-center gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                        @foreach (['ordered', 'processing', 'ready', 'completed', 'cancelled'] as $s)
                            <option value="{{ $s }}" @selected($transaction->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Update</button>
                </form>
                <a href="{{ route('transactions.print', $transaction) }}" target="_blank" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cetak Nota</a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-bold text-slate-900">Item Transaksi</h2>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Item</th>
                            <th class="px-5 py-3 text-center">Qty</th>
                            <th class="px-5 py-3 text-right">Harga</th>
                            <th class="px-5 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($transaction->items as $item)
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-900">{{ $item->name }}</p>
                                    <p class="text-xs uppercase text-slate-400">{{ $item->item_type }}</p>
                                </td>
                                <td class="px-5 py-3 text-center text-slate-600">{{ $item->quantity }}</td>
                                <td class="px-5 py-3 text-right text-slate-600">@idr($item->price)</td>
                                <td class="px-5 py-3 text-right font-semibold text-slate-900">@idr($item->subtotal)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($transaction->payments->isNotEmpty())
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h2 class="text-sm font-bold text-slate-900">Riwayat Pembayaran</h2>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($transaction->payments as $payment)
                            <div class="flex items-center justify-between px-5 py-3 text-sm">
                                <div>
                                    <p class="font-semibold text-slate-900 capitalize">{{ $payment->payment_method }}</p>
                                    <p class="text-xs text-slate-500">{{ $payment->paid_at?->format('d/m/Y H:i') }} {{ $payment->note ? '· '.$payment->note : '' }}</p>
                                </div>
                                <span class="font-bold text-slate-900">@idr($payment->amount)</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-bold text-slate-900">Ringkasan</h2>
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-semibold text-slate-800">@idr($transaction->subtotal)</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Diskon {!! $transaction->promotion ? '('.e($transaction->promotion->name).')' : '' !!}</dt><dd class="font-semibold text-rose-600">-@idr($transaction->discount_amount)</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-2.5 text-base font-extrabold"><dt class="text-slate-900">Total</dt><dd class="text-slate-900">@idr($transaction->total_amount)</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Dibayar</dt><dd class="font-semibold text-red-600">@idr($transaction->amountPaid())</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Sisa Tagihan</dt><dd class="font-semibold text-rose-600">@idr($transaction->balanceDue())</dd></div>
                </dl>
                <div class="mt-4 flex flex-wrap gap-2 text-[11px] font-semibold uppercase tracking-wider">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-600 capitalize">{{ $transaction->payment_method }}</span>
                    <span class="rounded-full px-2.5 py-1 {{ $transaction->payment_status === 'paid' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ $transaction->payment_status }}</span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-bold text-slate-900">Customer</h2>
                @if ($transaction->customer)
                    <p class="font-semibold text-slate-900">{{ $transaction->customer->name }}</p>
                    <p class="text-sm text-slate-500">{{ $transaction->customer->phone }}</p>
                    <p class="mt-1 font-mono text-xs font-bold text-red-700">{{ $transaction->customer->member_id }}</p>
                    <a href="{{ route('customers.show', $transaction->customer) }}" class="mt-3 block text-xs font-semibold text-red-600 hover:underline">Lihat profil →</a>
                @endif
                @if ($transaction->prescription)
                    <a href="{{ route('prescriptions.show', $transaction->prescription) }}" class="mt-3 block text-xs font-semibold text-red-600 hover:underline">Lihat resep terkait →</a>
                @endif
                <p class="mt-4 text-xs text-slate-400">Kasir: {{ $transaction->staff?->name ?? '-' }} · {{ $transaction->store?->name ?? '-' }}</p>
            </div>
        </div>
    </div>
@endsection
