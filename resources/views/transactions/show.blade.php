@extends('layouts.erp')

@section('title', 'Detail Transaksi')
@section('subtitle', $transaction->sales_no)

@section('content')
    @php
        $steps = ['ordered' => 'Dipesan', 'processing' => 'Diproses', 'ready' => 'Siap Diambil', 'completed' => 'Selesai'];
        $current = array_search($transaction->sales_status, array_keys($steps), true);

        $wearerGroups = $transaction->wearerGroups();
        $gross = $transaction->items->sum(fn ($item) => $item->gross());
        $discount = $transaction->items->sum(fn ($item) => $item->discountAmount());
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
                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold uppercase text-rose-700">{{ $transaction->sales_status }}</span>
            @endif

            <div class="flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('transactions.status', $transaction) }}" class="flex items-center gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                        @foreach (['ordered', 'processing', 'ready', 'completed', 'cancelled'] as $s)
                            <option value="{{ $s }}" @selected($transaction->sales_status === $s)>{{ ucfirst($s) }}</option>
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
                    <p class="text-xs text-slate-500">Dipisah per pemakai (user) dalam transaksi ini</p>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">#</th>
                            <th class="px-5 py-3">Item</th>
                            <th class="px-5 py-3 text-center">Qty</th>
                            <th class="px-5 py-3 text-right">Harga</th>
                            <th class="px-5 py-3 text-center">Disc</th>
                            <th class="px-5 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    @foreach ($wearerGroups as $group)
                        <tbody>
                            <tr class="bg-red-50/60">
                                <td colspan="6" class="px-5 py-2.5">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-red-700">
                                        Pemakai: {{ $group['label'] }}
                                        @if ($group['prescriptions']->isNotEmpty())
                                            <span class="ml-2 rounded-full bg-white px-2 py-0.5 text-[10px] font-semibold normal-case tracking-normal text-slate-500">
                                                Resep: {{ $group['prescriptions']->first()->resep ?: '-' }} · {{ $group['prescriptions']->first()->jenis_lensa ?: 'Umum' }}
                                            </span>
                                        @endif
                                    </p>
                                    @if ($group['ro1'] || $group['ro2'])
                                        <p class="mt-1 text-[11px] font-semibold normal-case text-red-700/80">
                                            <span>RO1: {{ $group['ro1'] ?: '-' }}</span>
                                            <span class="ml-3">RO2: {{ $group['ro2'] ?: '-' }}</span>
                                        </p>
                                    @endif
                                </td>
                            </tr>
                            @forelse ($group['items'] as $item)
                                <tr>
                                    <td class="px-5 py-3 text-xs text-slate-400">{{ $item->sortno }}</td>
                                    <td class="px-5 py-3">
                                        <p class="font-semibold text-slate-900">{{ $item->item_description }}</p>
                                        <p class="text-xs uppercase text-slate-400">{{ $item->item_type }}</p>
                                    </td>
                                    <td class="px-5 py-3 text-center text-slate-600">{{ $item->qty }} {{ $item->unit }}</td>
                                    <td class="px-5 py-3 text-right text-slate-600">@idr($item->price)</td>
                                    <td class="px-5 py-3 text-center text-slate-600">{{ rtrim(rtrim(number_format((float) $item->disc_percent, 2), '0'), '.') }}%</td>
                                    <td class="px-5 py-3 text-right font-semibold text-slate-900">@idr($item->subtotal)</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-3 text-xs text-slate-400">Tidak ada item.</td></tr>
                            @endforelse

                            @if ($group['prescriptions']->isNotEmpty())
                                <tr class="bg-slate-50/60">
                                    <td colspan="6" class="px-5 py-3">
                                        <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-500">Resep Kacamata</p>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            @foreach ($group['prescriptions'] as $rx)
                                                <div class="rounded-xl border border-slate-200 bg-white p-3 text-xs">
                                                    <p class="mb-1.5 font-bold uppercase tracking-wider text-slate-600">Sisi {{ $rx->sisi }} {{ $rx->sisi === 'R' ? '(Kanan)' : '(Kiri)' }}</p>
                                                    <div class="grid grid-cols-2 gap-y-1">
                                                        @foreach (['sph' => 'SPH', 'cyl' => 'CYL', 'axis' => 'AXIS', 'add' => 'ADD', 'pd' => 'PD'] as $key => $label)
                                                            <span class="text-slate-500">{{ $label }}</span>
                                                            <span class="text-right font-mono font-bold text-slate-900">{{ $rx->{$key} ?: '—' }}</span>
                                                        @endforeach
                                                    </div>
                                                    <div class="mt-2 space-y-0.5 border-t border-slate-100 pt-2 text-[11px] text-slate-500">
                                                        <p>
                                                            Lensa:
                                                            <span class="font-semibold text-slate-700">
                                                                {{ $rx->itemno_lensa ? ($group['items']->firstWhere('itemno', $rx->itemno_lensa)?->item_description ?? '—') : '—' }}
                                                            </span>
                                                            · Qty {{ $rx->qty }}
                                                        </p>
                                                        <p>Sumber: <span class="font-semibold text-slate-700">{{ $rx->sourceLabel() }}</span></p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if ($group['prescriptions']->first()->ket)
                                            <p class="mt-2 text-[11px] text-slate-500">Catatan: {{ $group['prescriptions']->first()->ket }}</p>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    @endforeach
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
                <h2 class="mb-3 text-sm font-bold text-slate-900">Pemakai (User)</h2>
                <ul class="space-y-3">
                    @forelse ($wearerGroups as $group)
                        <li class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-semibold text-slate-900">{{ $group['label'] }}</span>
                                    <span class="block text-[11px] text-slate-500">{{ $group['items']->count() }} item</span>
                                </span>
                            </div>

                            @if ($group['prescriptions']->isNotEmpty())
                                <form method="POST" action="{{ route('transactions.group.update', $transaction) }}" class="mt-3 space-y-2.5 border-t border-slate-100 pt-3">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="username" value="{{ $group['label'] }}">

                                    <div class="grid gap-2">
                                        <label class="block">
                                            <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500">RO1 · Periksa Mata</span>
                                            <input type="text" name="ro1" maxlength="120" value="{{ old("ro1.{$group['label']}", $group['ro1']) }}" placeholder="Nama petugas refraksi"
                                                   class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs focus:border-red-500 focus:outline-none">
                                        </label>
                                        <label class="block">
                                            <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-500">RO2 · Potong Lensa</span>
                                            <input type="text" name="ro2" maxlength="120" value="{{ old("ro2.{$group['label']}", $group['ro2']) }}" placeholder="Nama petugas lensa"
                                                   class="w-full rounded-lg border border-slate-200 bg-white px-2.5 py-2 text-xs focus:border-red-500 focus:outline-none">
                                        </label>
                                    </div>

                                    <button class="w-full rounded-lg bg-red-600 px-3 py-2 text-[11px] font-bold text-white hover:bg-red-700">Simpan Petugas</button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="text-xs text-slate-400">Tidak ada pemakai tercatat.</li>
                    @endforelse
                </ul>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-bold text-slate-900">Ringkasan</h2>
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd class="font-semibold text-slate-800">@idr($gross)</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Diskon</dt><dd class="font-semibold text-rose-600">-@idr($discount)</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-2.5 text-base font-extrabold"><dt class="text-slate-900">Grandtotal</dt><dd class="text-slate-900">@idr($transaction->grandtotal)</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Dibayar</dt><dd class="font-semibold text-red-600">@idr($transaction->amountPaid())</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Sisa Tagihan</dt><dd class="font-semibold text-rose-600">@idr($transaction->balanceDue())</dd></div>
                </dl>
                <div class="mt-4 flex flex-wrap gap-2 text-[11px] font-semibold uppercase tracking-wider">
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-600 capitalize">{{ $transaction->payment_by ?: '-' }}</span>
                    <span class="rounded-full px-2.5 py-1 {{ $transaction->isPaid() ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">{{ $transaction->isPaid() ? 'Lunas' : 'DP' }}</span>
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
                <p class="mt-4 text-xs text-slate-400">Kasir: {{ $transaction->input_by }} · {{ $transaction->sales_datetime }}</p>
            </div>
        </div>
    </div>
@endsection
