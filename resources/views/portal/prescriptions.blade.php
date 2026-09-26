@extends('layouts.member')

@section('title', 'Resep Saya')

@section('content')
    <h1 class="mb-4 text-lg font-extrabold text-slate-900">Riwayat Resep</h1>

    <div class="space-y-4">
        @forelse ($prescriptions as $rx)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-slate-900">{{ $rx->doctor_or_optician }}</p>
                        <p class="text-xs text-slate-500">{{ optional($rx->examination_date)->format('d M Y') }} · {{ $rx->prescription_type ?: 'Umum' }}</p>
                    </div>
                    <span class="font-mono text-[10px] font-bold text-slate-400">RX-{{ str_pad((string) $rx->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @foreach (['od' => 'OD Kanan', 'os' => 'OS Kiri'] as $eye => $label)
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                            <div class="grid grid-cols-2 gap-y-1 text-xs">
                                @foreach (['sph' => 'SPH', 'cyl' => 'CYL', 'axis' => 'AXIS', 'add' => 'ADD'] as $k => $lbl)
                                    <span class="text-slate-500">{{ $lbl }}</span>
                                    <span class="text-right font-mono font-bold text-slate-900">{{ $rx->{$eye.'_'.$k} ?: '—' }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($rx->notes)
                    <p class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">{{ $rx->notes }}</p>
                @endif
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-400">
                Belum ada resep kacamata.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $prescriptions->links() }}</div>
@endsection
