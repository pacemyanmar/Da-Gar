<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Translation
 * @package App\Models
 * @version May 5, 2018, 7:05 am UTC
 *
 * @property string group
 * @property string key
 * @property string text
 * @property string|\Carbon\Carbon created_at
 * @property string|\Carbon\Carbon updated_at
 */
class Translation extends Model
{

    public $table = 'language_lines';
    
    public $timestamps = false;



    public $fillable = [
        'group',
        'key',
        'text',
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'group' => 'string',
        'key' => 'string',
        'text' => 'array'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /*
     * Need to sanitize data
     */

    function insertOrUpdate(array $rows){
        $table = \DB::getTablePrefix().with(new self)->getTable();
        $first = reset($rows);

        $columns = implode( ',',
            array_map( function( $value ) { return "`$value`"; } , array_keys($first) )
        );

        $values = implode( ',', array_map( function( $row ) {
                return '('.implode( ',',
                        array_map( function( $value ) { return '"'.str_replace('"', '""', $value).'"'; } , $row )
                    ).')';
            } , $rows )
        );

        $updates = implode( ',',
            array_map( function( $value ) { return "`$value` = VALUES(`$value`)"; } , array_keys($first) )
        );

        $sql = "INSERT INTO {$table}({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";

        return \DB::statement( $sql );
    }
    
}
