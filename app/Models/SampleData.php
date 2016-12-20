<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class SampleData
 * @package App\Models
 * @version December 20, 2016, 12:54 pm UTC
 */
class SampleData extends Model
{

    public $table = 'sample_datas';
    


    public $fillable = [
        'name',
        'type',
        'unique',
        'extras'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'type' => 'string',
        'unique' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
