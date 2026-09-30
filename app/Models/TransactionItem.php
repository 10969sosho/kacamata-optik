<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'transaction_id',
        'transaction_user_id',
        'item_type',
        'frame_id',
        'lens_id',
        'accessory_id',
        'name',
        'quantity',
        'price',
        'discount',
        'subtotal',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Pemakai (user) yang memakai item ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(TransactionUser::class, 'transaction_user_id');
    }

    public function frame(): BelongsTo
    {
        return $this->belongsTo(Frame::class);
    }

    public function lens(): BelongsTo
    {
        return $this->belongsTo(Lens::class);
    }

    public function accessory(): BelongsTo
    {
        return $this->belongsTo(Accessory::class);
    }
}
