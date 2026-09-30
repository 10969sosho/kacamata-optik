@extends('layouts.erp')

@section('title', 'Master Item')
@section('subtitle', 'Seluruh item toko: frame, lensa, softlens, case & aksesoris')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('items.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
            <input type="hidden" name="tab" value="{{ $filters['tab'] }}">
            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Cari SKU / nama / brand..."
                   class="w-full max-w-xs rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 sm:w-64">
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Cari</button>
            @if ($filters['q'] !== '')
                <a href="{{ route('items.index', ['tab' => $filters['tab']]) }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Reset</a>
            @endif
        </form>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                <i data-lucide="tags" class="h-4 w-4"></i> Kelola Kategori
            </a>
            <a href="{{ $createUrl }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">
                <i data-lucide="plus" class="h-4 w-4"></i> Tambah Item
            </a>
        </div>
    </div>

    <!-- Kategori / tab -->
    <div class="mb-5 flex flex-wrap gap-2">
        @foreach ($tabs as $tab)
            <a href="{{ route('items.index', ['tab' => $tab['key'], 'q' => $filters['q']]) }}"
               class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold transition {{ $activeTab === $tab['key'] ? 'border-red-600 bg-red-600 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-900' }}">
                {{ $tab['label'] }}
                <span class="rounded-full px-1.5 py-0.5 text-[10px] {{ $activeTab === $tab['key'] ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $tab['count'] }}</span>
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Kategori</th>
                        <th class="px-5 py-3">SKU</th>
                        <th class="px-5 py-3">Nama / Brand</th>
                        <th class="px-5 py-3">Detail</th>
                        <th class="px-5 py-3 text-right">Harga Jual</th>
                        <th class="px-5 py-3 text-center">Stok</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <span class="rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide text-red-700">{{ $item['category'] }}</span>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $item['sku'] }}</td>
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-900">{{ $item['name'] }}</p>
                                <p class="text-xs text-slate-500">{{ $item['brand'] }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $item['detail'] ?: '-' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-900">@idr($item['price'])</td>
                            <td class="px-5 py-3 text-center">
                                @if ($item['stock'] <= 0)
                                    <span class="rounded-full bg-rose-100 px-2.5 py-1 text-[11px] font-bold text-rose-700">HABIS</span>
                                @elseif ($item['stock'] <= $item['min_stock'])
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-bold text-amber-700">LOW {{ $item['stock'] }}</span>
                                @else
                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-[11px] font-bold text-red-700">{{ $item['stock'] }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ $item['edit_url'] }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Edit</a>
                                <form action="{{ $item['delete_url'] }}" method="POST" class="inline" onsubmit="return confirm('Hapus item {{ $item['delete_name'] }}?')">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Tidak ada item pada kategori ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="border-t border-slate-100 px-5 py-4">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
