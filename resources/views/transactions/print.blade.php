<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nota {{ $transaction->invoice_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        @media print { .no-print { display: none !important; } body { background: #fff; } }
    </style>
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="mx-auto max-w-lg px-4 py-6">
        <div class="mb-4 flex items-center justify-between no-print">
            <button onclick="history.back()" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600">← Kembali</button>
            <button onclick="window.print()" class="rounded-xl bg-slate-900 px-5 py-2 text-sm font-semibold text-white">Cetak Struk</button>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <div class="border-b-2 border-dashed border-slate-300 pb-4 text-center">
                <img src="{{ asset('images/logo.jpg') }}" alt="Logo" class="mx-auto mb-2 h-10 w-10 rounded-xl">
                <p class="text-lg font-extrabold tracking-tight text-slate-900">{{ $transaction->store?->name ?? 'OPTIK' }}</p>
                <p class="text-xs text-slate-500">{{ $transaction->store?->address ?? '' }}</p>
                <p class="text-xs text-slate-500">{{ $transaction->store?->phone ?? '' }}</p>
            </div>

            <div class="border-b border-dashed border-slate-300 py-4 text-xs">
                <div class="flex justify-between"><span class="text-slate-500">No. Invoice</span><span class="font-bold text-slate-900">{{ $transaction->invoice_number }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Tanggal</span><span>{{ $transaction->transaction_date?->format('d/m/Y H:i') }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Kasir</span><span>{{ $transaction->staff?->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Customer</span><span class="font-semibold">{{ $transaction->customer?->name ?? '-' }}</span></div>
                @if ($transaction->customer)
                    <div class="flex justify-between"><span class="text-slate-500">Member</span><span class="font-mono">{{ $transaction->customer->member_id }}</span></div>
                @endif
            </div>

            <table class="w-full border-b border-dashed border-slate-300 py-2 text-xs">
                <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-2">Item</th>
                        <th class="py-2 text-center">Qty</th>
                        <th class="py-2 text-right">Harga</th>
                        <th class="py-2 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($transaction->items as $item)
                        <tr>
                            <td class="py-2 pr-2">
                                <p class="font-semibold text-slate-900">{{ $item->name }}</p>
                                @if ($item->frame?->sku || $item->lens?->sku)
                                    <p class="text-[10px] text-slate-400">{{ $item->frame?->sku ?? $item->lens?->sku }}</p>
                                @endif
                            </td>
                            <td class="py-2 text-center">{{ $item->quantity }}</td>
                            <td class="py-2 text-right">@idr($item->price)</td>
                            <td class="py-2 text-right font-semibold">@idr($item->subtotal)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="space-y-1.5 border-b border-dashed border-slate-300 py-4 text-xs">
                <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span>@idr($transaction->subtotal)</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Diskon {{ $transaction->promotion?->name ? '· '.$transaction->promotion->name : '' }}</span><span class="text-rose-600">-@idr($transaction->discount_amount)</span></div>
                <div class="flex justify-between text-base font-extrabold text-slate-900"><span>TOTAL</span><span>@idr($transaction->total_amount)</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Metode Bayar</span><span class="capitalize">{{ $transaction->payment_method }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Status Bayar</span><span class="font-bold {{ $transaction->payment_status === 'paid' ? 'text-red-600' : 'text-amber-600' }}">{{ $transaction->payment_status === 'paid' ? 'LUNAS' : 'DP / UANG MUKA' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Status Pesanan</span><span class="capitalize">{{ $transaction->status }}</span></div>
                @if ($transaction->balanceDue() > 0)
                    <div class="flex justify-between font-bold text-rose-600"><span>Sisa Tagihan</span><span>@idr($transaction->balanceDue())</span></div>
                @endif
            </div>

            <div class="grid grid-cols-5 gap-1 pt-4 text-[10px] text-slate-400">
                @for ($i = 0; $i < 25; $i++)
                    <span class="h-8 {{ $i % 3 === 0 ? 'bg-slate-800' : ($i % 2 === 0 ? 'bg-slate-600' : 'bg-slate-300') }}"></span>
                @endfor
            </div>

            <p class="pt-3 text-center text-[11px] text-slate-500">Terima kasih telah berbelanja.<br>Barang yang sudah dibeli tidak dapat ditukar tanpa cacat pabrik.</p>
        </div>
    </div>
</body>
</html>
