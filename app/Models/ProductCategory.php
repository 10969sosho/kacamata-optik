<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** @return HasMany<Lens, $this> */
    public function lenses(): HasMany
    {
        return $this->hasMany(Lens::class, 'category_id');
    }
}
