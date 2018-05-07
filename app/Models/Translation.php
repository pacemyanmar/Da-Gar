<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Translation
 * @package App\Models
 * @version May 5, 2018, 7:05 am UTC
 *
 * @property string group
 * @property string key
 * @property string text
 * @property string|\Carbon\Carbon created_at
 * @property string|\Carbon\Carbon updated_at
 */
class Translation extends Model
{

    public $table = 'language_lines';
    
    public $timestamps = false;



    public $fillable = [
        'group',
        'key',
        'text',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'group' => 'string',
        'key' => 'string',
        'text' => 'array'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];
    
}
