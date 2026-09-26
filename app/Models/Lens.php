<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lens extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'sku',
        'brand',
        'name',
        'category_id',
        'lens_type',
        'material',
        'index_val',
        'coating',
        'buy_price',
        'sell_price',
        'stock',
        'min_stock',
        'supplier',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

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
