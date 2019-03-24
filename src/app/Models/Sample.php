<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'frequency',
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
        'frequency' => 'integer',
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

    public $relatedTable;

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function data()
    {
        return $this->belongsTo(SampleData::class, 'sample_data_id');
    }

    public function result()
    {
        return $this->hasOne(SurveyResult::class);
    }


    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'update_user_id');
    }

    public function checkedBy()
    {
        return $this->belongsTo(User::class, 'qc_user_id');
    }

    public function setRelatedTable($table)
    {
        $this->relatedTable = $table;
        return $this;
    }

    public function details()
    {
        $table = $this->project->dbname.'_samples';

        $instance = $this->newRelatedInstance(SampleData::class);

        $instance->setTable($table);

        return $this->newBelongsTo(
            $instance->newQuery(), $this, 'sample_data_id', 'id', 'sample'
        );
    }

    public function results()
    {

        $sample = $this->query();

        $table = $this->project->dbname.'_view';
        $foreignKey = $this->getForeignKey();

        $instance = new SurveyResult();

        $instance->bind($table);

        $localKey = $this->getKeyName();

        return new HasOne($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

    /**
     * [resultWithTable description]
     * @param  [type] $table [description]
     * @return [type]        [description]
     * To Do change method name to hasOneResult
     */
    public function resultWithTable($table = null)
    {
        $foreignKey = $this->getForeignKey();

        if (empty($table)) {
            $table = $this->relatedTable;
        }

        $instance = new SurveyResult();

        $instance->bind($table);

        $localKey = $this->getKeyName();

        return new HasOne($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function resultsWithTable($table = null)
    {
        $foreignKey = $this->getForeignKey();

        if (empty($table)) {
            $table = $this->relatedTable;
        }

        $instance = new SurveyResult();

        $instance->setTable($table);

        $localKey = $this->getKeyName();

        return new HasMany($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }

}
