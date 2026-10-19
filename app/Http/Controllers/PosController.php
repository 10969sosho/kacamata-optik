<?php

namespace App\Http\Controllers;

use App\Models\Accessory;
use App\Models\Customer;
use App\Models\Frame;
use App\Models\Lens;
use App\Models\Payment;
use App\Models\Promotion;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\TransactionPrescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
                'rx' => $this->savedPrescriptions(),
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
            'users.*.ro1' => ['nullable', 'string', 'max:120'],
            'users.*.ro2' => ['nullable', 'string', 'max:120'],
            'users.*.rx_ref' => ['nullable', 'string', 'max:60'],
            'users.*.new_prescription' => ['nullable', 'boolean'],
            'items' => ['nullable', 'array', 'min:1'],
            'items.*.type' => ['required', 'in:frame,lens,accessory'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'ro1' => ['nullable', 'string', 'max:120'],
            'ro2' => ['nullable', 'string', 'max:120'],
            'rx_ref' => ['nullable', 'string', 'max:60'],
            'new_prescription' => ['nullable', 'boolean'],
            'promo_id' => ['nullable', 'exists:promotions,id'],
            'payment_method' => ['required', 'in:cash,transfer,qris,card'],
            'payment_status' => ['required', 'in:paid,down_payment'],
            'dp_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:ordered,processing,ready,completed'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['payment_status'] === 'down_payment' && ! ($request->filled('dp_amount') && (float) $request->input('dp_amount') > 0)) {
            throw ValidationException::withMessages(['dp_amount' => 'Nominal DP wajib diisi lebih dari 0.']);
        }

        $groups = $this->resolveGroups($request, $data);
        $prescriptions = $this->validatePrescriptions($request, $groups);

        $transaction = DB::transaction(function () use ($request, $data, $groups, $prescriptions) {
            $sortno = 0;
            $gross = 0.0;
            /** @var array<int, array<int, array<string, mixed>>> $linesByGroup */
            $linesByGroup = [];

            foreach ($groups as $index => $group) {
                foreach ($group['items'] as $item) {
                    $line = $this->resolveLine($item);
                    $line['sortno'] = ++$sortno;
                    $line['username'] = $group['name'];
                    $linesByGroup[$index][] = $line;
                    $gross += $line['price'] * $line['qty'];
                }
            }

            [$promo, $discount] = $this->applyPromotion($data['promo_id'] ?? null, $gross);
            $discPercent = $gross > 0 ? round($discount / $gross * 100, 2) : 0.0;

            $subtotal = 0.0;
            foreach ($linesByGroup as $index => $lines) {
                foreach ($lines as $position => $line) {
                    $line['disc_percent'] = $discPercent;
                    $line['subtotal'] = round($line['price'] * $line['qty'] * (1 - $discPercent / 100), 2);
                    $linesByGroup[$index][$position] = $line;
                    $subtotal += $line['subtotal'];
                }
            }

            $transaction = Transaction::create([
                'sales_no' => $this->nextSalesNo(),
                'sales_date' => now()->toDateString(),
                'sales_time' => now()->format('H:i:s'),
                'customer_id' => $data['customer_id'],
                'input_by' => $request->user()->name,
                'payment_by' => $data['payment_method'],
                'edc_id' => null,
                'grandtotal' => round($subtotal, 2),
                'sales_notes' => $data['notes'] ?? null,
                'sales_status' => $data['status'],
            ]);

            foreach ($linesByGroup as $lines) {
                foreach ($lines as $line) {
                    TransactionItem::create($line + [
                        'sales_no' => $transaction->sales_no,
                        'unit' => 'pcs',
                        'keterangan' => null,
                    ]);

                    $this->decrementStock($line);
                }
            }

            foreach ($groups as $index => $group) {
                if (isset($prescriptions[$index])) {
                    $this->savePrescriptions($transaction, $linesByGroup[$index], $group, $prescriptions[$index]);
                }
            }

            Payment::create([
                'sales_no' => $transaction->sales_no,
                'amount' => $data['payment_status'] === 'paid' ? (float) $transaction->grandtotal : (float) $data['dp_amount'],
                'payment_method' => $data['payment_method'],
                'paid_at' => now(),
                'note' => $data['payment_status'] === 'paid' ? 'Pelunasan penuh.' : 'Uang muka (DP).',
            ]);

            return $transaction;
        });

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Transaksi {$transaction->sales_no} berhasil dibuat.");
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
     * Daftar resep lama (dari transaksi sebelumnya) untuk dipakai ulang di POS,
     * lengkap dengan detail R/L sehingga bisa direview sebelum dipakai.
     *
     * @return Collection<int, array{ref: string, customer_id: int|string, doctor: ?string, type: ?string, source: ?string, date: ?string, od: array<string, mixed>, os: array<string, mixed>}>
     */
    private function savedPrescriptions(): Collection
    {
        return DB::table('transaction_prescriptions')
            ->join('transactions', 'transactions.sales_no', '=', 'transaction_prescriptions.sales_no')
            ->orderByDesc('transactions.sales_date')
            ->get([
                'transaction_prescriptions.sales_no as sales_no',
                'transaction_prescriptions.sortno as sortno',
                'transaction_prescriptions.sisi as sisi',
                'transaction_prescriptions.resep as resep',
                'transaction_prescriptions.jenis_lensa as jenis_lensa',
                'transaction_prescriptions.source as source',
                'transaction_prescriptions.itemno_lensa as itemno_lensa',
                'transaction_prescriptions.qty as qty',
                'transaction_prescriptions.sph as sph',
                'transaction_prescriptions.cyl as cyl',
                'transaction_prescriptions.axis as axis',
                'transaction_prescriptions.add as add',
                'transaction_prescriptions.pd as pd',
                'transactions.customer_id as customer_id',
                'transactions.sales_date as sales_date',
            ])
            ->groupBy(fn (object $row) => $row->sales_no.':'.$row->sortno)
            ->map(function (Collection $rows) {
                $first = $rows->first();

                return [
                    'ref' => $first->sales_no.':'.$first->sortno,
                    'customer_id' => $first->customer_id,
                    'doctor' => $first->resep,
                    'type' => $first->jenis_lensa,
                    'source' => $first->source,
                    'date' => $first->sales_date ? (string) substr((string) $first->sales_date, 0, 10) : null,
                    'od' => $this->rxSide($rows, TransactionPrescription::SIDES[0]),
                    'os' => $this->rxSide($rows, TransactionPrescription::SIDES[1]),
                ];
            })
            ->values();
    }

    /**
     * Detail satu sisi resep tersimpan.
     *
     * @param  Collection<int, object>  $rows
     * @return array{lens_id: ?string, qty: int, sph: ?string, cyl: ?string, axis: ?string, add: ?string, pd: ?string}
     */
    private function rxSide(Collection $rows, string $sisi): array
    {
        $row = $rows->firstWhere('sisi', $sisi);

        return [
            'lens_id' => $row?->itemno_lensa,
            'qty' => (int) ($row->qty ?? 1),
            'sph' => $row?->sph,
            'cyl' => $row?->cyl,
            'axis' => $row?->axis,
            'add' => $row?->{'add'},
            'pd' => $row?->pd,
        ];
    }

    /**
     * Satu transaksi = satu customer/member, berisi satu atau beberapa pemakai
     * (mis. Ayah, Anak 1, Anak 2). Format lama `items[]` diperlakukan sebagai
     * satu pemakai bernama customer.
     *
     * @param  array<string, mixed>  $data
     * @return array<int, array{name: string, items: array<int, array<string, mixed>>, rx_ref: ?string, new_prescription: bool, prescription: array<string, mixed>, ro1: ?string, ro2: ?string}>
     */
    private function resolveGroups(Request $request, array $data): array
    {
        if (isset($data['users'])) {
            $groups = [];

            foreach ($data['users'] as $index => $user) {
                $groups[$index] = [
                    'name' => trim($user['name']),
                    'items' => $user['items'],
                    'rx_ref' => ! empty($user['rx_ref']) ? trim((string) $user['rx_ref']) : null,
                    'new_prescription' => $request->boolean("users.{$index}.new_prescription"),
                    'prescription' => $request->input("users.{$index}.prescription") ?? [],
                    'ro1' => $this->personName($user['ro1'] ?? null),
                    'ro2' => $this->personName($user['ro2'] ?? null),
                ];
            }

            return $groups;
        }

        $customer = Customer::query()->findOrFail($data['customer_id']);

        return [[
            'name' => $customer->name,
            'items' => $data['items'],
            'rx_ref' => ! empty($data['rx_ref']) ? trim((string) $data['rx_ref']) : null,
            'new_prescription' => (bool) ($data['new_prescription'] ?? false),
            'prescription' => $request->input('prescription') ?? [],
            'ro1' => $this->personName($request->input('ro1')),
            'ro2' => $this->personName($request->input('ro2')),
        ]];
    }

    /**
     * Nama petugas (RO1 / RO2) dikirim sebagai teks bebas, dikosongkan bila kosong.
     */
    private function personName(mixed $value): ?string
    {
        $name = trim((string) $value);

        return $name === '' ? null : $name;
    }

    /**
     * Validasi resep tiap pemakai: pemakai yang memilih lensa wajib punya resep
     * (resep tersimpan dari transaksi lama atau input baru).
     *
     * @param  array<int, array{name: string, items: array<int, array<string, mixed>>, rx_ref: ?string, new_prescription: bool, prescription: array<string, mixed>, ro1: ?string, ro2: ?string}>  $groups
     * @return array<int, array<string, mixed>>
     */
    private function validatePrescriptions(Request $request, array $groups): array
    {
        $isNewFormat = $request->has('users');
        $messages = [];
        $payloads = [];

        foreach ($groups as $index => $group) {
            $hasLens = collect($group['items'])->contains('type', 'lens');
            $useNew = (bool) $group['new_prescription'] || ! empty($group['prescription']);
            $key = $isNewFormat ? "users.{$index}.prescription" : 'prescription';

            if ($isNewFormat && $hasLens && ! $group['rx_ref'] && ! $useNew) {
                $messages["users.{$index}.rx_ref"] = "Resep untuk pemakai \"{$group['name']}\" wajib diisi karena memilih lensa.";

                continue;
            }

            if ($useNew) {
                $payloads[$index] = $this->validateInlinePrescription($request, $key);
            } elseif ($group['rx_ref']) {
                $payloads[$index] = $this->resolveSavedPrescription($group['rx_ref'], $index, $isNewFormat, $messages);
            }
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }

        return $payloads;
    }

    /**
     * Salin nilai resep lama (R/L) berdasreferensi "sales_no:sortno".
     *
     * @param  array<int, string>  $messages
     * @return array<string, mixed>
     */
    private function resolveSavedPrescription(string $ref, int $index, bool $isNewFormat, array &$messages): array
    {
        [$salesNo, $sortno] = array_pad(explode(':', $ref, 2), 2, null);

        $rows = TransactionPrescription::query()
            ->where('sales_no', (string) $salesNo)
            ->where('sortno', (int) $sortno)
            ->get();

        if ($rows->isEmpty()) {
            $key = $isNewFormat ? "users.{$index}.rx_ref" : 'rx_ref';
            $messages[$key] = 'Resep tersimpan tidak ditemukan. Pilih "Input resep baru".';

            return [];
        }

        $right = $rows->firstWhere('sisi', TransactionPrescription::SIDES[0]);
        $left = $rows->firstWhere('sisi', TransactionPrescription::SIDES[1]);
        $first = $rows->first();

        return [
            'doctor_or_optician' => $first->resep,
            'prescription_type' => $first->jenis_lensa,
            'source' => $first->source,
            'notes' => $first->ket,
            'od_lens_id' => $right?->itemno_lensa,
            'od_qty' => $right?->qty,
            'os_lens_id' => $left?->itemno_lensa,
            'os_qty' => $left?->qty,
            'od_sph' => $right?->sph, 'od_cyl' => $right?->cyl, 'od_axis' => $right?->axis,
            'od_add' => $right?->{'add'}, 'od_pd' => $right?->pd,
            'os_sph' => $left?->sph, 'os_cyl' => $left?->cyl, 'os_axis' => $left?->axis,
            'os_add' => $left?->{'add'}, 'os_pd' => $left?->pd,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateInlinePrescription(Request $request, string $key): array
    {
        $validated = $request->validate([
            "{$key}.doctor_or_optician" => ['required', 'string', 'max:120'],
            "{$key}.prescription_type" => ['nullable', 'string', 'max:50'],
            "{$key}.source" => ['nullable', 'in:'.implode(',', array_keys(TransactionPrescription::SOURCES))],
            "{$key}.od_lens_id" => ['nullable', 'integer'],
            "{$key}.od_qty" => ['nullable', 'integer', 'min:1'],
            "{$key}.os_lens_id" => ['nullable', 'integer'],
            "{$key}.os_qty" => ['nullable', 'integer', 'min:1'],
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
            "{$key}.notes" => ['nullable', 'string', 'max:1000'],
        ]);

        /** @var array<string, mixed> $payload */
        $payload = data_get($validated, $key, []);

        return $payload;
    }

    /**
     * Simpan resep R & L untuk satu kelompok pemakai. Tiap sisi menempel pada
     * baris lensa milik sisi tersebut (item lensa & qty R/L bisa berbeda);
     * bila tidak ada, jatuh ke baris lensa pertama / item pertama kelompok.
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @param  array{name: string, items: array<int, array<string, mixed>>, rx_ref: ?string, new_prescription: bool, prescription: array<string, mixed>, ro1: ?string, ro2: ?string}  $group
     * @param  array<string, mixed>  $values
     */
    private function savePrescriptions(Transaction $transaction, array $lines, array $group, array $values): void
    {
        $lensLines = collect($lines)->where('item_type', 'lens')->keyBy('itemno');
        $fallback = $lensLines->first() ?? ($lines[0] ?? null);

        if (! $fallback) {
            return;
        }

        foreach (TransactionPrescription::SIDES as $index => $side) {
            $eye = $index === 0 ? 'od' : 'os';
            $line = $lensLines->get((string) ($values["{$eye}_lens_id"] ?? '')) ?? $fallback;

            TransactionPrescription::create([
                'sales_no' => $transaction->sales_no,
                'sortno' => $line['sortno'],
                'itemno_lensa' => $line['item_type'] === 'lens' ? $line['itemno'] : null,
                'item_type' => $line['item_type'],
                'qty' => max(1, (int) ($values["{$eye}_qty"] ?? 1)),
                'sisi' => $side,
                'sph' => $this->blankToNull($values["{$eye}_sph"] ?? null),
                'cyl' => $this->blankToNull($values["{$eye}_cyl"] ?? null),
                'axis' => $this->blankToNull($values["{$eye}_axis"] ?? null),
                'add' => $this->blankToNull($values["{$eye}_add"] ?? null),
                'pd' => $this->blankToNull($values["{$eye}_pd"] ?? null),
                'ro1' => $group['ro1'],
                'ro2' => $group['ro2'],
                'resep' => $this->blankToNull($values['doctor_or_optician'] ?? null),
                'jenis_lensa' => $this->blankToNull($values['prescription_type'] ?? null),
                'source' => $this->blankToNull($values['source'] ?? null),
                'ket' => $this->blankToNull($values['notes'] ?? null),
            ]);
        }
    }

    private function blankToNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
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

        return [
            'itemno' => (string) $model->id,
            'item_type' => $type,
            'item_description' => $model->name,
            'price' => (float) $model->sell_price,
            'qty' => $qty,
        ];
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function decrementStock(array $line): void
    {
        $model = match ($line['item_type']) {
            'frame' => Frame::class,
            'lens' => Lens::class,
            default => Accessory::class,
        };

        $model::whereKey($line['itemno'])->decrement('stock', $line['qty']);
    }

    /**
     * @return array{0: ?Promotion, 1: float}
     */
    private function applyPromotion(?int $promoId, float $gross): array
    {
        if (! $promoId) {
            return [null, 0];
        }

        $promo = Promotion::active()->find($promoId);

        if (! $promo || $gross < (float) $promo->min_spend) {
            return [null, 0];
        }

        $discount = $promo->promo_type === 'nominal'
            ? (float) $promo->discount_value
            : $gross * ((float) $promo->discount_value / 100);

        return [$promo, min($gross, round($discount))];
    }

    private function nextSalesNo(): string
    {
        $prefix = 'TRX-'.now()->format('Ymd').'-';

        $last = Transaction::query()
            ->where('sales_no', 'like', $prefix.'%')
            ->orderByDesc('sales_no')
            ->value('sales_no');

        $seq = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }
}
