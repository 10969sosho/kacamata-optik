<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'transaction_id',
        'amount',
        'payment_method',
        'reference_number',
        'paid_at',
        'note',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
