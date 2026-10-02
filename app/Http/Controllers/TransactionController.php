<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionUser;
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

    /**
     * Update status pengerjaan + petugas (RO1 periksa mata, RO2 potong lensa)
     * untuk satu pemakai dalam transaksi.
     */
    public function updateUser(Request $request, Transaction $transaction, TransactionUser $transactionUser): RedirectResponse
    {
        abort_unless((int) $transactionUser->transaction_id === (int) $transaction->id, 404);

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', TransactionUser::STATUSES)],
            'ro1' => ['nullable', 'string', 'max:120'],
            'ro2' => ['nullable', 'string', 'max:120'],
        ]);

        $transactionUser->update($data);

        return back()->with(
            'success',
            "Pemakai \"{$transactionUser->name}\" → status {$transactionUser->status}, RO1 ".($transactionUser->ro1 ?: '-').', RO2 '.($transactionUser->ro2 ?: '-').'.'
        );
    }

    public function print(Transaction $transaction): View
    {
        return view('transactions.print', [
            'transaction' => $transaction->load(['customer', 'staff', 'store', 'items', 'payments', 'promotion']),
        ]);
    }
}
