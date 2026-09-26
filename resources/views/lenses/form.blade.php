@extends('layouts.erp')

@section('title', $lens->exists ? 'Edit Lensa' : 'Tambah Lensa')
@section('subtitle', $lens->exists ? $lens->name : 'Form input lensa baru')

@section('content')
    <form method="POST" action="{{ $lens->exists ? route('lenses.update', $lens) : route('lenses.store') }}"
          class="max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($lens->exists) @method('PUT') @endif

        @php
            $lensTypes = ['Single Vision', 'Bifocal', 'Progressive', 'Photochromic', 'Blue Light', 'Anti Reflective', 'Polarized'];
            $indexes = ['1.56', '1.59', '1.60', '1.61', '1.67', '1.74'];
        @endphp

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">SKU *</label>
                <input name="sku" required value="{{ old('sku', $lens->sku) }}" placeholder="LS-ES-001"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Brand *</label>
                <input name="brand" required value="{{ old('brand', $lens->brand) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Lensa *</label>
                <input name="name" required value="{{ old('name', $lens->name) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori</label>
                <select name="category_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                    <option value="">— Pilih kategori —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) old('category_id', $lens->category_id) === (string) $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Tipe Lensa *</label>
                <select name="lens_type" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                    @foreach ($lensTypes as $type)
                        <option value="{{ $type }}" @selected(old('lens_type', $lens->lens_type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Index *</label>
                <select name="index_val" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                    @foreach ($indexes as $idx)
                        <option value="{{ $idx }}" @selected(old('index_val', $lens->index_val) === $idx)>{{ $idx }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Material</label>
                <input name="material" value="{{ old('material', $lens->material) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Coating</label>
                <input name="coating" value="{{ old('coating', $lens->coating) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Supplier</label>
                <input name="supplier" value="{{ old('supplier', $lens->supplier) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Harga Beli *</label>
                <input name="buy_price" type="number" step="0.01" min="0" required value="{{ old('buy_price', $lens->buy_price) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Harga Jual *</label>
                <input name="sell_price" type="number" step="0.01" min="0" required value="{{ old('sell_price', $lens->sell_price) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Stok *</label>
                    <input name="stock" type="number" min="0" required value="{{ old('stock', $lens->stock ?? 0) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Min. Stok *</label>
                    <input name="min_stock" type="number" min="0" required value="{{ old('min_stock', $lens->min_stock ?? 5) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-600">Simpan</button>
            <a href="{{ route('lenses.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
        </div>
    </form>
@endsection
