<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Prescription;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionUser;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $customer = $this->customer($request);

        return view('portal.dashboard', [
            'customer' => $customer,
            'latestTransaction' => $customer->transactions()->with('items')->latest('transaction_date')->first(),
            'promotions' => Promotion::active()->latest()->take(3)->get(),
        ]);
    }

    public function transactions(Request $request): View
    {
        return view('portal.transactions', [
            'transactions' => $this->customer($request)->transactions()->with('items')->latest('transaction_date')->paginate(10),
        ]);
    }

    /**
     * Detail transaksi milik member yang login (riwayat transaksi -> detail).
     */
    public function transactionShow(Request $request, Transaction $transaction): View
    {
        $customer = $this->customer($request);

        abort_unless((int) $transaction->customer_id === (int) $customer->id, 404);

        $transaction->load(['users.prescription', 'items', 'payments', 'promotion', 'store', 'customer']);

        return view('portal.transaction-detail', [
            'transaction' => $transaction,
            'groups' => $this->wearerGroups($transaction),
        ]);
    }

    public function promos(): View
    {
        return view('portal.promos', [
            'promotions' => Promotion::active()->orderByDesc('start_date')->paginate(10),
        ]);
    }

    /**
     * Item transaksi dikelompokkan per pemakai (user), termasuk kelompok
     * "Umum" untuk item yang tidak terikat pemakai tertentu.
     *
     * @return Collection<int, array{label: string, rx: Prescription|null, items: Collection<int, TransactionItem>}>
     */
    private function wearerGroups(Transaction $transaction): Collection
    {
        $groups = $transaction->users
            ->map(fn (TransactionUser $user): array => [
                'label' => $user->name,
                'rx' => $user->prescription,
                'items' => $transaction->items->where('transaction_user_id', $user->id)->values(),
            ])
            ->values();

        $unassigned = $transaction->items->whereNull('transaction_user_id');

        if ($unassigned->isNotEmpty()) {
            $groups->push([
                'label' => 'Umum',
                'rx' => $transaction->prescription,
                'items' => $unassigned->values(),
            ]);
        }

        if ($groups->isEmpty()) {
            $groups->push([
                'label' => $transaction->customer?->name ?? 'Umum',
                'rx' => $transaction->prescription,
                'items' => $transaction->items,
            ]);
        }

        return $groups;
    }

    private function customer(Request $request): Customer
    {
        $customer = $request->user()->customer;

        abort_unless($customer, 403, 'Akun Anda belum terhubung ke kartu member.');

        return $customer;
    }
}
