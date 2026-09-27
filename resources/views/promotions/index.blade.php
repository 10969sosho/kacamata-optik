@extends('layouts.erp')

@section('title', 'Promo')
@section('subtitle', 'Diskon & penawaran khusus member')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <p class="text-sm text-slate-500">Berlaku otomatis di POS saat syarat terpenuhi.</p>
        <a href="{{ route('promotions.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">
            <i data-lucide="plus" class="h-4 w-4"></i> Tambah Promo
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Promo</th>
                        <th class="px-5 py-3">Tipe</th>
                        <th class="px-5 py-3 text-right">Nilai</th>
                        <th class="px-5 py-3 text-right">Min. Belanja</th>
                        <th class="px-5 py-3">Periode</th>
                        <th class="px-5 py-3 text-center">Pakai</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($promotions as $promo)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-900">{{ $promo->name }}</p>
                                <p class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($promo->description, 60) }}</p>
                            </td>
                            <td class="px-5 py-3 capitalize text-slate-600">{{ str_replace('_', ' ', $promo->promo_type) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900">
                                @if ($promo->promo_type === 'nominal')
                                    @idr($promo->discount_value)
                                @else
                                    {{ rtrim(rtrim(number_format((float) $promo->discount_value, 2), '0'), '.') }}%
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right text-slate-600">@idr($promo->min_spend)</td>
                            <td class="px-5 py-3 text-xs text-slate-600">
                                {{ $promo->start_date?->format('d/m/Y') ?? 'Mulai kapan saja' }}<br>
                                s/d {{ $promo->end_date?->format('d/m/Y') ?? 'Tanpa batas' }}
                            </td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $promo->transactions_count }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $promo->is_active ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $promo->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('promotions.edit', $promo) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Edit</a>
                                <form action="{{ route('promotions.destroy', $promo) }}" method="POST" class="inline" onsubmit="return confirm('Hapus promo {{ $promo->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400">Belum ada promo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $promotions->links() }}</div>
    </div>
@endsection
