<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resep {{ $prescription->customer?->member_id }} · {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; } @media print { .no-print { display: none; } }</style>
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-4 flex items-center justify-between no-print">
            <a href="{{ url()->previous() }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600">← Kembali</a>
            <div class="flex gap-2">
                <a href="{{ route('prescriptions.edit', $prescription) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600">Edit Resep</a>
                <button onclick="window.print()" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Cetak Resep</button>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <div class="flex items-start justify-between border-b-2 border-slate-900 pb-5">
                <div>
                    <p class="text-lg font-extrabold tracking-tight text-slate-900">RESEP KACAMATA</p>
                    <p class="text-xs uppercase tracking-widest text-slate-400">Optical Prescription Card</p>
                </div>
                <div class="text-right text-xs text-slate-500">
                    <p class="font-mono font-bold text-slate-900">{{ $prescription->customer?->member_id }}</p>
                    <p>{{ optional($prescription->examination_date)->format('d F Y') }}</p>
                </div>
            </div>

            <div class="grid gap-4 border-b border-slate-200 py-5 text-sm sm:grid-cols-3">
                <div>
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">Nama Pasien</p>
                    <p class="font-bold text-slate-900">{{ $prescription->customer?->name }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">Dokter / Optometris</p>
                    <p class="font-bold text-slate-900">{{ $prescription->doctor_or_optician }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">Tipe Resep</p>
                    <p class="font-bold text-slate-900">{{ $prescription->prescription_type ?: '-' }}</p>
                </div>
            </div>

            @php
                $rows = ['SPH' => 'sph', 'CYL' => 'cyl', 'AXIS' => 'axis', 'ADD' => 'add', 'PD' => 'pd'];
            @endphp

            <div class="py-6">
                <table class="w-full text-center">
                    <thead>
                        <tr class="text-[11px] uppercase tracking-wider text-slate-400">
                            <th class="py-2 text-left">Parameter</th>
                            <th class="bg-slate-50 py-2 font-bold text-slate-900">OD (Kanan)</th>
                            <th class="bg-slate-50 py-2 font-bold text-slate-900">OS (Kiri)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($rows as $label => $key)
                            <tr>
                                <td class="py-3 text-left text-sm font-semibold text-slate-600">{{ $label }}</td>
                                <td class="bg-slate-50 py-3 font-mono text-lg font-bold text-slate-900">{{ $prescription->{'od_'.$key} ?: '—' }}</td>
                                <td class="bg-slate-50 py-3 font-mono text-lg font-bold text-slate-900">{{ $prescription->{'os_'.$key} ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid gap-4 border-t border-slate-200 pt-5 text-sm sm:grid-cols-3">
                <div>
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">PD Total</p>
                    <p class="font-mono font-bold text-slate-900">{{ $prescription->pd_total ?: '—' }} mm</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">Fitting Height</p>
                    <p class="font-mono font-bold text-slate-900">{{ $prescription->fitting_height ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-[11px] uppercase tracking-wider text-slate-400">No. Resep</p>
                    <p class="font-mono font-bold text-slate-900">RX-{{ str_pad((string) $prescription->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>

            @if ($prescription->notes)
                <p class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ $prescription->notes }}</p>
            @endif

            <div class="mt-8 flex justify-between text-xs text-slate-400">
                <span>Dokter / Optometris</span>
                <span class="w-48 border-t border-dashed border-slate-300 pt-1 text-center">{{ $prescription->doctor_or_optician }}</span>
            </div>
        </div>
    </div>
</body>
</html>
