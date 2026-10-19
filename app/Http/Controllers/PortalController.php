<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $customer = $this->customer($request);

        return view('portal.dashboard', [
            'customer' => $customer,
            'latestTransaction' => $customer->transactions()->with('payments')->latest('sales_date')->latest('sales_no')->first(),
            'promotions' => Promotion::active()->latest()->take(3)->get(),
        ]);
    }

    public function transactions(Request $request): View
    {
        return view('portal.transactions', [
            'transactions' => $this->customer($request)->transactions()->with('items')->latest('sales_date')->latest('sales_no')->paginate(10),
        ]);
    }

    /**
     * Detail transaksi milik member yang login (riwayat transaksi -> detail).
     */
    public function transactionShow(Request $request, Transaction $transaction): View
    {
        $customer = $this->customer($request);

        abort_unless((int) $transaction->customer_id === (int) $customer->id, 404);

        $transaction->load(['items', 'prescriptions', 'payments']);

        return view('portal.transaction-detail', [
            'transaction' => $transaction,
            'groups' => $transaction->wearerGroups(),
        ]);
    }

    public function promos(): View
    {
        return view('portal.promos', [
            'promotions' => Promotion::active()->orderByDesc('start_date')->paginate(10),
        ]);
    }

    private function customer(Request $request): Customer
    {
        $customer = $request->user()->customer;

        abort_unless($customer, 403, 'Akun Anda belum terhubung ke kartu member.');

        return $customer;
    }
}
