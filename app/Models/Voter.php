<?php

namespace App\Models;

use App\Traits\SampleMorphManyTrait;
use Eloquent as Model;

/**
 * Class Voter
 * @package App\Models
 * @version November 10, 2016, 5:08 am UTC
 */
class Voter extends Model
{
    use SampleMorphManyTrait;

    public $table = 'voters';

    public $timestamps = false;

    public $fillable = [
        'name',
        'gender',
        'nrc_id',
        'father',
        'mother',
        'address',
        'dob',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'gender' => 'string',
        'nrc_id' => 'string',
        'father' => 'string',
        'mother' => 'string',
        'address' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function results($table = null)
    {
        $survey = new SurveyResult;
        if ($table) {
            $survey->setTable($table);
        }
        return $this->morphMany($survey, 'samplable');
    }

}
