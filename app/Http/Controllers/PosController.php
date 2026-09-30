<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Customer;
use App\Models\Frame;
use App\Models\Lens;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionUser;
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
        $lenses = Lens::with('category')->orderBy('name')->get();
        $accessories = Accessory::active()->with('category')->orderBy('name')->get();
        $promotions = Promotion::active()->orderBy('name')->get();
        $prescriptions = Prescription::query()->latest('examination_date')->get();

        $frameItems = $frames->map(fn (Frame $frame) => [
            'type' => 'frame',
            'id' => $frame->id,
            'sku' => $frame->sku,
            'name' => $frame->name,
            'brand' => $frame->brand,
            'category' => 'Frame',
            'detail' => $frame->size ? 'Ukuran '.$frame->size : $frame->brand,
            'price' => (float) $frame->sell_price,
            'stock' => (int) $frame->stock,
        ]);

        $lensItems = $lenses->map(fn (Lens $lens) => [
            'type' => 'lens',
            'id' => $lens->id,
            'sku' => $lens->sku,
            'name' => $lens->name,
            'brand' => $lens->brand,
            'category' => $lens->category?->name ?? 'Lensa',
            'detail' => collect([$lens->lens_type, $lens->index_val])->filter()->implode(' · '),
            'price' => (float) $lens->sell_price,
            'stock' => (int) $lens->stock,
        ]);

        $accessoryItems = $accessories->map(fn (Accessory $accessory) => [
            'type' => 'accessory',
            'id' => $accessory->id,
            'sku' => $accessory->sku,
            'name' => $accessory->name,
            'brand' => $accessory->brand ?? '-',
            'category' => $accessory->category?->name ?? 'Aksesoris',
            'detail' => $accessory->brand ?: ($accessory->category?->name ?? '-'),
            'price' => (float) $accessory->sell_price,
            'stock' => (int) $accessory->stock,
        ]);

        $items = $frameItems->concat($lensItems)->concat($accessoryItems)->values();

        return view('pos.create', [
            'promotions' => $promotions,
            'catalog' => [
                'customers' => Customer::where('status', 'active')->orderBy('name')
                    ->get(['id', 'name', 'phone', 'member_id'])->values(),
                'items' => $items,
                'promos' => $promotions->map(fn (Promotion $promotion) => [
                    'id' => $promotion->id, 'name' => $promotion->name, 'type' => $promotion->promo_type,
                    'value' => (float) $promotion->discount_value, 'min' => (float) $promotion->min_spend,
                ])->values(),
                'rx' => $prescriptions->map(fn (Prescription $prescription) => [
                    'id' => $prescription->id,
                    'customer_id' => $prescription->customer_id,
                    'doctor' => $prescription->doctor_or_optician,
                    'date' => optional($prescription->examination_date)->format('d/m/Y'),
                ])->values(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'users' => ['required_without:items', 'array', 'min:1'],
            'users.*.name' => ['required', 'string', 'max:120'],
            'users.*.items' => ['required', 'array', 'min:1'],
            'users.*.items.*.type' => ['required', 'in:frame,lens,accessory'],
            'users.*.items.*.id' => ['required', 'integer'],
            'users.*.items.*.qty' => ['required', 'integer', 'min:1'],
            'users.*.prescription_id' => ['nullable', 'integer', 'exists:prescriptions,id'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.type' => ['required', 'in:frame,lens,accessory'],
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

        $groups = $this->resolveGroups($request, $data);
        $inlinePrescriptions = $this->validatePrescriptions($request, $groups);

        $transaction = DB::transaction(function () use ($request, $data, $groups, $inlinePrescriptions) {
            /** @var array<int, array<int, array<string, mixed>>> $linesByGroup */
            $linesByGroup = [];
            foreach ($groups as $index => $group) {
                foreach ($group['items'] as $item) {
                    $linesByGroup[$index][] = $this->resolveLine($item);
                }
            }

            $subtotal = collect($linesByGroup)->flatten(1)
                ->sum(fn (array $line) => $line['price'] * $line['quantity']);
            [$promo, $discount] = $this->applyPromotion($data['promo_id'] ?? null, (float) $subtotal);
            $total = max(0, $subtotal - $discount);

            $prescriptionIds = [];
            foreach ($groups as $index => $group) {
                $prescriptionIds[$index] = $group['prescription_id'] ?? null;

                if (! $prescriptionIds[$index] && isset($inlinePrescriptions[$index])) {
                    $prescriptionIds[$index] = Prescription::create(
                        $inlinePrescriptions[$index] + [
                            'customer_id' => (int) $data['customer_id'],
                            'prescription_type' => ($inlinePrescriptions[$index]['prescription_type'] ?? null) ?: 'Distance',
                        ]
                    )->id;
                }
            }

            $transaction = Transaction::create([
                'invoice_number' => $this->nextInvoiceNumber(),
                'store_id' => $request->user()->store_id,
                'staff_id' => $request->user()->id,
                'customer_id' => $data['customer_id'],
                'prescription_id' => collect($prescriptionIds)->filter()->first(),
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

            foreach ($groups as $index => $group) {
                $wearer = TransactionUser::create([
                    'transaction_id' => $transaction->id,
                    'name' => $group['name'],
                    'prescription_id' => $prescriptionIds[$index] ?? null,
                ]);

                foreach ($linesByGroup[$index] as $line) {
                    TransactionItem::create($line + [
                        'transaction_id' => $transaction->id,
                        'transaction_user_id' => $wearer->id,
                    ]);

                    $this->decrementStock($line);
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
     * Satu transaksi = satu customer/member, berisi satu atau beberapa pemakai
     * (mis. Ayah, Anak 1, Anak 2). Format lama `items[]` diperlakukan sebagai
     * satu pemakai bernama customer.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, array{name: string, items: array<int, array<string, mixed>>, prescription_id: ?int, new_prescription: bool, prescription: array<string, mixed>}>
     */
    private function resolveGroups(Request $request, array $data): array
    {
        if (isset($data['users'])) {
            $groups = [];

            foreach ($data['users'] as $index => $user) {
                $groups[$index] = [
                    'name' => trim($user['name']),
                    'items' => $user['items'],
                    'prescription_id' => ! empty($user['prescription_id']) ? (int) $user['prescription_id'] : null,
                    'new_prescription' => $request->boolean("users.{$index}.new_prescription"),
                    'prescription' => $request->input("users.{$index}.prescription") ?? [],
                ];
            }

            return $groups;
        }

        $customer = Customer::query()->findOrFail($data['customer_id']);

        return [[
            'name' => $customer->name,
            'items' => $data['items'],
            'prescription_id' => ! empty($data['prescription_id']) ? (int) $data['prescription_id'] : null,
            'new_prescription' => (bool) ($data['new_prescription'] ?? false),
            'prescription' => $request->input('prescription') ?? [],
        ]];
    }

    /**
     * Validasi resep tiap pemakai: pemakai yang memilih lensa wajib punya resep
     * (resep tersimpan atau input baru). Resep baru divalidasi per pemakai.
     *
     * @param  array<int, array{name: string, items: array<int, array<string, mixed>>, prescription_id: ?int, new_prescription: bool, prescription: array<string, mixed>}>  $groups
     * @return array<int, array<string, mixed>>
     */
    private function validatePrescriptions(Request $request, array $groups): array
    {
        $isNewFormat = $request->has('users');
        $messages = [];
        $payloads = [];

        foreach ($groups as $index => $group) {
            $hasLens = collect($group['items'])->contains('type', 'lens');
            $useInline = (bool) $group['new_prescription'] || ! empty($group['prescription']);
            $key = $isNewFormat ? "users.{$index}.prescription" : 'prescription';

            if ($isNewFormat && $hasLens && ! $group['prescription_id'] && ! $useInline) {
                $messages["users.{$index}.prescription_id"] = "Resep untuk pemakai \"{$group['name']}\" wajib diisi karena memilih lensa.";

                continue;
            }

            if ($useInline) {
                $payloads[$index] = $this->validateInlinePrescription($request, $key);
            }
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }

        return $payloads;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateInlinePrescription(Request $request, string $key): array
    {
        $validated = $request->validate([
            "{$key}.doctor_or_optician" => ['required', 'string', 'max:120'],
            "{$key}.examination_date" => ['required', 'date'],
            "{$key}.prescription_type" => ['nullable', 'string', 'max:50'],
            "{$key}.od_sph" => ['nullable', 'string', 'max:10'],
            "{$key}.od_cyl" => ['nullable', 'string', 'max:10'],
            "{$key}.od_axis" => ['nullable', 'string', 'max:10'],
            "{$key}.od_add" => ['nullable', 'string', 'max:10'],
            "{$key}.od_pd" => ['nullable', 'string', 'max:10'],
            "{$key}.os_sph" => ['nullable', 'string', 'max:10'],
            "{$key}.os_cyl" => ['nullable', 'string', 'max:10'],
            "{$key}.os_axis" => ['nullable', 'string', 'max:10'],
            "{$key}.os_add" => ['nullable', 'string', 'max:10'],
            "{$key}.os_pd" => ['nullable', 'string', 'max:10'],
            "{$key}.pd_total" => ['nullable', 'string', 'max:10'],
            "{$key}.fitting_height" => ['nullable', 'string', 'max:10'],
            "{$key}.notes" => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var array<string, mixed> $payload */
        $payload = data_get($validated, $key, []);

        return $payload;
    }

    /**
     * Ambil baris transaksi untuk satu item beserta cek stok (row lock).
     *
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function resolveLine(array $item): array
    {
        $sources = [
            'frame' => ['model' => Frame::class, 'label' => 'Frame'],
            'lens' => ['model' => Lens::class, 'label' => 'Lensa'],
            'accessory' => ['model' => Accessory::class, 'label' => 'Aksesoris'],
        ];

        $type = (string) $item['type'];
        $source = $sources[$type] ?? null;
        $qty = (int) $item['qty'];

        if (! $source) {
            throw ValidationException::withMessages(['items' => 'Jenis item tidak dikenal.']);
        }

        /** @var Frame|Lens|Accessory|null $model */
        $model = $source['model']::query()->lockForUpdate()->find($item['id']);

        if (! $model) {
            throw ValidationException::withMessages(['items' => "{$source['label']} tidak ditemukan."]);
        }

        if ($model->stock < $qty) {
            throw ValidationException::withMessages(['items' => "Stok {$source['label']} {$model->name} tersisa {$model->stock}."]);
        }

        $price = (float) $model->sell_price;

        return [
            'item_type' => $type,
            'frame_id' => $type === 'frame' ? $model->id : null,
            'lens_id' => $type === 'lens' ? $model->id : null,
            'accessory_id' => $type === 'accessory' ? $model->id : null,
            'name' => $model->name,
            'quantity' => $qty,
            'price' => $price,
            'discount' => 0,
            'subtotal' => $price * $qty,
        ];
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function decrementStock(array $line): void
    {
        [$model, $column] = match ($line['item_type']) {
            'frame' => [Frame::class, 'frame_id'],
            'lens' => [Lens::class, 'lens_id'],
            default => [Accessory::class, 'accessory_id'],
        };

        $model::whereKey($line[$column])->decrement('stock', $line['quantity']);
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
}
