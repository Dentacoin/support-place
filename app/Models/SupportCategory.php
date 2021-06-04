<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Astrotomic\Translatable\Translatable;

class SupportCategory extends Model {
    
    use SoftDeletes;
    
    public $translatedAttributes = [
        'name',
    ];

    protected $fillable = [
        'name',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function questions() {
        return $this->hasMany('App\Models\SupportQuestion', 'category_id', 'id');
    }
}

class SupportCategoryTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'support_category_id',
        'name',
    ];
}

?>