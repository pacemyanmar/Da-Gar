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
        'qnum_trans',
        'question',
        'question_trans',
        'css_id',
        'raw_ans',
        'render',
        'sort',
        'layout',
        'section',
        'double_entry',
        'optional',
        'report',
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
        'qnum_trans' => 'array',
        'question' => 'string',
        'question_trans' => 'array',
        'raw_ans' => 'array',
        'render' => 'array',
        'sort' => 'integer',
        'project_id' => 'integer',
        'section' => 'integer',
        'double_entry' => 'boolean',
        'optional' => 'boolean',
        'report' => 'boolean',
        'layout' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'qnum' => 'required|alpha_num|unique_with:questions,project_id',
        'question' => 'required',
        'raw_ans' => 'required',
        'section' => 'required',
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

    public function scopeOnlyPublished($query)
    {
        return $query->whereQstatus('published');
    }

    public function getQnumAttribute($value)
    {
        $lang = \App::getLocale();
        $qnum = json_decode($this->attributes['qnum_trans'], true);
        if (!empty($qnum) && array_key_exists($lang, $qnum)) {
            $translation = $qnum[$lang];
        }
        if (!empty($translation)) {
            $value = $translation;
        }
        return $value;
    }

    public function getQuestionAttribute($value)
    {
        $lang = \App::getLocale();
        $question = json_decode($this->attributes['question_trans'], true);
        if (!empty($question) && array_key_exists($lang, $question)) {
            $translation = $question[$lang];
        }
        if (!empty($translation)) {
            $value = $translation;
        }
        return $value;
    }
}
