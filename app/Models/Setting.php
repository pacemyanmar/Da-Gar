<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Setting
 * @package App\Models
 * @version December 10, 2016, 6:50 am UTC
 */
class Setting extends Model
{

    public $table = 'settings';

    public $primaryKey = 'key';

    public $incrementing = false;

    public $timestamps = false;

    public $fillable = [
        'key',
        'value',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'key' => 'string',
        'value' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'key' => 'required',
        'value' => 'required',
    ];

}
