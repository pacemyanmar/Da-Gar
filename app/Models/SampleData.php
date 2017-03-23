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
        'spotchecker_code',
        'type',
        'dbgroup',
        'sample',
        'area_type',
        'code',
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
        'language',

        'code2',
        'name2',
        'gender2',
        'nrc_id2',
        'dob2',
        'father2',
        'mother2',
        'ethnicity2',
        'current_org2',
        'mobile2',
        'line_phone2',
        'education2',
        'email2',
        'address2',
        'language2',

        'village',
        'ward',
        'village_tract',
        'township',
        'district',
        'state',
        'parent_id',
        'created_at',
        'updated_at',
        'name_trans',
        'gender_trans',
        'nrc_id_trans',
        'father_trans',
        'mother_trans',
        'address_trans',
        'village_trans',
        'ward_trans',
        'village_tract_trans',
        'township_trans',
        'district_trans',
        'state_trans',
        'education_trans',
        'ethnicity_trans',
        'language_trans',
        'bank_information_trans',
        'mobile_provider_trans',
        'parties',
    ];

    public static $export = [
        'idcode' => 'idcode',
        'spotchecker_code' => 'spotchecker_code',
        'type' => 'type',
        'sample' => 'sample',
        'area_type' => 'area_type',
        'name' => 'name',
        'gender' => 'gender',
        'nrc_id' => 'nrc_id',
        'dob' => 'dob',
        'father' => 'father',
        'mother' => 'mother',
        'ethnicity' => 'ethnicity',
        'current_org' => 'current_org',
        'mobile' => 'mobile',
        'line_phone' => 'phone',
        'education' => 'education',
        'email' => 'email',
        'address' => 'address',
        'village' => 'village',
        'ward' => 'ward',
        'village_tract' => 'village_tract',
        'township' => 'township',
        'district' => 'district',
        'state' => 'state',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'idcode' => 'string',
        'spotchecker_code' => 'string',
        'type' => 'string',
        'name' => 'string',
        'gender' => 'string',
        'nrc_id' => 'string',
        'father' => 'string',
        'mother' => 'string',
        'address' => 'string',
        'village' => 'string',
        'ward' => 'string',
        'village_tract' => 'string',
        'township' => 'string',
        'district' => 'string',
        'state' => 'string',
        'mobile_provider' => 'string',

        'name_trans' => 'array',
        'gender_trans' => 'array',
        'nrc_id_trans' => 'array',
        'father_trans' => 'array',
        'mother_trans' => 'array',
        'address_trans' => 'array',
        'education_trans' => 'array',
        'ethnicity_trans' => 'array',
        'language_trans' => 'array',
        'bank_information_trans' => 'array',

        'name2_trans' => 'array',
        'gender2_trans' => 'array',
        'nrc_id2_trans' => 'array',
        'father2_trans' => 'array',
        'mother2_trans' => 'array',
        'address2_trans' => 'array',
        'education2_trans' => 'array',
        'ethnicity2_trans' => 'array',
        'language2_trans' => 'array',
        'bank_information2_trans' => 'array',

        'village_trans' => 'array',
        'ward_trans' => 'array',
        'village_tract_trans' => 'array',
        'township_trans' => 'array',
        'district_trans' => 'array',
        'state_trans' => 'array',
        'mobile_provider_trans' => 'array',
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

    public function projects()
    {
        return $this->belongsToMany(Projects::class, 'samples', 'sample_data_id', 'project_id');
    }

    public function getStateAttribute($value)
    {
        return $this->getTranslation('state', $value);
    }

    public function getDistrictAttribute($value)
    {
        return $this->getTranslation('district', $value);
    }

    public function getTownshipAttribute($value)
    {
        return $this->getTranslation('township', $value);
    }

    public function getVillageTractAttribute($value)
    {
        return $this->getTranslation('village_tract', $value);
    }

    public function getWardAttribute($value)
    {
        return $this->getTranslation('ward', $value);
    }

    public function getVillageAttribute($value)
    {
        return $this->getTranslation('village', $value);
    }

    public function getNameAttribute($value)
    {
        return $this->getTranslation('name', $value);
    }

    public function getGenderAttribute($value)
    {
        return $this->getTranslation('gender', $value);
    }

    public function getLanguageAttribute($value)
    {
        return $this->getTranslation('language', $value);
    }

    public function getEducationAttribute($value)
    {
        return $this->getTranslation('education', $value);
    }

    public function getEthnicityAttribute($value)
    {
        return $this->getTranslation('ethnicity', $value);
    }

    public function getNrcIdAttribute($value)
    {
        return $this->getTranslation('nrc_id', $value);
    }

    public function getFatherAttribute($value)
    {
        return $this->getTranslation('father', $value);
    }

    public function getMotherAttribute($value)
    {
        return $this->getTranslation('mother', $value);
    }

    public function getMobileProviderAttribute($value)
    {
        return $this->getTranslation('mobile_provider', $value);
    }

    private function getTranslation($column, $value)
    {
        $lang = \App::getLocale();
        $label = json_decode($this->attributes[$column . '_trans'], true);
        if (!empty($label) && array_key_exists($lang, $label)) {
            $translation = $label[$lang];
        }
        if (!empty($translation)) {
            $value = $translation;
        }
        return $value;
    }

}
