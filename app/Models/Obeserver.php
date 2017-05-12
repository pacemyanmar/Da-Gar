<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Obeserver
 * @package App\Models
 * @version May 12, 2017, 4:14 am UTC
 */
class Obeserver extends Model
{

    public $table = 'obeservers';
    


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
        'education'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
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
