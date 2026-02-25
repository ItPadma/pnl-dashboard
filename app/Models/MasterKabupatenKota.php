<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKabupatenKota extends Model
{
    protected $connection = 'rdw_252';
    protected $table = 'master_kabupaten';

    public $timestamps = false;
    protected $guarded = ['*'];

    protected static function booted()
    {
        static::creating(function () {
            return false;
        });

        static::updating(function () {
            return false;
        });

        static::deleting(function () {
            return false;
        });
    }
}
