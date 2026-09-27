@extends('layouts.erp')

@section('title', 'Sales Report')
@section('subtitle', 'Laporan penjualan & export CSV')

@section('content')
    <form method="GET" action="{{ route('reports.index') }}" class="mb-6 flex flex-wrap items-end gap-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div>
            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Dari Tanggal</label>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Sampai</label>
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Staff Kasir</label>
            <select name="staff_id" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                <option value="">Semua</option>
                @foreach ($staff as $s)
                    <option value="{{ $s->id }}" @selected((string) ($filters['staff_id'] ?? '') === (string) $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Status Bayar</label>
            <select name="payment_status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                <option value="">Semua</option>
                @foreach (['paid' => 'Lunas', 'down_payment' => 'DP', 'unpaid' => 'Belum Bayar'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Tipe Produk</label>
            <select name="item_type" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                <option value="">Semua</option>
                @foreach (['frame' => 'Frame', 'lens' => 'Lensa', 'custom' => 'Custom'] as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['item_type'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Terapkan</button>
        <a href="{{ route('reports.export', $filters) }}" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">
            <i data-lucide="download" class="h-4 w-4"></i> Download CSV
        </a>
    </form>

    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        @foreach ([
            ['label' => 'Total Gross Sales', 'value' => $summary['gross']],
            ['label' => 'Total Diskon', 'value' => $summary['discount']],
            ['label' => 'Total Net Sales', 'value' => $summary['net']],
            ['label' => 'Jumlah Transaksi', 'value' => $summary['count'], 'money' => false],
            ['label' => 'ATV', 'value' => $summary['atv']],
        ] as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $card['label'] }}</p>
                <p class="mt-1 text-xl font-extrabold text-slate-900">
                    @if ($card['money'] ?? true) @idr($card['value']) @else {{ number_format((float) $card['value']) }} @endif
                </p>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Invoice</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Kasir</th>
                        <th class="px-5 py-3 text-right">Subtotal</th>
                        <th class="px-5 py-3 text-right">Diskon</th>
                        <th class="px-5 py-3 text-right">Net</th>
                        <th class="px-5 py-3">Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($rows as $trx)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <a href="{{ route('transactions.show', $trx) }}" class="font-semibold text-slate-900 hover:text-red-600">{{ $trx->invoice_number }}</a>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ optional($trx->transaction_date)->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $trx->customer?->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $trx->staff?->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-right text-slate-700">@idr($trx->subtotal)</td>
                            <td class="px-5 py-3 text-right text-rose-600">-@idr($trx->discount_amount)</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900">@idr($trx->total_amount)</td>
                            <td class="px-5 py-3 capitalize text-slate-600">{{ $trx->payment_status }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400">Tidak ada data pada filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $rows->links() }}</div>
    </div>
@endsection
