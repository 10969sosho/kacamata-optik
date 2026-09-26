@extends('layouts.erp')

@section('title', 'Master Frame')
@section('subtitle', 'Katalog frame kacamata')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('frames.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari SKU / nama / barcode..."
                   class="w-full max-w-xs rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 sm:w-64">
            <select name="brand" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                <option value="">Semua Brand</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand }}" @selected(($filters['brand'] ?? '') === $brand)>{{ $brand }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
        </form>
        <a href="{{ route('frames.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-600">
            <i data-lucide="plus" class="h-4 w-4"></i> Tambah Frame
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">SKU</th>
                        <th class="px-5 py-3">Nama / Brand</th>
                        <th class="px-5 py-3">Ukuran</th>
                        <th class="px-5 py-3 text-right">Harga Jual</th>
                        <th class="px-5 py-3 text-center">Stok</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($frames as $frame)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $frame->sku }}</td>
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-900">{{ $frame->name }}</p>
                                <p class="text-xs text-slate-500">{{ $frame->brand }} {{ $frame->model }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $frame->size ?: '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900">@idr($frame->sell_price)</td>
                            <td class="px-5 py-3 text-center">
                                @if ($frame->stock <= 0)
                                    <span class="rounded-full bg-rose-100 px-2.5 py-1 text-[11px] font-bold text-rose-700">HABIS</span>
                                @elseif ($frame->isLowStock())
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-700">LOW {{ $frame->stock }}</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-emerald-700">{{ $frame->stock }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('frames.edit', $frame) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Edit</a>
                                <form action="{{ route('frames.destroy', $frame) }}" method="POST" class="inline" onsubmit="return confirm('Hapus frame {{ $frame->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Tidak ada frame.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $frames->links() }}</div>
    </div>
@endsection
