<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class BulkSms
 * @package App\Models
 * @version March 11, 2019, 4:58 pm UTC
 *
 * @property string phone
 * @property string name
 * @property string message
 */
class BulkSms extends Model
{

    public $table = 'bulk_sms';

    public $primaryKey = 'phone';

    public $incrementing = false;


    public $fillable = [
        'phone',
        'name',
        'message',
        'status'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'phone' => 'string|unique',
        'name' => 'string',
        'message' => 'string',
        'status' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
