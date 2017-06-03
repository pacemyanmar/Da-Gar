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
        'location_code',
        'ps_code',

        'type',
        'dbgroup',
        'sample',
        'area_type',

        'level1', // state
        'level1_id',
        'level2',
        'level3',
        'level4',
        'level5',
        'level6',
        'parent_id',

        'level1_trans', // state
        'level2_trans',
        'level3_trans',
        'level4_trans',
        'level5_trans',
        'level6_trans',

        'parties',
        'observer_field',
        'supervisor_field',
        'supervisor_name',
        'supervisor_name_trans',
        'supervisor_mobile',
        'supervisor_dob',
        'supervisor_gender',
        'supervisor_mail1',
        'supervisor_mail2',
        'supervisor_address',

        'sms_primary',
        'sms_backup',
        'call_primary',
        'call_backup',
        'hotline1',
        'hotline2',
        'sms_time',
        'incident_center',
        'registered_voters',
        'obs_type',
        'sbo',
        'pvt1',
        'pvt2',
        'pvt3',
        'pvt4'
    ];

    public static $export = [
        'location_code' => 'location_code',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'location_code' => 'string',
        'ps_code' => 'string',

        'type' => 'string',
        'dbgroup' => 'string',
        'sample' => 'string',
        'area_type' => 'string',

        'level1' => 'string', // state
        'level2' => 'string',
        'level3' => 'string',
        'level4' => 'string',
        'level5' => 'string',
        'level6' => 'string',
        'parent_id' => 'integer',

        'level1_trans' => 'string', // state
        'level2_trans' => 'string',
        'level3_trans' => 'string',
        'level4_trans' => 'string',
        'level5_trans' => 'string',
        'level6_trans' => 'string',

        'parties' => 'string',
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

    public function observers()
    {
        return $this->hasMany(Observer::class, 'sample_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Projects::class, 'samples', 'sample_data_id', 'project_id');
    }

    public function getStateAttribute($value)
    {
        return $this->getTranslation('level1', $value);
    }

    public function getDistrictAttribute($value)
    {
        return $this->getTranslation('level2', $value);
    }

    public function getTownshipAttribute($value)
    {
        return $this->getTranslation('level3', $value);
    }

    public function getVillageTractAttribute($value)
    {
        return $this->getTranslation('level4', $value);
    }

    public function getWardAttribute($value)
    {
        return $this->getTranslation('level4', $value);
    }

    public function getVillageAttribute($value)
    {
        return $this->getTranslation('level5', $value);
    }

    public function getLevel1Attribute($value)
    {
        return $this->getTranslation('level1', $value);
    }

    public function getLevel2Attribute($value)
    {
        return $this->getTranslation('level2', $value);
    }

    public function getLevel3Attribute($value)
    {
        return $this->getTranslation('level3', $value);
    }

    public function getLevel4Attribute($value)
    {
        return $this->getTranslation('level4', $value);
    }

    public function getLevel5Attribute($value)
    {
        return $this->getTranslation('level5', $value);
    }



    private function getTranslation($column, $value)
    {
        if (\App::isLocale('en')) {
            return $value;
        } else {
            return ($this->attributes[$column.'_trans'])? $this->attributes[$column.'_trans']:$value;
        }
    }

}
