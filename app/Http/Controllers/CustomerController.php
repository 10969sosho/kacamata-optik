<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::query()
            ->withCount('transactions')
            ->when($request->filled('q'), fn ($q) => $q->where(
                fn ($w) => $w->where('name', 'like', "%{$request->string('q')}%")
                    ->orWhere('phone', 'like', "%{$request->string('q')}%")
                    ->orWhere('member_id', 'like', "%{$request->string('q')}%"),
            ))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('customers.index', [
            'customers' => $customers,
            'filters' => $request->only(['q', 'status']),
        ]);
    }

    public function create(): View
    {
        return view('customers.form', ['customer' => new Customer]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['member_id'] = Customer::generateMemberId();
        $data['registered_at'] = $data['registered_at'] ?? now()->toDateString();
        $data['status'] = $request->input('status', 'active');

        $customer = Customer::create($data);

        return redirect()->route('customers.show', $customer)
            ->with('success', "Customer {$customer->name} terdaftar dengan Member ID {$customer->member_id}.");
    }

    public function show(Customer $customer): View
    {
        $customer->load([
            'prescriptions' => fn ($q) => $q->latest('examination_date'),
            'transactions' => fn ($q) => $q->with('items')->latest('transaction_date'),
        ]);

        return view('customers.show', ['customer' => $customer]);
    }

    public function edit(Customer $customer): View
    {
        return view('customers.form', ['customer' => $customer]);
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validated($request, $customer);
        $data['status'] = $request->input('status', $customer->status);

        $customer->update($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Data customer diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Customer $customer = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20', 'unique:customers,phone'.($customer ? ','.$customer->id : '')],
            'email' => ['nullable', 'email', 'max:120', 'unique:customers,email'.($customer ? ','.$customer->id : '')],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:Laki-laki,Perempuan'],
            'address' => ['nullable', 'string', 'max:255'],
            'registered_at' => ['nullable', 'date'],
        ]);
    }
}
