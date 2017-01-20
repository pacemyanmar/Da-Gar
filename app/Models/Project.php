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
        'type',
        'sections',
        'samples',
        'copies',
        'dblink',
        'dbname',
        'status',
        'index_columns',
        'project_trans',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'project' => 'string',
        'project_trans' => 'array',
        'type' => 'string',
        'sections' => 'array',
        'samples' => 'array',
        'copies' => 'integer',
        'dblink' => 'string',
        'index_columns' => 'array',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'project' => 'required',
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
        return $this->hasManyThrough(SurveyInput::class, Question::class);
    }

    public function samplesDb()
    {
        return $this->hasMany(Sample::class);
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
        $lang = \App::getLocale();
        $project = json_decode($this->attributes['project_trans'], true);
        if (!empty($project) && array_key_exists($lang, $project)) {
            $translation = $project[$lang];
        }
        if (!empty($translation)) {
            $value = $translation;
        }
        return $value;
    }
}
