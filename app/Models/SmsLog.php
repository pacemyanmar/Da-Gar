<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class SmsLog
 * @package App\Models
 * @version December 9, 2016, 5:27 pm UTC
 */
class SmsLog extends Model
{

    public $table = 'sms_logs';

    public $incrementing = false;

    public $fillable = [
        'id',
        'service_id',
        'from_number',
        'to_number',
        'name',
        'content',
        'error_message',
        'search_result',
        'phone',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'service_id' => 'string',
        'from_number' => 'string',
        'to_number' => 'string',
        'name' => 'string',
        'content' => 'string',
        'error_message' => 'string',
        'search_result' => 'string',
        'phone' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

}
