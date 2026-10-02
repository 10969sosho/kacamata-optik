@extends('layouts.erp')

@section('title', 'Resep Kacamata')
@section('subtitle', 'Riwayat resep seluruh customer')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('prescriptions.index') }}" class="flex flex-1 items-center gap-2">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari customer / dokter..."
                   class="w-full max-w-sm rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20">
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Cari</button>
        </form>
        <a href="{{ route('prescriptions.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-red-700">
            <i data-lucide="plus" class="h-4 w-4"></i> Input Resep Baru
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Dokter / Optometris</th>
                        <th class="px-5 py-3">Tanggal Periksa</th>
                        <th class="px-5 py-3">Tipe</th>
                        <th class="px-5 py-3">Sumber</th>
                        <th class="px-5 py-3 text-center">OD</th>
                        <th class="px-5 py-3 text-center">OS</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($prescriptions as $rx)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-slate-900">{{ $rx->customer?->name }}</p>
                                <p class="text-xs text-slate-400">{{ $rx->customer?->member_id }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $rx->doctor_or_optician }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ optional($rx->examination_date)->format('d/m/Y') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $rx->prescription_type ?: '-' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">{{ $rx->sourceLabel() }}</span>
                            </td>
                            <td class="px-5 py-3 text-center font-mono text-xs text-slate-700">{{ $rx->od_sph }}/{{ $rx->od_cyl }}×{{ $rx->od_axis }}</td>
                            <td class="px-5 py-3 text-center font-mono text-xs text-slate-700">{{ $rx->os_sph }}/{{ $rx->os_cyl }}×{{ $rx->os_axis }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('prescriptions.show', $rx) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Lihat</a>
                                <a href="{{ route('prescriptions.edit', $rx) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-100">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-10 text-center text-slate-400">Belum ada resep.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">{{ $prescriptions->links() }}</div>
    </div>
@endsection
