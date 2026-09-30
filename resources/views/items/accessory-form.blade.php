@extends('layouts.erp')

@section('title', $accessory->exists ? 'Edit Item' : 'Tambah Item')
@section('subtitle', $accessory->exists ? $accessory->name : 'Item softlens, case & aksesoris lainnya')

@section('content')
    <form method="POST" action="{{ $accessory->exists ? route('items.accessories.update', $accessory) : route('items.accessories.store') }}"
          class="max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($accessory->exists) @method('PUT') @endif

        @if ($categories->isEmpty())
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Belum ada kategori aksesoris. Buat dulu kategori
                <a href="{{ route('categories.index') }}" class="font-bold underline">Softlens / Case / Aksesoris</a>
                agar item bisa dikategorikan.
            </div>
        @endif

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">SKU *</label>
                <input name="sku" required value="{{ old('sku', $accessory->sku) }}" placeholder="AC-SL-001"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Item *</label>
                <input name="name" required value="{{ old('name', $accessory->name) }}" placeholder="Softlens Bulanan Natural"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Brand</label>
                <input name="brand" value="{{ old('brand', $accessory->brand) }}" placeholder="Mis. Acuvue"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori *</label>
                <select name="category_id" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                    <option value="">— Pilih kategori —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) old('category_id', $accessory->category_id) === (string) $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Harga Beli *</label>
                <input name="buy_price" type="number" step="0.01" min="0" required value="{{ old('buy_price', $accessory->buy_price ?? 0) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Harga Jual *</label>
                <input name="sell_price" type="number" step="0.01" min="0" required value="{{ old('sell_price', $accessory->sell_price ?? 0) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Stok *</label>
                    <input name="stock" type="number" min="0" required value="{{ old('stock', $accessory->stock ?? 0) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Min. Stok *</label>
                    <input name="min_stock" type="number" min="0" required value="{{ old('min_stock', $accessory->min_stock ?? 5) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Status *</label>
                <select name="status" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">
                    <option value="active" @selected(old('status', $accessory->status ?? 'active') === 'active')>Aktif</option>
                    <option value="inactive" @selected(old('status', $accessory->status) === 'inactive')>Nonaktif</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none">{{ old('description', $accessory->description) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button class="rounded-xl bg-red-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-red-700">Simpan</button>
            <a href="{{ route('items.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
        </div>
    </form>
@endsection
