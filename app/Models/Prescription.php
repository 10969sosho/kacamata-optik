<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    /**
     * Sumber resep: diperiksa di toko (in_store) atau dibawa dari luar (bring_own).
     *
     * @var array<string, string>
     */
    public const SOURCES = [
        'in_store' => 'In Store',
        'bring_own' => 'Bawa Resep Sendiri',
    ];

    /** @var array<string, string> */
    protected $attributes = [
        'prescription_type' => 'Distance',
        'source' => 'in_store',
    ];

    /** @var list<string> */
    protected $fillable = [
        'customer_id',
        'doctor_or_optician',
        'examination_date',
        'prescription_type',
        'source',
        'od_sph',
        'od_cyl',
        'od_axis',
        'od_add',
        'od_pd',
        'os_sph',
        'os_cyl',
        'os_axis',
        'os_add',
        'os_pd',
        'pd_total',
        'fitting_height',
        'notes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'examination_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? (string) $this->source;
    }
}
