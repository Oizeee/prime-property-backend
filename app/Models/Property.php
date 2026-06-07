<?php

namespace App\Models;

use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use HasFactory, SoftDeletes;

    /** Allowed hadap (facing) directions. */
    public const HADAP_OPTIONS = ['Utara', 'Selatan', 'Timur', 'Barat'];

    /** Property type enum values. */
    public const TIPE_OPTIONS = ['Ruko', 'Villa'];

    /** Listing status enum values. */
    public const STATUS_OPTIONS = ['in stock', 'sold_out'];

    /** Readiness status enum values. */
    public const SIAP_OPTIONS = ['siap_huni', 'siap_kosong', 'siap_huni_renovasi'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama_property',
        'group',
        'lebar',
        'panjang',
        'hadap',
        'tipe',
        'tingkat',
        'price',
        'carport',
        'status',
        'siap',
        'maps_link',
        'kawasan',
        'unit',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hadap' => 'array',
            'kawasan' => 'array',
            'carport' => 'boolean',
            'lebar' => 'decimal:2',
            'panjang' => 'decimal:2',
            'tingkat' => 'decimal:1',
            'price' => 'integer',
        ];
    }

    /**
     * Audit log entries for this property.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
