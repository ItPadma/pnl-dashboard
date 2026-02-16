<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRefSatuanUkur extends Model
{
    protected $table = 'master_ref_satuan_ukur';

    protected $fillable = [
        'kode',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
