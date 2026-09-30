@extends('layouts.member')

@section('title', 'Transaksi Saya')

@section('content')
    <h1 class="mb-4 text-lg font-extrabold text-slate-900">Riwayat Transaksi</h1>

    <div class="space-y-4">
        @forelse ($transactions as $trx)
            @php
                $steps = ['ordered' => 'Dipesan', 'processing' => 'Diproses', 'ready' => 'Siap', 'completed' => 'Selesai'];
                $current = array_search($trx->status, array_keys($steps), true);
            @endphp
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-mono text-sm font-bold text-slate-900">{{ $trx->invoice_number }}</p>
                        <p class="text-xs text-slate-500">{{ optional($trx->transaction_date)->format('d M Y, H:i') }}</p>
                    </div>
                    <p class="text-sm font-extrabold text-slate-900">@idr($trx->total_amount)</p>
                </div>

                <ul class="mt-3 space-y-1 text-xs text-slate-500">
                    @foreach ($trx->items as $item)
                        <li class="flex justify-between"><span>{{ $item->name }} ×{{ $item->quantity }}</span><span>@idr($item->subtotal)</span></li>
                    @endforeach
                </ul>

                @if ($current !== false)
                    <div class="mt-4 flex items-center gap-1">
                        @foreach ($steps as $key => $label)
                            @php($done = array_search($key, array_keys($steps), true) <= $current)
                            <div class="flex flex-1 flex-col items-center gap-1">
                                <span class="h-1.5 w-full rounded {{ $done ? 'bg-red-600' : 'bg-slate-200' }}"></span>
                                <span class="text-[9px] font-semibold {{ $done ? 'text-red-600' : 'text-slate-400' }}">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 rounded-lg bg-rose-50 px-3 py-2 text-center text-xs font-bold uppercase text-rose-600">{{ $trx->status }}</p>
                @endif

                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                    <span class="rounded-full {{ $trx->payment_status === 'paid' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }} px-2.5 py-1 font-bold uppercase">
                        {{ $trx->payment_status === 'paid' ? 'Lunas' : 'DP' }}
                    </span>
                    <span class="text-slate-500 capitalize">{{ str_replace('_', ' ', $trx->payment_method) }}</span>
                </div>

                <a href="{{ route('portal.transactions.show', $trx) }}" class="mt-3 flex items-center justify-between rounded-xl bg-slate-900 px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-800">
                    <span>Lihat Detail</span>
                    <i data-lucide="chevron-right" class="h-4 w-4"></i>
                </a>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-400">
                Belum ada transaksi.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $transactions->links() }}</div>
@endsection
