@extends('layouts.erp')

@section('title', 'Master Lensa')
@section('subtitle', 'Katalog lensa kacamata')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('lenses.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari SKU / nama lensa..."
                   class="w-full max-w-xs rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 sm:w-64">
            <select name="brand" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                <option value="">Semua Brand</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand }}" @selected(($filters['brand'] ?? '') === $brand)>{{ $brand }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
        </form>
        <a href="{{ route('lenses.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-600">
            <i data-lucide="plus" class="h-4 w-4"></i> Tambah Lensa
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">SKU</th>
                        <th class="px-5 py-3">Nama / Brand</th>
                        <th class="px-5 py-3">Tipe & Index</th>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3 text-right">Harga Jual</th>
                        <th class="px-5 py-3 text-center">Stok</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($lenses as $lens)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $lens->sku }}</td>
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-900">{{ $lens->name }}</p>
                                <p class="text-xs text-slate-500">{{ $lens->brand }} · {{ $lens->supplier ?: 'Tanpa supplier' }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $lens->lens_type }} · {{ $lens->index_val }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $lens->category?->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900">@idr($lens->sell_price)</td>
                            <td class="px-5 py-3 text-center">
                                @if ($lens->stock <= $lens->min_stock)
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-700">LOW {{ $lens->stock }}</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-emerald-700">{{ $lens->stock }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('lenses.edit', $lens) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Edit</a>
                                <form action="{{ route('lenses.destroy', $lens) }}" method="POST" class="inline" onsubmit="return confirm('Hapus lensa {{ $lens->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Tidak ada lensa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $lenses->links() }}</div>
    </div>
@endsection
