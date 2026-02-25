<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterProvinsi extends Model
{
    protected $connection = 'rdw_252';
    protected $table = 'master_provinsi';

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
