<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NettInvoiceHistory extends Model
{
    protected $table = 'nett_invoice_histories';

    public $timestamps = false;

    protected $fillable = [
        'id_transaksi',
        'no_invoice_npkp',
        'no_invoice_retur',
        'nilai_invoice_npkp',
        'nilai_retur_used',
        'nilai_nett',
        'remaining_value',
        'status',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'nilai_invoice_npkp' => 'decimal:2',
        'nilai_retur_used' => 'decimal:2',
        'nilai_nett' => 'decimal:2',
        'remaining_value' => 'decimal:2',
        'created_at' => 'datetime',
    ];
}
