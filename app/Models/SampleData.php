<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class SampleData
 * @package App\Models
 * @version December 30, 2016, 1:04 pm UTC
 */
class SampleData extends Model
{

    public $table = 'sample_datas';

    public $timestamps = false;

    public $fillable = [
        'idcode',
        'type',
        'group',
        'name',
        'gender',
        'nrc_id',
        'dob',
        'father',
        'mother',
        'ethnicity',
        'current_org',
        'mobile',
        'line_phone',
        'education',
        'email',
        'address',
        'village',
        'village_tract',
        'township',
        'district',
        'state',
        'parent_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'idcode' => 'string',
        'type' => 'string',
        'name' => 'string',
        'gender' => 'string',
        'nrc_id' => 'string',
        'father' => 'string',
        'mother' => 'string',
        'address' => 'string',
        'village' => 'string',
        'village_tract' => 'string',
        'township' => 'string',
        'district' => 'string',
        'state' => 'string',
        'parent_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function samples()
    {
        return $this->hasMany(Sample::class, 'sample_data_id');
    }

}
