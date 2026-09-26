<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $customer = $this->customer($request);

        return view('portal.dashboard', [
            'customer' => $customer,
            'latestPrescription' => $customer->latestPrescription(),
            'latestTransaction' => $customer->transactions()->with('items')->latest('transaction_date')->first(),
            'promotions' => Promotion::active()->latest()->take(3)->get(),
        ]);
    }

    public function prescriptions(Request $request): View
    {
        return view('portal.prescriptions', [
            'prescriptions' => $this->customer($request)->prescriptions()->latest('examination_date')->paginate(10),
        ]);
    }

    public function transactions(Request $request): View
    {
        return view('portal.transactions', [
            'transactions' => $this->customer($request)->transactions()->with('items')->latest('transaction_date')->paginate(10),
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
