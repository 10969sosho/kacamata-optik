@extends('layouts.erp')

@section('title', 'Detail Customer')
@section('subtitle', $customer->name.' · '.$customer->member_id)

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Member card + profile -->
        <div class="space-y-6">
            <div class="relative overflow-hidden rounded-2xl bg-slate-900 p-6 text-white shadow-lg">
                <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-red-500/20 blur-2xl"></div>
                <div class="relative">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.2em] text-red-400">Membership Card</p>
                            <p class="mt-3 font-mono text-xl font-extrabold tracking-wider">{{ $customer->member_id }}</p>
                        </div>
                        <span class="rounded-full bg-red-500/20 px-2.5 py-1 text-[10px] font-bold uppercase text-red-300">{{ $customer->status }}</span>
                    </div>
                    <p class="mt-4 text-lg font-bold">{{ $customer->name }}</p>
                    <p class="text-sm text-slate-400">{{ $customer->phone }}</p>
                    <div class="mt-5 flex items-end justify-between border-t border-white/10 pt-4">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-slate-500">Total Belanja</p>
                            <p class="text-lg font-extrabold text-red-400">@idr($customer->totalSpend())</p>
                        </div>
                        <div class="grid h-10 w-16 grid-cols-5 gap-0.5 opacity-80">
                            @for ($i = 0; $i < 15; $i++)
                                <span class="bg-white {{ $i % 3 === 0 ? 'opacity-100' : 'opacity-40' }}"></span>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-sm font-bold text-slate-900">Profil</h2>
                    <a href="{{ route('customers.edit', $customer) }}" class="text-xs font-semibold text-red-600 hover:underline">Edit</a>
                </div>
                <dl class="space-y-3 text-sm">
                    @foreach ([
                        'Email' => $customer->email ?: '-',
                        'Gender' => $customer->gender ?: '-',
                        'Tanggal Lahir' => optional($customer->birth_date)->format('d/m/Y') ?: '-',
                        'Terdaftar' => optional($customer->registered_at)->format('d/m/Y') ?: '-',
                        'Alamat' => $customer->address ?: '-',
                    ] as $label => $value)
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">{{ $label }}</dt>
                            <dd class="text-right font-medium text-slate-800">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                <div class="mt-5 flex gap-2">
                    <a href="{{ route('prescriptions.create', ['customer_id' => $customer->id]) }}"
                       class="flex-1 rounded-xl bg-slate-900 px-4 py-2.5 text-center text-xs font-bold text-white hover:bg-slate-800">+ Input Resep</a>
                    <a href="{{ route('pos.create') }}"
                       class="flex-1 rounded-xl bg-red-600 px-4 py-2.5 text-center text-xs font-bold text-white hover:bg-red-700">Buat Transaksi</a>
                </div>
            </div>
        </div>

        <!-- Prescriptions + transactions -->
        <div class="space-y-6 lg:col-span-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-bold text-slate-900">Riwayat Resep Kacamata</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($customer->prescriptions as $rx)
                        <a href="{{ route('prescriptions.show', $rx) }}" class="flex items-center justify-between px-5 py-4 hover:bg-slate-50">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $rx->doctor_or_optician }}</p>
                                <p class="text-xs text-slate-500">{{ optional($rx->examination_date)->format('d M Y') }} · {{ $rx->prescription_type ?: 'Umum' }}</p>
                            </div>
                            <span class="text-xs font-semibold text-slate-400">OD {{ $rx->od_sph }}/OS {{ $rx->os_sph }} →</span>
                        </a>
                    @empty
                        <p class="px-5 py-8 text-center text-sm text-slate-400">Belum ada resep.</p>
                    @endforelse
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-sm font-bold text-slate-900">Riwayat Transaksi</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3">Invoice</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3 text-right">Total</th>
                                <th class="px-5 py-3">Bayar</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($customer->transactions as $trx)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-5 py-3">
                                        <a href="{{ route('transactions.show', $trx) }}" class="font-semibold text-slate-900 hover:text-red-600">{{ $trx->invoice_number }}</a>
                                    </td>
                                    <td class="px-5 py-3 text-slate-600">{{ optional($trx->transaction_date)->format('d/m/Y') }}</td>
                                    <td class="px-5 py-3 text-right font-semibold text-slate-900">@idr($trx->total_amount)</td>
                                    <td class="px-5 py-3 text-slate-600"><span class="capitalize">{{ $trx->payment_status }}</span></td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold capitalize text-slate-600">{{ $trx->status }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada transaksi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
