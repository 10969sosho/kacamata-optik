<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
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
            'filters' => $request->only(['from', 'to', 'input_by', 'payment_by', 'item_type']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->query($request)->get();
        $date = now()->format('Ymd-His');

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Sales No', 'Tanggal', 'Jam', 'Customer', 'Kasir', 'Grandtotal', 'Metode Bayar', 'Status']);

            foreach ($rows as $t) {
                fputcsv($out, [
                    $t->sales_no,
                    $t->sales_date?->format('d/m/Y'),
                    substr((string) $t->sales_time, 0, 5),
                    $t->customer?->name,
                    $t->input_by,
                    $t->grandtotal,
                    $t->payment_by,
                    $t->sales_status,
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
            ->with(['customer', 'items'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('sales_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('sales_date', '<=', $request->date('to')))
            ->when($request->filled('input_by'), fn ($q) => $q->where('input_by', $request->string('input_by')))
            ->when($request->filled('payment_by'), fn ($q) => $q->where('payment_by', $request->string('payment_by')))
            ->when($request->filled('item_type'), fn ($q) => $q->whereHas(
                'items',
                fn ($item) => $item->where('item_type', $request->string('item_type')),
            ))
            ->whereNotIn('sales_status', ['cancelled', 'refunded'])
            ->orderBy('sales_date')
            ->orderBy('sales_no');
    }

    /**
     * @param  Collection<int, Transaction>  $rows
     * @return array<string, float|int>
     */
    private function summary($rows): array
    {
        $gross = (float) $rows->sum('grandtotal');
        $discount = (float) TransactionItem::query()
            ->whereIn('sales_no', $rows->pluck('sales_no'))
            ->get()
            ->sum(fn (TransactionItem $item) => $item->discountAmount());
        $count = $rows->count();

        return [
            'gross' => $gross,
            'discount' => $discount,
            'net' => $gross,
            'count' => $count,
            'atv' => $count > 0 ? $gross / $count : 0,
        ];
    }
}
