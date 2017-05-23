<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class LogicalCheck
 * @package App\Models
 * @version May 22, 2017, 3:09 pm UTC
 */
class LogicalCheck extends Model
{

    public $table = 'logical_checks';

    public  $incrementing = false;


    public $fillable = [
        'id',
        'leftval',
        'rightval',
        'operator',
        'scope'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'leftval' => 'string',
        'rightval' => 'string',
        'operator' => 'string',
        'scope' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
