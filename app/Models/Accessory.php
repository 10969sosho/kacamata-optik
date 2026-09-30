<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Item non-frame & non-lens (softlens, case / wadah, aksesoris lain).
 */
class Accessory extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'sku',
        'name',
        'brand',
        'category_id',
        'buy_price',
        'sell_price',
        'stock',
        'min_stock',
        'status',
        'description',
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

    /** @return Builder<$this> */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
