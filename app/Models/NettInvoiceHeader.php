<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NettInvoiceHeader extends Model
{
    protected $table = 'nett_invoice_headers';

    protected $primaryKey = 'no_invoice';
    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_transaksi',
        'pt',
        'principal',
        'depo',
        'no_invoice',
        'invoice_value_original',
        'invoice_value_nett',
        'mp_bulan',
        'mp_tahun',
        'is_checked',
        'is_downloaded',
        'status',
        'created_at',
    ];

    protected $casts = [
        'invoice_value_original' => 'decimal:2',
        'invoice_value_nett' => 'decimal:2',
        'is_checked' => 'boolean',
        'is_downloaded' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(NettInvoiceHeaderItem::class, 'no_invoice', 'no_invoice');
    }
}
