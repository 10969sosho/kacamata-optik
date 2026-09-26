<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'description',
        'banner',
        'promo_type',
        'discount_value',
        'min_spend',
        'start_date',
        'end_date',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_spend' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /** @return HasMany<Transaction, $this> */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'promo_id');
    }

    /** @return Builder<$this> */
    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('start_date')->orWhere('start_date', '<=', $today))
            ->where(fn (Builder $q) => $q->whereNull('end_date')->orWhere('end_date', '>=', $today));
    }
}
