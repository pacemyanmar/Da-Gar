<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class ProjectPhone
 * @package App\Models
 * @version April 28, 2017, 9:36 pm UTC
 */
class ProjectPhone extends Model
{

    public $table = 'project_phones';

    public $fillable = [
        'phone',
        'project_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'phone' => 'string',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
