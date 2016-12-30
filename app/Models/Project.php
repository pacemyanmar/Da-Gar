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
        'status',
        'index_columns',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'project' => 'string',
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

}
