@extends('layouts.erp')

@section('title', 'Input Resep Kacamata')
@section('subtitle', 'Data refraksi OD & OS')

@section('content')
    <form method="POST" action="{{ route('prescriptions.store') }}" class="space-y-6">
        @csrf

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-bold text-slate-900">Data Pemeriksaan</h2>
            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Customer *</label>
                    <select name="customer_id" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20">
                        <option value="">— Pilih customer —</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" @selected((string) old('customer_id', $presetCustomer?->id) === (string) $c->id)>
                                {{ $c->name }} ({{ $c->member_id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Dokter / Optometris *</label>
                    <input name="doctor_or_optician" required value="{{ old('doctor_or_optician') }}" placeholder="Dr. Robert Sp.M"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal Periksa *</label>
                    <input name="examination_date" type="date" required value="{{ old('examination_date', now()->toDateString()) }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
                <div class="sm:col-span-3">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Tipe Resep</label>
                    <input name="prescription_type" value="{{ old('prescription_type') }}" placeholder="Distance / Reading / Progressive"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
            </div>
        </div>

        @php
            $fields = ['sph' => 'SPH', 'cyl' => 'CYL', 'axis' => 'AXIS', 'add' => 'ADD', 'pd' => 'PD'];
        @endphp

        <div class="grid gap-6 md:grid-cols-2">
            @foreach (['od' => 'OD — Mata Kanan', 'os' => 'OS — Mata Kiri'] as $eye => $label)
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 flex items-center gap-2 text-sm font-bold text-slate-900">
                        <span class="grid h-7 w-7 place-items-center rounded-lg bg-slate-900 text-[10px] font-bold text-white">{{ strtoupper($eye) }}</span>
                        {{ $label }}
                    </h2>
                    <div class="grid grid-cols-3 gap-4">
                        @foreach ($fields as $key => $labelField)
                            <div>
                                <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $labelField }}</label>
                                <input name="{{ $eye }}_{{ $key }}" value="{{ old($eye.'_'.$key) }}" placeholder="{{ $key === 'axis' ? '90' : '-1.25' }}"
                                       class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">PD Total (mm)</label>
                    <input name="pd_total" value="{{ old('pd_total') }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Fitting Height</label>
                    <input name="fitting_height" value="{{ old('fitting_height') }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">
                </div>
                <div class="sm:col-span-3">
                    <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-slate-500">Catatan Resep</label>
                    <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-600">Simpan Resep</button>
                <a href="{{ route('prescriptions.index') }}" class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
            </div>
        </div>
    </form>
@endsection
