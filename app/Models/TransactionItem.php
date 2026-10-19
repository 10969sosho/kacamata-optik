<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $primaryKey = 'sales_no';

    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'sales_no',
        'sortno',
        'itemno',
        'item_type',
        'item_description',
        'username',
        'qty',
        'unit',
        'price',
        'disc_percent',
        'subtotal',
        'keterangan',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'sortno' => 'integer',
        'qty' => 'integer',
        'price' => 'decimal:2',
        'disc_percent' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'sales_no', 'sales_no');
    }

    /**
     * Produk asli (frame / lens / accessory) berdasarkan itemno + item_type.
     */
    public function product(): ?Model
    {
        $model = match ($this->item_type) {
            'frame' => Frame::class,
            'lens' => Lens::class,
            'accessory' => Accessory::class,
            default => null,
        };

        return $model ? $model::query()->find($this->itemno) : null;
    }

    public function gross(): float
    {
        return (float) $this->price * (int) $this->qty;
    }

    public function discountAmount(): float
    {
        return round($this->gross() * ((float) $this->disc_percent / 100), 2);
    }

    /**
     * Primary key gabungan (sales_no, sortno) — bukan auto increment.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('sales_no', $this->getAttribute('sales_no'))
            ->where('sortno', $this->getAttribute('sortno'));
    }
}
