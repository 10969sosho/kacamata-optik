@extends('layouts.erp')

@section('title', $promotion->exists ? 'Edit Promo' : 'Tambah Promo')
@section('subtitle', $promotion->exists ? $promotion->name : 'Promo baru')

@section('content')
    <form method="POST" action="{{ $promotion->exists ? route('promotions.update', $promotion) : route('promotions.store') }}"
          class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($promotion->exists) @method('PUT') @endif

        <div class="grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Promo *</label>
                <input name="name" required value="{{ old('name', $promotion->name) }}" placeholder="Diskon Merdeka 15%"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">{{ old('description', $promotion->description) }}</textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Tipe Promo *</label>
                <select name="promo_type" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                    @foreach (['percentage' => 'Persen (%)', 'nominal' => 'Nominal (Rp)', 'buy_x_get_y' => 'Beli X Gratis Y', 'member_only' => 'Khusus Member'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('promo_type', $promotion->promo_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nilai Diskon *</label>
                <input name="discount_value" type="number" step="0.01" min="0" required value="{{ old('discount_value', $promotion->discount_value) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Minimum Belanja</label>
                <input name="min_spend" type="number" step="0.01" min="0" value="{{ old('min_spend', $promotion->min_spend) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Banner (URL)</label>
                <input name="banner" value="{{ old('banner', $promotion->banner) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Mulai</label>
                <input name="start_date" type="date" value="{{ old('start_date', optional($promotion->start_date)->format('Y-m-d')) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Selesai</label>
                <input name="end_date" type="date" value="{{ old('end_date', optional($promotion->end_date)->format('Y-m-d')) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $promotion->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500/30">
                Promo aktif
            </label>
        </div>

        <div class="mt-6 flex gap-3">
            <button class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-600">Simpan</button>
            <a href="{{ route('promotions.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
        </div>
    </form>
@endsection
