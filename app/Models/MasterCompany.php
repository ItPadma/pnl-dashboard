<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCompany extends Model
{
    protected $table = 'master_companies';

    protected $fillable = [
        'code',
        'name',
    ];
}
