<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NettInvoiceDetailItem extends Model
{
    protected $table = 'nett_invoice_detail_items';

    protected $primaryKey = 'no_invoice_retur';
    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_transaksi',
        'no_invoice_retur',
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

    public function detail(): BelongsTo
    {
        return $this->belongsTo(NettInvoiceDetail::class, 'no_invoice_retur', 'no_invoice_retur');
    }
}
