<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Enumerator
 * @package App\Models
 * @version December 28, 2016, 1:05 pm UTC
 */
class Enumerator extends Model
{

    public $table = 'enumerators';
    


    public $fillable = [
        'idcode',
        'name',
        'gender',
        'nrc_id',
        'dob',
        'address'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'idcode' => 'string',
        'name' => 'string',
        'gender' => 'string',
        'nrc_id' => 'string',
        'address' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
