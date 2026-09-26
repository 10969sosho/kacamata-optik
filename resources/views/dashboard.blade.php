@extends('layouts.erp')

@section('title', 'Dashboard')
@section('subtitle', 'Ringkasan performa toko hari ini')

@section('content')
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-7">
        @php
            $cards = [
                ['label' => 'Sales Hari Ini', 'value' => $salesToday, 'icon' => 'calendar-check', 'money' => true, 'tone' => 'emerald'],
                ['label' => 'Sales Bulan Ini', 'value' => $salesMonth, 'icon' => 'trending-up', 'money' => true, 'tone' => 'emerald'],
                ['label' => 'Total Transaksi', 'value' => $totalTransactions, 'icon' => 'receipt-text', 'money' => false, 'tone' => 'indigo'],
                ['label' => 'ATV', 'value' => $atv, 'icon' => 'wallet', 'money' => true, 'tone' => 'indigo'],
                ['label' => 'Total Customer', 'value' => $totalCustomers, 'icon' => 'users', 'money' => false, 'tone' => 'sky'],
                ['label' => 'Customer Baru', 'value' => $newCustomers, 'icon' => 'user-plus', 'money' => false, 'tone' => 'sky'],
                ['label' => 'Repeat Orders', 'value' => $repeatOrders, 'icon' => 'repeat-2', 'money' => false, 'tone' => 'amber'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-{{ $card['tone'] }}-50 text-{{ $card['tone'] }}-600">
                        <i data-lucide="{{ $card['icon'] }}" class="h-4 w-4"></i>
                    </span>
                </div>
                <p class="mt-3 text-lg font-extrabold text-slate-900">
                    @if ($card['money']) @idr($card['value']) @else {{ number_format((float) $card['value']) }} @endif
                </p>
                <p class="text-[11px] font-medium uppercase tracking-wider text-slate-500">{{ $card['label'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        <!-- Recent transactions -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-bold text-slate-900">Transaksi Terbaru</h2>
                <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-emerald-600 hover:underline">Lihat semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Kasir</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentTransactions as $trx)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-semibold text-slate-900">
                                    <a href="{{ route('transactions.show', $trx) }}" class="hover:text-emerald-600">{{ $trx->invoice_number }}</a>
                                </td>
                                <td class="px-5 py-3 text-slate-600">{{ $trx->customer?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $trx->staff?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-slate-900">@idr($trx->total_amount)</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold capitalize text-slate-600">{{ $trx->status }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low stock -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center gap-2">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-rose-50 text-rose-600"><i data-lucide="alert-triangle" class="h-4 w-4"></i></span>
                    <h2 class="text-sm font-bold text-slate-900">Low Stock Alert</h2>
                </div>
                <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Frame</p>
                <ul class="space-y-2">
                    @forelse ($lowStockFrames as $frame)
                        <li class="flex items-center justify-between text-sm">
                            <span class="truncate text-slate-700">{{ $frame->name }}</span>
                            <span class="rounded-md bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-600">{{ $frame->stock }}/{{ $frame->min_stock }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-400">Semua stok frame aman.</li>
                    @endforelse
                </ul>
                <p class="mb-3 mt-5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Lensa</p>
                <ul class="space-y-2">
                    @forelse ($lowStockLenses as $lens)
                        <li class="flex items-center justify-between text-sm">
                            <span class="truncate text-slate-700">{{ $lens->name }}</span>
                            <span class="rounded-md bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-600">{{ $lens->stock }}/{{ $lens->min_stock }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-400">Semua stok lensa aman.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Top selling -->
    <div class="mt-6 grid gap-6 md:grid-cols-2">
        @foreach ([['Top Frames', $topFrames], ['Top Lenses', $topLenses]] as [$title, $rows])
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-sm font-bold text-slate-900">{{ $title }} Terlaris</h2>
                <ul class="space-y-3">
                    @forelse ($rows as $row)
                        <li class="flex items-center justify-between gap-3 text-sm">
                            <span class="flex min-w-0 items-center gap-3">
                                <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg bg-slate-100 text-xs font-bold text-slate-500">{{ $loop->iteration }}</span>
                                <span class="truncate text-slate-700">{{ $row->name }}</span>
                            </span>
                            <span class="shrink-0 rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">{{ (int) $row->sold }} terjual</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-400">Belum ada data penjualan.</li>
                    @endforelse
                </ul>
            </div>
        @endforeach
    </div>
@endsection
