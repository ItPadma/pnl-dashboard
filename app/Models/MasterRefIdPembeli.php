<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRefIdPembeli extends Model
{
    protected $table = 'master_ref_id_pembeli';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
