<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Project
 * @package App\Models
 * @version November 2, 2016, 7:49 am UTC
 */
class Project extends Model
{

    public $table = 'projects';
    


    public $fillable = [
        'name',
        'type'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'type' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
