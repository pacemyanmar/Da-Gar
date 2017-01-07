<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Sample
 * @package App\Models
 * @version December 28, 2016, 2:16 pm UTC
 */
class Sample extends Model
{

    public $table = 'samples';

    public $timestamps = false;

    public $fillable = [
        'sample_data_id',
        'sample_data_type',
        'form_id',
        'project_id',
        'user_id',
        'update_user_id',
        'qc_user_id',
        'extras',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'sample_data_id' => 'integer',
        'sample_data_type' => 'string',
        'form_id' => 'integer',
        'project_id' => 'integer',
        'user_id' => 'integer',
        'update_user_id' => 'integer',
        'qc_user_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function result()
    {
        return $this->hasOne(SurveyResult::class);
    }

    public function resultWithTable($table = null)
    {
        $foreignKey = $this->getForeignKey();

        $instance = new SurveyResult();

        $instance->setTable($table);

        $localKey = $this->getKeyName();

        return new HasOne($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

}
