<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Question
 * @package App\Models
 * @version November 3, 2016, 3:53 pm UTC
 */
class Question extends Model
{

    public $table = 'questions';
    


    public $fillable = [
        'qnum',
        'question',
        'raw_ans',
        'sort',
        'project_id',
        'render',
        'section',
        'layout'
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
        'layout' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'qnum' => 'required',
        'question' => 'required',
        'raw_ans' => 'required',
        'sort' => 'required'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }
}