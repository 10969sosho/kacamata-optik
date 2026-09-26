@extends('layouts.erp')

@section('title', 'Kategori Produk')
@section('subtitle', 'Kelola kategori frame, lensa & aksesoris')

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-slate-900">{{ $category ? 'Edit Kategori' : 'Tambah Kategori' }}</h2>
            <form method="POST" action="{{ $category ? route('categories.update', $category) : route('categories.store') }}" class="space-y-4">
                @csrf
                @if ($category) @method('PUT') @endif

                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama *</label>
                    <input name="name" required value="{{ old('name', $category?->name) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Tipe *</label>
                    <select name="type" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                        @foreach (['lens' => 'Lensa', 'frame' => 'Frame', 'accessory' => 'Aksesoris'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', $category?->type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category?->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-emerald-500 focus:ring-emerald-500/30">
                    Aktif
                </label>
                <div class="flex gap-2">
                    <button class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-600">Simpan</button>
                    @if ($category)
                        <a href="{{ route('categories.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600">Batal</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-sm font-bold text-slate-900">Daftar Kategori</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Tipe</th>
                            <th class="px-5 py-3 text-center">Lensa</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($categories as $cat)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-slate-900">{{ $cat->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $cat->slug }}</p>
                                </td>
                                <td class="px-5 py-3 capitalize text-slate-600">{{ $cat->type }}</td>
                                <td class="px-5 py-3 text-center text-slate-600">{{ $cat->lenses_count }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $cat->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $cat->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('categories.index', ['edit' => $cat->id]) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Edit</a>
                                    <form action="{{ route('categories.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400">Belum ada kategori.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">{{ $categories->links() }}</div>
        </div>
    </div>
@endsection
