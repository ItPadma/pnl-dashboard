<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NettInvoiceDetail extends Model
{
    protected $table = 'nett_invoice_details';

    protected $primaryKey = 'no_invoice_retur';
    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_transaksi',
        'pt',
        'principal',
        'depo',
        'no_invoice_retur',
        'invoice_retur_value',
        'mp_bulan',
        'mp_tahun',
        'is_checked',
        'is_downloaded',
        'status',
        'created_at',
    ];

    protected $casts = [
        'invoice_retur_value' => 'decimal:2',
        'is_checked' => 'boolean',
        'is_downloaded' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(NettInvoiceDetailItem::class, 'no_invoice_retur', 'no_invoice_retur');
    }
}
