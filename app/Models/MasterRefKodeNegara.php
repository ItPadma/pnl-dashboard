<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRefKodeNegara extends Model
{
    protected $table = 'master_ref_kode_negara';

    protected $fillable = [
        'kode',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
