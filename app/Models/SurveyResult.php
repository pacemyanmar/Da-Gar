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
    protected $guarded = ['id'];

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

    /**
     * [samplable description]
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function samplable()
    {
        return $this->morphTo();
    }
}
