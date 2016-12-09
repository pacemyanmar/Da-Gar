<?php

namespace App\Models;

use Eloquent as Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

/**
 * Class Project
 * @package App\Models
 * @version November 2, 2016, 8:56 am UTC
 */
class Project extends Model
{
    use HybridRelations;

    protected $connection = 'mysql';

    public $table = 'projects';

    public $fillable = [
        'project',
        'type',
        'sections',
        'samples',
        'dblink',
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
        'dblink' => 'string',
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
        return $this->HasMany(Question::class);
    }

    /**
     * { Distance children inputs for project }
     *
     * @return     \Illuminate\Database\Eloquent\Relations\HasManyThrouth
     */
    public function inputs()
    {
        return $this->HasManyThrough(SurveyInput::class, Question::class);
    }

}
