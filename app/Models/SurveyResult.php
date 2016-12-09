<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Moloquent;

/**
 * Class SurveyResult
 * @package App\Models
 * @version November 13, 2016, 1:34 pm UTC
 */
class SurveyResult extends Moloquent
{
    protected $connection = 'mongodb';

    public $table = 'survey_results';

    public $collection = 'survey_results';

    public $timestamps = false;

    public $fillable = [
        'value',
        'inputid',
        'sort',
        'samplable_id',
        'samplable_type',
        'data_one',
        'data_two',
        'data_three',
        'data_four',
        'data_five',
        'data_six',
        'data_seven',
        'data_eight',
        'data_nine',
        'data_ten',
        'sample',
        'section',
        'survey_input_id',
        'project_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'value' => 'string',
        'inputid' => 'string',
        'sort' => 'integer',
        'samplable_id' => 'integer',
        'samplable_type' => 'string',
        'samplable_data' => 'array',
        'survey_input_id' => 'integer',
        'project_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function surveyInput()
    {
        return $this->belongsTo(\App\Models\SurveyInput::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }
}
