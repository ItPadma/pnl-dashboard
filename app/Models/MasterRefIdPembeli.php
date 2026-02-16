<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRefIdPembeli extends Model
{
    protected $table = 'master_ref_id_pembeli';

    protected $fillable = [
        'kode',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
