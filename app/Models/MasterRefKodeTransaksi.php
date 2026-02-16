<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRefKodeTransaksi extends Model
{
    protected $table = 'master_ref_kode_transaksi';

    protected $fillable = [
        'kode',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
