<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    protected $fillable = [
        'user_id',
        'ip',
        'user_agent',
        'url',
        'method',
        'action',
        'action_type',
        'action_data',
        'affected_table'
    ];
}
