<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterPkp extends Model
{
    protected $table = 'master_pkp';

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
