<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterDepo extends Model
{
    protected $table = 'master_depos';

    protected $fillable = [
        'code',
        'name',
    ];
}
