<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class LocationMeta
 * @package App\Models
 * @version December 3, 2017, 8:23 am UTC
 *
 * @property string field_name
 * @property string field_type
 * @property integer project_id
 */
class LocationMeta extends Model
{
    use SoftDeletes;

    public $table = 'location_metas';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'label',
        'field_name',
        'field_type',
        'project_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'label' => 'string',
        'field_name' => 'string',
        'field_type' => 'string',
        'project_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'project_id' => 'required',
        'fields.*.field_name' => 'required|alpha_dash',
        'fields.*.field_type' => 'required'
    ];

    
}
