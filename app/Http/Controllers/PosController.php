<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Frame;
use App\Models\Lens;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    public function create(): View
    {
        $frames = Frame::where('status', 'active')->orderBy('name')->get();
        $lenses = Lens::orderBy('name')->get();
        $promotions = Promotion::active()->orderBy('name')->get();
        $prescriptions = Prescription::query()->latest('examination_date')->get();

        return view('pos.create', [
            'promotions' => $promotions,
            'catalog' => [
                'customers' => Customer::where('status', 'active')->orderBy('name')
                    ->get(['id', 'name', 'phone', 'member_id'])->values(),
                'frames' => $frames->map(fn (Frame $f) => [
                    'id' => $f->id, 'sku' => $f->sku, 'name' => $f->name, 'brand' => $f->brand,
                    'price' => (float) $f->sell_price, 'stock' => $f->stock,
                ])->values(),
                'lenses' => $lenses->map(fn (Lens $l) => [
                    'id' => $l->id, 'sku' => $l->sku, 'name' => $l->name, 'brand' => $l->brand,
                    'type' => $l->lens_type, 'index' => $l->index_val,
                    'price' => (float) $l->sell_price, 'stock' => $l->stock,
                ])->values(),
                'promos' => $promotions->map(fn (Promotion $p) => [
                    'id' => $p->id, 'name' => $p->name, 'type' => $p->promo_type,
                    'value' => (float) $p->discount_value, 'min' => (float) $p->min_spend,
                ])->values(),
                'rx' => $prescriptions->map(fn (Prescription $p) => [
                    'id' => $p->id, 'customer_id' => $p->customer_id,
                    'doctor' => $p->doctor_or_optician,
                    'date' => optional($p->examination_date)->format('d/m/Y'),
                ])->values(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.type' => ['required', 'in:frame,lens'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'promo_id' => ['nullable', 'exists:promotions,id'],
            'payment_method' => ['required', 'in:cash,transfer,qris,card'],
            'payment_status' => ['required', 'in:paid,down_payment'],
            'dp_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:ordered,processing,ready,completed'],
            'prescription_id' => ['nullable', 'exists:prescriptions,id'],
            'new_prescription' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['payment_status'] === 'down_payment' && ! ($request->filled('dp_amount') && (float) $request->input('dp_amount') > 0)) {
            throw ValidationException::withMessages(['dp_amount' => 'Nominal DP wajib diisi lebih dari 0.']);
        }

        if ($request->boolean('new_prescription')) {
            $this->validateInlinePrescription($request);
        }

        $transaction = DB::transaction(function () use ($request, $data) {
            $lines = $this->resolveItems($data['items']);

            $subtotal = collect($lines)->sum(fn (array $line) => $line['price'] * $line['quantity']);
            [$promo, $discount] = $this->applyPromotion($data['promo_id'] ?? null, $subtotal);
            $total = max(0, $subtotal - $discount);

            $prescriptionId = $data['prescription_id'] ?? null;
            if ($request->boolean('new_prescription')) {
                $inline = $request->validate([
                    'prescription.doctor_or_optician' => ['required', 'string', 'max:120'],
                    'prescription.examination_date' => ['required', 'date'],
                    'prescription.prescription_type' => ['nullable', 'string', 'max:50'],
                    'prescription.od_sph' => ['nullable', 'string', 'max:10'],
                    'prescription.od_cyl' => ['nullable', 'string', 'max:10'],
                    'prescription.od_axis' => ['nullable', 'string', 'max:10'],
                    'prescription.od_add' => ['nullable', 'string', 'max:10'],
                    'prescription.od_pd' => ['nullable', 'string', 'max:10'],
                    'prescription.os_sph' => ['nullable', 'string', 'max:10'],
                    'prescription.os_cyl' => ['nullable', 'string', 'max:10'],
                    'prescription.os_axis' => ['nullable', 'string', 'max:10'],
                    'prescription.os_add' => ['nullable', 'string', 'max:10'],
                    'prescription.os_pd' => ['nullable', 'string', 'max:10'],
                    'prescription.pd_total' => ['nullable', 'string', 'max:10'],
                    'prescription.fitting_height' => ['nullable', 'string', 'max:10'],
                    'prescription.notes' => ['nullable', 'string', 'max:1000'],
                ])['prescription'];

                $inline['customer_id'] = (int) $data['customer_id'];
                $prescriptionId = Prescription::create($inline)->id;
            }

            $transaction = Transaction::create([
                'invoice_number' => $this->nextInvoiceNumber(),
                'store_id' => $request->user()->store_id,
                'staff_id' => $request->user()->id,
                'customer_id' => $data['customer_id'],
                'prescription_id' => $prescriptionId,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'promo_id' => $promo?->id,
                'total_amount' => $total,
                'payment_status' => $data['payment_status'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
                'transaction_date' => now(),
            ]);

            foreach ($lines as $line) {
                TransactionItem::create($line + ['transaction_id' => $transaction->id]);

                if ($line['item_type'] === 'frame') {
                    Frame::whereKey($line['frame_id'])->decrement('stock', $line['quantity']);
                } else {
                    Lens::whereKey($line['lens_id'])->decrement('stock', $line['quantity']);
                }
            }

            Payment::create([
                'transaction_id' => $transaction->id,
                'amount' => $data['payment_status'] === 'paid' ? $total : (float) $data['dp_amount'],
                'payment_method' => $data['payment_method'],
                'paid_at' => now(),
                'note' => $data['payment_status'] === 'paid' ? 'Pelunasan penuh.' : 'Uang muka (DP).',
            ]);

            return $transaction;
        });

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Transaksi {$transaction->invoice_number} berhasil dibuat.");
    }

    public function quickCustomer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20', 'unique:customers,phone'],
        ]);

        $customer = Customer::create($data + [
            'member_id' => Customer::generateMemberId(),
            'registered_at' => now()->toDateString(),
            'status' => 'active',
        ]);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'member_id' => $customer->member_id,
        ], 201);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function resolveItems(array $items): array
    {
        $lines = [];

        foreach ($items as $item) {
            $qty = (int) $item['qty'];

            if ($item['type'] === 'frame') {
                $model = Frame::query()->lockForUpdate()->find($item['id']);
                if (! $model) {
                    throw ValidationException::withMessages(['items' => 'Frame tidak ditemukan.']);
                }
                if ($model->stock < $qty) {
                    throw ValidationException::withMessages(['items' => "Stok frame {$model->name} tersisa {$model->stock}."]);
                }
                $lines[] = [
                    'item_type' => 'frame',
                    'frame_id' => $model->id,
                    'lens_id' => null,
                    'name' => $model->name,
                    'quantity' => $qty,
                    'price' => (float) $model->sell_price,
                    'discount' => 0,
                    'subtotal' => (float) $model->sell_price * $qty,
                ];

                continue;
            }

            $model = Lens::query()->lockForUpdate()->find($item['id']);
            if (! $model) {
                throw ValidationException::withMessages(['items' => 'Lensa tidak ditemukan.']);
            }
            if ($model->stock < $qty) {
                throw ValidationException::withMessages(['items' => "Stok lensa {$model->name} tersisa {$model->stock}."]);
            }
            $lines[] = [
                'item_type' => 'lens',
                'frame_id' => null,
                'lens_id' => $model->id,
                'name' => $model->name,
                'quantity' => $qty,
                'price' => (float) $model->sell_price,
                'discount' => 0,
                'subtotal' => (float) $model->sell_price * $qty,
            ];
        }

        return $lines;
    }

    /**
     * @return array{0: ?Promotion, 1: float}
     */
    private function applyPromotion(?int $promoId, float $subtotal): array
    {
        if (! $promoId) {
            return [null, 0];
        }

        $promo = Promotion::active()->find($promoId);

        if (! $promo || $subtotal < (float) $promo->min_spend) {
            return [null, 0];
        }

        $discount = $promo->promo_type === 'nominal'
            ? (float) $promo->discount_value
            : $subtotal * ((float) $promo->discount_value / 100);

        return [$promo, min($subtotal, round($discount))];
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = 'TRX-'.now()->format('Ymd').'-';

        $last = Transaction::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    private function validateInlinePrescription(Request $request): void
    {
        $request->validate([
            'prescription' => ['required', 'array'],
            'prescription.doctor_or_optician' => ['required', 'string', 'max:120'],
            'prescription.examination_date' => ['required', 'date'],
            'prescription.od_sph' => ['nullable', 'string', 'max:10'],
            'prescription.os_sph' => ['nullable', 'string', 'max:10'],
        ]);
    }
}
