<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class SurveyResult
 * @package App\Models
 * @version November 13, 2016, 1:34 pm UTC
 */
class SurveyResult extends Model
{
    protected $guarded = ['id'];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }
}
