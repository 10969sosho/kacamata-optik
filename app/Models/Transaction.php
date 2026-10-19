<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Transaction extends Model
{
    protected $primaryKey = 'sales_no';

    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /**
     * Status pesanan kacamata.
     *
     * @var list<string>
     */
    public const STATUSES = ['ordered', 'processing', 'ready', 'completed', 'cancelled', 'refunded'];

    /** @var list<string> */
    protected $fillable = [
        'sales_no',
        'sales_date',
        'sales_time',
        'customer_id',
        'input_by',
        'payment_by',
        'edc_id',
        'grandtotal',
        'sales_notes',
        'sales_status',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'sales_date' => 'date',
        'grandtotal' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return HasMany<TransactionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'sales_no', 'sales_no');
    }

    /** @return HasMany<TransactionPrescription, $this> */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(TransactionPrescription::class, 'sales_no', 'sales_no');
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'sales_no', 'sales_no');
    }

    protected function salesDateTime(): Attribute
    {
        return Attribute::get(fn (): string => ($this->sales_date?->format('d/m/Y') ?? '-').' '.substr((string) $this->sales_time, 0, 5));
    }

    /**
     * Item transaksi dikelompokkan per pemakai (username), termasuk kelompok
     * "Umum" untuk item yang tidak terikat pemakai tertentu.
     *
     * @return Collection<int, array{label: string, items: Collection<int, TransactionItem>, prescriptions: Collection<int, TransactionPrescription>, ro1: ?string, ro2: ?string}>
     */
    public function wearerGroups(): Collection
    {
        $rxBySortno = $this->prescriptions->groupBy(fn (TransactionPrescription $rx) => (string) $rx->sortno);

        $groups = $this->items
            ->groupBy(fn (TransactionItem $item) => (string) $item->username)
            ->map(function (Collection $items, string $label) use ($rxBySortno): array {
                $sortnos = $items->pluck('sortno')->map(fn ($sortno) => (string) $sortno);
                $prescriptions = $rxBySortno
                    ->filter(fn ($rows, string $sortno) => $sortnos->contains($sortno))
                    ->flatten(1)
                    ->values();

                return [
                    'label' => $label === '' ? 'Umum' : $label,
                    'items' => $items->values(),
                    'prescriptions' => $prescriptions,
                    'ro1' => $prescriptions->first()?->ro1,
                    'ro2' => $prescriptions->first()?->ro2,
                ];
            })
            ->values();

        if ($groups->isEmpty()) {
            $groups->push([
                'label' => $this->customer?->name ?? 'Umum',
                'items' => collect(),
                'prescriptions' => collect(),
                'ro1' => null,
                'ro2' => null,
            ]);
        }

        return $groups;
    }

    public function isPaid(): bool
    {
        return $this->balanceDue() <= 0;
    }

    public function amountPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->grandtotal - $this->amountPaid());
    }
}
