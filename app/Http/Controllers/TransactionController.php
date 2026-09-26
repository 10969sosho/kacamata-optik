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
            ->with(['customer', 'staff'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where('invoice_number', 'like', "%{$request->string('q')}%"))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('transaction_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('transaction_date', '<=', $request->date('to')))
            ->latest('transaction_date')
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
            'transaction' => $transaction->load(['customer', 'staff', 'store', 'items.frame', 'items.lens', 'payments', 'promotion', 'prescription']),
        ]);
    }

    public function updateStatus(Request $request, Transaction $transaction): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', self::STATUSES)],
        ]);

        $transaction->update(['status' => $data['status']]);

        return back()->with('success', "Status transaksi diubah menjadi {$data['status']}.");
    }

    public function print(Transaction $transaction): View
    {
        return view('transactions.print', [
            'transaction' => $transaction->load(['customer', 'staff', 'store', 'items', 'payments', 'promotion']),
        ]);
    }
}
