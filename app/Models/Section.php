<?php

namespace App\Models;

use App\Scopes\OrderByScope;
use Eloquent as Model;

/**
 * Class Section
 * @package App\Models
 * @version February 28, 2017, 3:24 pm UTC
 */
class Section extends Model
{

    public $table = 'sections';

    public $timestamps = false;

    public $fillable = [
        'sectionname',
        'sort',
        'descriptions',
        'indouble',
        'optional',
        'disablesms',
        'project_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'sectionname' => 'string',
        'sort' => 'integer',
        'descriptions' => 'string',
        'indouble' => 'boolean',
        'optional' => 'boolean',
        'disablesms' => 'boolean',
        'project_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

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
        return $this->belongsTo(Project::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'section');
    }

    /**
     * { Distance children inputs for project }
     *
     * @return     \Illuminate\Database\Eloquent\Relations\HasManyThrouth
     */
    public function inputs()
    {
        return $this->hasManyThrough(SurveyInput::class, Question::class, 'section');
    }
}
