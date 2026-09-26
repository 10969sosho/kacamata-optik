@extends('layouts.erp')

@section('title', $customer->exists ? 'Edit Customer' : 'Customer Baru')
@section('subtitle', $customer->exists ? $customer->name : 'Pendaftaran member baru')

@section('content')
    <form method="POST" action="{{ $customer->exists ? route('customers.update', $customer) : route('customers.store') }}"
          class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @if ($customer->exists) @method('PUT') @endif

        @unless ($customer->exists)
            <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <i data-lucide="badge-check" class="h-4 w-4"></i>
                Member ID otomatis dibuat saat disimpan.
            </div>
        @else
            <div class="mb-5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
                <span class="text-slate-500">Member ID</span>
                <span class="ml-2 font-mono font-bold text-slate-900">{{ $customer->member_id }}</span>
            </div>
        @endunless

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Lengkap *</label>
                <input name="name" required value="{{ old('name', $customer->name) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">No. WhatsApp *</label>
                <input name="phone" required value="{{ old('phone', $customer->phone) }}" placeholder="081234567890"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Email</label>
                <input name="email" type="email" value="{{ old('email', $customer->email) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal Lahir</label>
                <input name="birth_date" type="date" value="{{ old('birth_date', optional($customer->birth_date)->format('Y-m-d')) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Gender</label>
                <select name="gender" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                    <option value="">— Pilih —</option>
                    @foreach (['Laki-laki', 'Perempuan'] as $g)
                        <option value="{{ $g }}" @selected(old('gender', $customer->gender) === $g)>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal Daftar</label>
                <input name="registered_at" type="date" value="{{ old('registered_at', optional($customer->registered_at)->format('Y-m-d') ?? now()->toDateString()) }}"
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Alamat</label>
                <textarea name="address" rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">{{ old('address', $customer->address) }}</textarea>
            </div>
            @if ($customer->exists)
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Status</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                        <option value="active" @selected(old('status', $customer->status) === 'active')>Aktif</option>
                        <option value="inactive" @selected(old('status', $customer->status) === 'inactive')>Nonaktif</option>
                    </select>
                </div>
            @endif
        </div>

        <div class="mt-6 flex gap-3">
            <button class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-600">Simpan</button>
            <a href="{{ route('customers.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
        </div>
    </form>
@endsection
