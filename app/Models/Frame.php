<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Frame extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'brand',
        'model',
        'color',
        'material',
        'size',
        'gender',
        'buy_price',
        'sell_price',
        'stock',
        'min_stock',
        'status',
        'photo',
        'description',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
    ];

    /** @return HasMany<TransactionItem, $this> */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
