<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBrand extends Model
{
    protected $table = 'master_brands';

    protected $fillable = [
        'code',
        'name',
    ];
}
