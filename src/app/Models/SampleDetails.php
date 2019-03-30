<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class SampleDetails
 * @package App\Models
 * @version September 6, 2018, 10:00 am UTC
 *
 * @property string project_id
 */
class SampleDetails extends Model
{

    public $incrementing = false;

    public $timestamps = false;

    public $table = 'sample_details';

    public $guarded = [
        'id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    
}
