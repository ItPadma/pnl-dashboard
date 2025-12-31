<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NettInvoiceHeaderItem extends Model
{
    protected $table = 'nett_invoice_header_items';

    protected $primaryKey = 'no_invoice';
    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_transaksi',
        'no_invoice',
        'kode_barang',
        'satuan',
        'qty',
        'harga_satuan',
        'harga_total',
        'created_at',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'harga_total' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function header(): BelongsTo
    {
        return $this->belongsTo(NettInvoiceHeader::class, 'no_invoice', 'no_invoice');
    }
}
