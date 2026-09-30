@extends('layouts.member')

@section('title', 'Dashboard Member')

@section('content')
    <!-- Membership card -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-red-900 p-6 text-white shadow-xl">
        <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-red-500/20 blur-3xl"></div>
        <div class="relative">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] uppercase tracking-[0.25em] text-red-400">Kartu Member Digital</p>
                    <p class="mt-3 font-mono text-2xl font-extrabold tracking-widest">{{ $customer->member_id }}</p>
                </div>
                <span class="rounded-full bg-red-500/20 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-red-300">
                    {{ $customer->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>

            <p class="mt-5 text-lg font-bold">{{ $customer->name }}</p>
            <p class="text-sm text-slate-400">{{ $customer->phone }}</p>

            <div class="mt-5 flex items-end justify-between border-t border-white/10 pt-4">
                <div>
                    <p class="text-[10px] uppercase tracking-wider text-slate-500">Total Belanja</p>
                    <p class="text-xl font-extrabold text-red-400">@idr($customer->totalSpend())</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] uppercase tracking-wider text-slate-500">Total Order</p>
                    <p class="text-xl font-extrabold">{{ $customer->totalOrders() }}</p>
                </div>
            </div>

            <!-- barcode simulasi -->
            <div class="mt-5 flex items-center gap-3 rounded-2xl bg-white/95 p-3">
                <div class="flex h-12 flex-1 items-end gap-[3px] overflow-hidden">
                    @for ($i = 0; $i < 42; $i++)
                        <span class="h-full bg-slate-900 {{ $i % 3 === 0 ? 'w-[3px]' : ($i % 2 === 0 ? 'w-[2px]' : 'w-[1px]') }} {{ $i % 4 === 0 ? 'opacity-100' : 'opacity-80' }}"></span>
                    @endfor
                </div>
                <span class="font-mono text-[11px] font-bold text-slate-700">{{ str_replace('-', '', $customer->member_id) }}</span>
            </div>
        </div>
    </div>

    <!-- Latest order status -->
    <section class="mt-6">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Status Pesanan Terkini</h2>
            <a href="{{ route('portal.transactions') }}" class="text-xs font-semibold text-red-600">Semua transaksi</a>
        </div>

        @if ($latestTransaction)
            @php
                $steps = ['ordered' => 'Dipesan', 'processing' => 'Diproses', 'ready' => 'Siap Diambil', 'completed' => 'Selesai'];
                $current = array_search($latestTransaction->status, array_keys($steps), true);
            @endphp
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-mono text-sm font-bold text-slate-900">{{ $latestTransaction->invoice_number }}</p>
                        <p class="text-xs text-slate-500">{{ optional($latestTransaction->transaction_date)->format('d M Y, H:i') }}</p>
                    </div>
                    <p class="text-sm font-extrabold text-slate-900">@idr($latestTransaction->total_amount)</p>
                </div>

                @if ($current !== false)
                    <div class="mt-5 flex items-center">
                        @foreach ($steps as $key => $label)
                            @php($done = array_search($key, array_keys($steps), true) <= $current)
                            <div class="flex items-center {{ $loop->last ? '' : 'flex-1' }}">
                                <div class="flex flex-col items-center gap-1">
                                    <span class="grid h-7 w-7 place-items-center rounded-full text-[10px] font-bold {{ $done ? 'bg-red-600 text-white' : 'bg-slate-200 text-slate-400' }}">✓</span>
                                    <span class="text-[9px] font-semibold {{ $done ? 'text-red-600' : 'text-slate-400' }}">{{ $label }}</span>
                                </div>
                                @unless ($loop->last)
                                    <span class="mx-1 mb-4 h-1 flex-1 rounded {{ $done ? 'bg-red-400' : 'bg-slate-200' }}"></span>
                                @endunless
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 rounded-lg bg-rose-50 px-3 py-2 text-center text-xs font-bold uppercase text-rose-600">{{ $latestTransaction->status }}</p>
                @endif

                <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs">
                    <span class="rounded-full {{ $latestTransaction->payment_status === 'paid' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }} px-2.5 py-1 font-bold uppercase">
                        {{ $latestTransaction->payment_status === 'paid' ? 'Lunas' : 'DP' }}
                    </span>
                    <span class="text-slate-500 capitalize">{{ str_replace('_', ' ', $latestTransaction->payment_method) }}</span>
                </div>

                <a href="{{ route('portal.transactions.show', $latestTransaction) }}" class="mt-3 block rounded-xl bg-slate-900 px-4 py-2.5 text-center text-xs font-bold text-white hover:bg-slate-800">
                    Lihat Detail Transaksi
                </a>
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-400">
                Belum ada pesanan.
            </div>
        @endif
    </section>

    <!-- Promo banners -->
    <section class="mt-6">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Promo Untuk Anda</h2>
            <a href="{{ route('portal.promos') }}" class="text-xs font-semibold text-red-600">Semua promo</a>
        </div>
        <div class="space-y-3">
            @forelse ($promotions as $promo)
                <div class="rounded-2xl border border-red-100 bg-gradient-to-r from-red-50 to-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $promo->name }}</p>
                            <p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($promo->description, 70) }}</p>
                        </div>
                        <span class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-extrabold text-white">
                            {{ $promo->promo_type === 'nominal' ? '-'.number_format((float) $promo->discount_value) : rtrim(rtrim(number_format((float) $promo->discount_value, 2), '0'), '.').'%' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-400">
                    Belum ada promo aktif.
                </div>
            @endforelse
        </div>
    </section>
@endsection
