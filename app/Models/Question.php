<?php

namespace App\Models;

use App\Scopes\OrderByScope;
use Eloquent as Model;
use Illuminate\Support\Facades\Lang;

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
        'observation_type',
        'party',
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
        'qnum_trans' => 'string',
        'question' => 'string',
        'question_trans' => 'string',
        'raw_ans' => 'array',
        'render' => 'array',
        'sort' => 'integer',
        'project_id' => 'integer',
        'section' => 'integer',
        'double_entry' => 'boolean',
        'optional' => 'boolean',
        'report' => 'boolean',
        'layout' => 'string',
        'observation_type' => 'array',
        'party' => 'array'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'qnum' => 'unique_with:questions,project_id',
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

    public function sectionInstance()
    {
        return $this->belongsTo(Section::class, 'section');
    }

    // backward compatibility
    // need to remove later
    public function sectionDb()
    {
        return $this->belongsTo(Section::class, 'section');
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

    public function getQuestionAttribute($value)
    {
        return $this->getTranslation('question', $value);
    }

    public function getQuestionTransAttribute($value)
    {
        $second_locale = config('sms.second_locale.locale');
        return Lang::get('questions.q'.$this->attributes['id'].strtolower($this->attributes['qnum']), [], $second_locale);
    }

    private function getTranslation($column, $value)
    {
        $primary_locale = config('sms.primary_locale.locale');
        $second_locale = config('sms.second_locale.locale');
        if (\App::isLocale($primary_locale)) {
            return $value;
        } else {
            return Lang::get('questions.q'.$this->attributes['id'].strtolower($this->attributes['qnum']), [], $second_locale);
        }
    }
}
