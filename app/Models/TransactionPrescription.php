<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionPrescription extends Model
{
    protected $table = 'transaction_prescriptions';

    protected $primaryKey = 'sales_no';

    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /**
     * Sisi lensa: R = kanan (OD), L = kiri (OS).
     *
     * @var list<string>
     */
    public const SIDES = ['R', 'L'];

    /**
     * Sumber resep: diperiksa di toko atau dibawa sendiri dari dokter luar.
     *
     * @var array<string, string>
     */
    public const SOURCES = [
        'in_store' => 'In Store',
        'bring_own' => 'Bawa Resep Sendiri',
    ];

    /** @var list<string> */
    protected $fillable = [
        'sales_no',
        'sortno',
        'itemno_lensa',
        'item_type',
        'qty',
        'sisi',
        'sph',
        'cyl',
        'axis',
        'add',
        'pd',
        'ro1',
        'ro2',
        'resep',
        'jenis_lensa',
        'source',
        'ket',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'sortno' => 'integer',
        'qty' => 'integer',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'sales_no', 'sales_no');
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[(string) $this->getAttribute('source')] ?? '—';
    }

    /**
     * Primary key gabungan (sales_no, sortno, sisi) — bukan auto increment.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query->where('sales_no', $this->getAttribute('sales_no'))
            ->where('sortno', $this->getAttribute('sortno'))
            ->where('sisi', $this->getAttribute('sisi'));
    }
}
