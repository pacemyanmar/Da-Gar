<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Project
 * @package App\Models
 * @version November 2, 2016, 8:56 am UTC
 */
class Project extends Model
{
    public $table = 'projects';

    public $fillable = [
        'project',
        'unique_code',
        'dbname',
        'dblink',
        'type',
        'dbgroup',
        'parties',
        'samples',
        'copies',
        'index_columns',
        'status',
        'project_trans',
        'training',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'project' => 'string',
        'unique_code' => 'string',
        'project_trans' => 'string',
        'type' => 'string',
        'parties' => 'string',
        'samples' => 'array',
        'copies' => 'integer',
        'dblink' => 'string',
        'index_columns' => 'array',
        'training' => 'boolean',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'project' => 'required',
        'unique_code' => 'unique:projects|required',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     **/
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * { Distance children inputs for project }
     *
     * @return     \Illuminate\Database\Eloquent\Relations\HasManyThrouth
     */
    public function inputs()
    {
        return $this->hasManyThrough(SurveyInput::class, Question::class, 'project_id', 'question_id');
    }

    public function samplesList()
    {
        return $this->hasMany(Sample::class);
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function logics()
    {
        return $this->hasMany(LogicalCheck::class);
    }

    /**
     * Projects and sample_datas has pivot relation.
     * samples is pivot table
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function samplesData()
    {
        return $this->belongsToMany(SampleData::class, 'samples', 'project_id', 'sample_data_id');
    }

    public function getProjectAttribute($value)
    {
        if (\App::isLocale('en')) {
            return $value;
        } else {
            return ($this->attributes['project_trans'])? $this->attributes['project_trans']:$value;
        }
    }

    public function getProjectEnAttribute($value)
    {
        return $this->attributes['project'];
    }
}
