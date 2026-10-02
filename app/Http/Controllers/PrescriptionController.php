<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function index(Request $request): View
    {
        $prescriptions = Prescription::query()
            ->with('customer')
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = "%{$request->string('q')}%";
                $q->where(fn ($w) => $w->where('doctor_or_optician', 'like', $term)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term)->orWhere('member_id', 'like', $term)));
            })
            ->latest('examination_date')
            ->paginate(10)
            ->withQueryString();

        return view('prescriptions.index', ['prescriptions' => $prescriptions, 'filters' => $request->only('q')]);
    }

    public function create(Request $request): View
    {
        return view('prescriptions.form', [
            'customers' => Customer::orderBy('name')->get(),
            'presetCustomer' => $request->integer('customer_id') ? Customer::find($request->integer('customer_id')) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $prescription = Prescription::create($this->validated($request));

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Resep kacamata berhasil disimpan.');
    }

    public function edit(Prescription $prescription): View
    {
        return view('prescriptions.form', [
            'customers' => Customer::orderBy('name')->get(),
            'presetCustomer' => $prescription->customer,
            'prescription' => $prescription,
        ]);
    }

    public function update(Request $request, Prescription $prescription): RedirectResponse
    {
        $prescription->update($this->validated($request));

        return redirect()->route('prescriptions.show', $prescription)
            ->with('success', 'Resep kacamata berhasil diperbarui.');
    }

    public function show(Prescription $prescription): View
    {
        return view('prescriptions.show', ['prescription' => $prescription->load('customer')]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'doctor_or_optician' => ['required', 'string', 'max:120'],
            'examination_date' => ['required', 'date'],
            'prescription_type' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'in:'.implode(',', array_keys(Prescription::SOURCES))],
            'od_sph' => ['nullable', 'string', 'max:10'],
            'od_cyl' => ['nullable', 'string', 'max:10'],
            'od_axis' => ['nullable', 'string', 'max:10'],
            'od_add' => ['nullable', 'string', 'max:10'],
            'od_pd' => ['nullable', 'string', 'max:10'],
            'os_sph' => ['nullable', 'string', 'max:10'],
            'os_cyl' => ['nullable', 'string', 'max:10'],
            'os_axis' => ['nullable', 'string', 'max:10'],
            'os_add' => ['nullable', 'string', 'max:10'],
            'os_pd' => ['nullable', 'string', 'max:10'],
            'pd_total' => ['nullable', 'string', 'max:10'],
            'fitting_height' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['prescription_type'] = ! empty($data['prescription_type']) ? $data['prescription_type'] : 'Distance';
        $data['source'] = ! empty($data['source']) ? $data['source'] : 'in_store';

        return $data;
    }
}
