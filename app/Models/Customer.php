<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'member_id',
        'name',
        'phone',
        'email',
        'birth_date',
        'gender',
        'address',
        'registered_at',
        'status',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'birth_date' => 'date',
        'registered_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Prescription, $this> */
    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    /** @return HasMany<Transaction, $this> */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public static function generateMemberId(): string
    {
        $n = (int) self::max('id') + 1;

        do {
            $code = 'KCM-'.str_pad((string) $n, 6, '0', STR_PAD_LEFT);
            $n++;
        } while (self::where('member_id', $code)->exists());

        return $code;
    }

    public function latestPrescription(): ?Prescription
    {
        return $this->prescriptions()->latest('examination_date')->first();
    }

    public function totalSpend(): float
    {
        return (float) $this->billableTransactions()->sum('total_amount');
    }

    public function totalOrders(): int
    {
        return $this->billableTransactions()->count();
    }

    /**
     * Cancelled and refunded orders must not count toward member spending.
     *
     * @return HasMany<Transaction, $this>
     */
    protected function billableTransactions(): HasMany
    {
        return $this->transactions()->whereNotIn('status', ['cancelled', 'refunded']);
    }
}
