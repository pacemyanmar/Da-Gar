<?php

namespace App\Models;

use App\Scopes\OrderByScope;
use Eloquent as Model;

/**
 * Class Question
 * @package App\Models
 * @version November 3, 2016, 3:53 pm UTC
 */
class Question extends Model
{
    public $table = 'questions';

    public $timestamps = false;

    public $fillable = [
        'qnum',
        'question',
        'raw_ans',
        'render',
        'sort',
        'layout',
        'section',
        'project_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'qnum' => 'string',
        'question' => 'string',
        'raw_ans' => 'array',
        'render' => 'array',
        'sort' => 'integer',
        'project_id' => 'integer',
        'section' => 'integer',
        'layout' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'qnum' => 'required|alpha_num',
        'question' => 'required',
        'raw_ans' => 'required',
        'sort' => 'required',
    ];

    /**
     * add global scope for ordering
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new OrderByScope('sort', 'asc'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function surveyInputs()
    {
        return $this->hasMany(\App\Models\SurveyInput::class);
    }
}
