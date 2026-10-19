<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    private const STATUSES = ['ordered', 'processing', 'ready', 'completed', 'cancelled'];

    public function index(Request $request): View
    {
        $transactions = Transaction::query()
            ->with('customer')
            ->withSum('payments as amount_paid', 'amount')
            ->when($request->filled('status'), fn ($q) => $q->where('sales_status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where('sales_no', 'like', "%{$request->string('q')}%"))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('sales_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('sales_date', '<=', $request->date('to')))
            ->latest('sales_date')
            ->latest('sales_no')
            ->paginate(10)
            ->withQueryString();

        return view('transactions.index', [
            'transactions' => $transactions,
            'filters' => $request->only(['status', 'q', 'from', 'to']),
            'statuses' => self::STATUSES,
        ]);
    }

    public function show(Transaction $transaction): View
    {
        return view('transactions.show', [
            'transaction' => $transaction->load(['customer', 'items', 'prescriptions', 'payments']),
        ]);
    }

    public function updateStatus(Request $request, Transaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
        ]);

        $transaction->update(['sales_status' => $data['status']]);

        return back()->with('success', "Status transaksi diubah menjadi {$data['status']}.");
    }

    /**
     * Update petugas RO1 (periksa mata) & RO2 (potong lensa) untuk satu
     * pemakai dalam transaksi — disimpan pada baris resep pemakai tersebut.
     */
    public function updateGroup(Request $request, Transaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:120'],
            'ro1' => ['nullable', 'string', 'max:120'],
            'ro2' => ['nullable', 'string', 'max:120'],
        ]);

        $sortnos = $transaction->items()->where('username', $data['username'])->pluck('sortno');

        $transaction->prescriptions()->whereIn('sortno', $sortnos)->update([
            'ro1' => $this->blankToNull($data['ro1'] ?? null),
            'ro2' => $this->blankToNull($data['ro2'] ?? null),
        ]);

        return back()->with(
            'success',
            "Petugas pemakai \"{$data['username']}\" diperbarui: RO1 ".($data['ro1'] ?: '-').', RO2 '.($data['ro2'] ?: '-').'.'
        );
    }

    public function print(Transaction $transaction): View
    {
        return view('transactions.print', [
            'transaction' => $transaction->load(['customer', 'items', 'prescriptions', 'payments']),
        ]);
    }

    private function blankToNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
