<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Astrotomic\Translatable\Translatable;

class SupportQuestion extends Model {
    
    use SoftDeletes, Translatable;
    
    public $translatedAttributes = [
        'question',
        'slug',
        'content',
    ];

    protected $fillable = [
        'category_id',
    	'is_main',
        'order_number'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function category() {
        return $this->hasOne('App\Models\UserCategory', 'id', 'category_id')->withTrashed();
    }
}

class SupportQuestionTranslation extends Model {

    public $timestamps = false;
    protected $fillable = [
        'support_question_id',
        'question',
        'slug',
        'content',
    ];

}



?>