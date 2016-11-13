<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Input
 * @package App\Models
 * @version November 13, 2016, 7:41 am UTC
 */
class Input extends Model
{

    public $table = 'inputs';
    


    public $fillable = [
        'type',
        'name',
        'label',
        'default',
        'sort',
        'question_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'type' => 'string',
        'name' => 'string',
        'label' => 'string',
        'default' => 'string',
        'sort' => 'integer',
        'question_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function surveyResults()
    {
        return $this->hasMany(\App\Models\SurveyResult::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }
}
