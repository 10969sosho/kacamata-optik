@extends('layouts.erp')

@section('title', 'Customer')
@section('subtitle', 'Daftar member & pelanggan')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('customers.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama / HP / Member ID..."
                   class="w-full max-w-xs rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 sm:w-72">
            <select name="status" class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                <option value="">Semua Status</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Aktif</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Nonaktif</option>
            </select>
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Filter</button>
        </form>
        <a href="{{ route('customers.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 px-4 py-2.5 text-sm font-bold text-white hover:bg-emerald-600">
            <i data-lucide="user-plus" class="h-4 w-4"></i> Customer Baru
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Member ID</th>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Kontak</th>
                        <th class="px-5 py-3 text-center">Transaksi</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 font-mono text-xs font-bold text-emerald-700">{{ $customer->member_id }}</td>
                            <td class="px-5 py-3 font-semibold text-slate-900">{{ $customer->name }}</td>
                            <td class="px-5 py-3 text-slate-600">
                                <p>{{ $customer->phone }}</p>
                                <p class="text-xs text-slate-400">{{ $customer->email ?: '-' }}</p>
                            </td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $customer->transactions_count }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $customer->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ strtoupper($customer->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('customers.show', $customer) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Detail</a>
                                <a href="{{ route('customers.edit', $customer) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">Tidak ada customer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $customers->links() }}</div>
    </div>
@endsection
