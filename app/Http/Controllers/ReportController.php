<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('reports.index', [
            'rows' => $this->query($request)->paginate(15)->withQueryString(),
            'summary' => $this->summary($this->query($request)->get()),
            'staff' => User::whereIn('role', ['admin', 'staff'])->orderBy('name')->get(),
            'filters' => $request->only(['from', 'to', 'staff_id', 'payment_status', 'item_type']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->query($request)->get();
        $date = now()->format('Ymd-His');

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Invoice', 'Tanggal', 'Customer', 'Kasir', 'Subtotal', 'Diskon', 'Total', 'Status Bayar', 'Metode', 'Status Order']);

            foreach ($rows as $t) {
                fputcsv($out, [
                    $t->invoice_number,
                    optional($t->transaction_date)->format('d/m/Y H:i'),
                    $t->customer?->name,
                    $t->staff?->name,
                    $t->subtotal,
                    $t->discount_amount,
                    $t->total_amount,
                    $t->payment_status,
                    $t->payment_method,
                    $t->status,
                ]);
            }

            fclose($out);
        }, "laporan-penjualan-{$date}.csv", ['Content-Type' => 'text/csv']);
    }

    /**
     * @return Builder<Transaction>
     */
    private function query(Request $request)
    {
        return Transaction::query()
            ->with(['customer', 'staff'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->date('to')))
            ->when($request->filled('staff_id'), fn ($q) => $q->where('staff_id', $request->integer('staff_id')))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('item_type'), fn ($q) => $q->whereHas(
                'items',
                fn ($item) => $item->where('item_type', $request->string('item_type')),
            ))
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->orderBy('transaction_date');
    }

    /**
     * @param  Collection<int, Transaction>  $rows
     * @return array<string, float|int>
     */
    private function summary($rows): array
    {
        $gross = (float) $rows->sum('subtotal');
        $discount = (float) $rows->sum('discount_amount');
        $count = $rows->count();

        return [
            'gross' => $gross,
            'discount' => $discount,
            'net' => $gross - $discount,
            'count' => $count,
            'atv' => $count > 0 ? ($gross - $discount) / $count : 0,
        ];
    }
}
