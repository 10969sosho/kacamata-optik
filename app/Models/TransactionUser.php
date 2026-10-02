<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Nama pemakai (user) di dalam satu transaksi.
 *
 * Satu transaksi dengan satu customer / member id bisa memuat beberapa pemakai
 * (mis. Ayah, Anak 1, Anak 2), masing-masing dengan item & resepnya sendiri.
 */
class TransactionUser extends Model
{
    /** @var array<string, string> */
    protected $attributes = [
        'status' => 'ordered',
    ];

    /**
     * Status pengerjaan pesanan, berlaku per pemakai (bukan per transaksi).
     *
     * @var list<string>
     */
    public const STATUSES = ['ordered', 'processing', 'ready', 'completed', 'cancelled'];

    /** @var array<string, string> */
    public const STATUS_LABELS = [
        'ordered' => 'Ordered (Dipesan)',
        'processing' => 'Processing (Diproses)',
        'ready' => 'Ready (Siap Diambil)',
        'completed' => 'Completed (Selesai)',
        'cancelled' => 'Cancelled (Dibatalkan)',
    ];

    /** @var list<string> */
    protected $fillable = [
        'transaction_id',
        'name',
        'prescription_id',
        'status',
        'ro1',
        'ro2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /** @return HasMany<TransactionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst($this->status);
    }
}
