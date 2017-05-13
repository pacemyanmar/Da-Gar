<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Observer
 * @package App\Models
 * @version May 13, 2017, 7:07 am UTC
 */
class Observer extends Model
{

    public $table = 'observers';
    


    public $fillable = [
        'name',
        'code',
        'sample_id',
        'national_id',
        'phone_1',
        'phone_2',
        'address',
        'language',
        'ethnicity',
        'occupation',
        'gender',
        'dob',
        'education',
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
        'name' => 'string',
        'code' => 'string',
        'sample_id' => 'integer',
        'national_id' => 'string',
        'phone_1' => 'string',
        'phone_2' => 'string',
        'address' => 'string',
        'language' => 'string',
        'ethnicity' => 'string',
        'occupation' => 'string',
        'gender' => 'string',
        'dob' => 'date',
        'education' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
