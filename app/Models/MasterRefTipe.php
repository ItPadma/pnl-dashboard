<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterRefTipe extends Model
{
    protected $table = 'master_ref_tipe';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
