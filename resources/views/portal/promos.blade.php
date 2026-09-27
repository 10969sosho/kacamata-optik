@extends('layouts.member')

@section('title', 'Promo Member')

@section('content')
    <h1 class="mb-4 text-lg font-extrabold text-slate-900">Katalog Promo</h1>

    <div class="space-y-4">
        @forelse ($promotions as $promo)
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="bg-gradient-to-r from-slate-900 to-red-900 px-5 py-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[10px] uppercase tracking-[0.2em] text-red-400">{{ str_replace('_', ' ', $promo->promo_type) }}</p>
                            <p class="mt-1 text-base font-extrabold">{{ $promo->name }}</p>
                        </div>
                        <span class="rounded-xl bg-white px-3 py-1.5 text-sm font-extrabold text-slate-900">
                            {{ $promo->promo_type === 'nominal' ? '-'.number_format((float) $promo->discount_value) : rtrim(rtrim(number_format((float) $promo->discount_value, 2), '0'), '.').'%' }}
                        </span>
                    </div>
                </div>
                <div class="p-5 text-sm">
                    <p class="text-slate-600">{{ $promo->description ?: 'Nikmati penawaran spesial ini.' }}</p>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-slate-400">Minimum Belanja</p>
                            <p class="mt-0.5 font-bold text-slate-900">@idr($promo->min_spend)</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-slate-400">Berlaku s/d</p>
                            <p class="mt-0.5 font-bold text-slate-900">{{ $promo->end_date?->format('d M Y') ?? 'Tanpa batas' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-400">
                Belum ada promo aktif.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $promotions->links() }}</div>
@endsection
