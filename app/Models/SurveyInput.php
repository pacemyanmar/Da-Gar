<?php

namespace App\Models;

use App\Scopes\OrderByScope;
use Eloquent as Model;

/**
 * Class SurveyInput
 * @package App\Models
 * @version November 13, 2016, 8:59 am UTC
 */
class SurveyInput extends Model
{
    public $table = 'survey_inputs';

    public $incrementing = false;

    protected $keyType = 'varchar';

    public $timestamps = false;

    public $fillable = [
        'id',
        'inputid',
        'type',
        'name',
        'label',
        'value',
        'sort',
        'question_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'string',
        'inputid' => 'string',
        'type' => 'string',
        'name' => 'string',
        'label' => 'string',
        'value' => 'string',
        'sort' => 'integer',
        'question_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'id' => 'required|unique',
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function surveyResults()
    {
        return $this->hasMany(\App\Models\SurveyResult::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function question()
    {
        return $this->belongsTo(\App\Models\Question::class);
    }
}
