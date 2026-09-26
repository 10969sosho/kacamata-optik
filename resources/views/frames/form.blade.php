@extends('layouts.erp')

@section('title', $frame->exists ? 'Edit Frame' : 'Tambah Frame')
@section('subtitle', $frame->exists ? $frame->name : 'Form input frame baru')

@section('content')
    <form method="POST" action="{{ $frame->exists ? route('frames.update', $frame) : route('frames.store') }}"
          class="max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($frame->exists) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">SKU *</label>
                <input name="sku" required value="{{ old('sku', $frame->sku) }}" placeholder="FR-RB-001"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Barcode</label>
                <input name="barcode" value="{{ old('barcode', $frame->barcode) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Frame *</label>
                <input name="name" required value="{{ old('name', $frame->name) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Brand *</label>
                <input name="brand" required value="{{ old('brand', $frame->brand) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Model</label>
                <input name="model" value="{{ old('model', $frame->model) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Warna</label>
                <input name="color" value="{{ old('color', $frame->color) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Ukuran</label>
                <input name="size" value="{{ old('size', $frame->size) }}" placeholder="52-18-140"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Material</label>
                <input name="material" value="{{ old('material', $frame->material) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Gender</label>
                <select name="gender" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                    @foreach (['Unisex', 'Pria', 'Wanita'] as $g)
                        <option value="{{ $g }}" @selected(old('gender', $frame->gender) === $g)>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Foto (URL)</label>
                <input name="photo" value="{{ old('photo', $frame->photo) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Harga Beli *</label>
                <input name="buy_price" type="number" step="0.01" min="0" required value="{{ old('buy_price', $frame->buy_price) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Harga Jual *</label>
                <input name="sell_price" type="number" step="0.01" min="0" required value="{{ old('sell_price', $frame->sell_price) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Stok *</label>
                    <input name="stock" type="number" min="0" required value="{{ old('stock', $frame->stock ?? 0) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Min. Stok *</label>
                    <input name="min_stock" type="number" min="0" required value="{{ old('min_stock', $frame->min_stock ?? 5) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Status</label>
                <select name="status" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                    <option value="active" @selected(old('status', $frame->status ?? 'active') === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status', $frame->status) === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Deskripsi</label>
                <textarea name="description" rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">{{ old('description', $frame->description) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-600">Simpan</button>
            <a href="{{ route('frames.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
        </div>
    </form>
@endsection
