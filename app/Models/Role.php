<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Role
 * @package App\Models
 * @version January 13, 2017, 2:06 pm UTC
 */
class Role extends Model
{

    public $table = 'roles';
    


    public $fillable = [
        'level',
        'group',
        'role_name',
        'description',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'level' => 'integer',
        'group' => 'string',
        'role_name' => 'string',
        'description' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
