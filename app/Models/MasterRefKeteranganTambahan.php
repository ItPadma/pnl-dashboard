<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRefKeteranganTambahan extends Model
{
    protected $table = 'master_ref_keterangan_tambahan';

    protected $fillable = [
        'kode',
        'kode_transaksi_id',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function kodeTransaksi()
    {
        return $this->belongsTo(MasterRefKodeTransaksi::class, 'kode_transaksi_id', 'kode');
    }
}
