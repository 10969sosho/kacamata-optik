@extends('layouts.erp')

@section('title', 'Transaksi')
@section('subtitle', 'Riwayat & status pesanan')

@section('content')
    <form method="GET" action="{{ route('transactions.index') }}" class="mb-5 flex flex-wrap items-end gap-2">
        <div>
            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Invoice</label>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="TRX-..."
                   class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Status</label>
            <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                <option value="">Semua</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Dari</label>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">Sampai</label>
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-red-500 focus:outline-none">
        </div>
        <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
        <a href="{{ route('transactions.create') }}" class="ml-auto rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">+ Buat Transaksi</a>
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Invoice</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Kasir</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Bayar</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $trx)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $trx->invoice_number }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ optional($trx->transaction_date)->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $trx->customer?->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $trx->staff?->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900">@idr($trx->total_amount)</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $trx->payment_status === 'paid' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $trx->payment_status === 'paid' ? 'LUNAS' : 'DP' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold capitalize text-slate-600">{{ $trx->status }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('transactions.show', $trx) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Detail</a>
                                <a href="{{ route('transactions.print', $trx) }}" target="_blank" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Cetak</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400">Tidak ada transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $transactions->links() }}</div>
    </div>
@endsection
