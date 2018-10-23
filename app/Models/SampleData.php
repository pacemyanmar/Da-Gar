<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class SampleData
 * @package App\Models
 * @version December 30, 2016, 1:04 pm UTC
 */
class SampleData extends Model
{

    public $table = 'sample_datas';

    public $timestamps = false;

    public $incrementing = false;

    public $fillable = [

    ];

    public static $export = [
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [

    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function samples()
    {
        return $this->hasMany(Sample::class, 'sample_data_id');
    }

    public function observers()
    {
        return $this->hasMany(Observer::class, 'sample_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Projects::class, 'samples', 'sample_data_id', 'project_id');
    }

    public function getSampleByCode($code, $table)
    {
        $this->setTable($table);
        return $this->find($code);
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key)
    {

        preg_match('/get(.*)Attribute/', $key, $matches);

        //return method_exists($this, 'get'.Str::studly($key).'Attribute');
        return true;
    }

    public function __call($method, $parameters)
    {
        if (in_array($method, ['increment', 'decrement'])) {
            return $this->$method(...$parameters);
        }

        if(preg_match('/get(.*)Attribute/', $method, $matches)) {
            if (\App::isLocale(config('app.locale'))) {
                return $parameters[0];
            } else {
                return trans('location.' . str_dbcolumn($parameters[0]));
            }
        }

        return $this->newQuery()->$method(...$parameters);
    }

    /*
     * Need to sanitize data
     */

    function insertOrUpdate(array $rows, $table){
        //$table = \DB::getTablePrefix().with(new self)->getTable();

        $first = reset($rows);

        $columns = implode( ',',
            array_map( function( $value ) { return "$value"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                        array_map( function( $value ) { return '"'.str_replace('"', '""', $value).'"'; } , $row )
                    ).')';
            } , $rows )
        );

        $updates = implode( ',',
            array_map( function( $value ) { return "$value = VALUES($value)"; } , array_keys($first) )
        );

        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        return \DB::statement( $sql );
    }

}
