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
        'given_name',
        'family_name',
        'full_name',
        'observer_field',
        'code',
        'email',
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
        'mobile_provider',
        'sms_primary',
        'sms_backup',
        'call_primary',
        'call_backup',
        'hotline1',
        'hotline2',
        'form_type',
        'full_name_trans',
        'phone_1_trans',
        'phone_2_trans',
        'language_trans',
        'ethincity_trans',
        'occupation_trans',

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
        'code' => 'string', // code must be unique across database
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
        'education' => 'string',
        'observer_field' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    public function location()
    {
        return $this->belongsTo(SampleData::class, 'sample_id');
    }

    
}
