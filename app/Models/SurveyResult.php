<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class SurveyResult
 * @package App\Models
 * @version November 13, 2016, 1:34 pm UTC
 */
class SurveyResult extends Model
{

    public $table = 'survey_results';
    


    public $fillable = [
        'value',
        'qnum',
        'sort',
        'samplable_id',
        'samplable_type',
        'survey_input_id',
        'project_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'value' => 'string',
        'qnum' => 'string',
        'sort' => 'integer',
        'samplable_id' => 'integer',
        'samplable_type' => 'string',
        'survey_input_id' => 'integer',
        'project_id' => 'integer'
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
