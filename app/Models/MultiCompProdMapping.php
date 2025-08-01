<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MultiCompProdMapping extends Model
{
    protected $table = 'PADMA_GL_MultiCompProdMapping';

    protected $fillable = [];

    public function company()
    {
        return $this->belongsTo(MasterCompany::class, 'szCompanyID', 'code');
    }

    public function brand()
    {
        return $this->belongsTo(MasterBrand::class, 'szProductCategoryID', 'code');
    }
}
