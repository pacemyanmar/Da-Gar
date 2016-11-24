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
        'dblink'
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
        'dblink' => 'string'
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
        return $this->HasMany(\App\Models\Question::class);
    }

    
}
