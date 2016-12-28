<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Enumerator
 * @package App\Models
 * @version December 28, 2016, 1:05 pm UTC
 */
class Enumerator extends Model
{

    public $table = 'enumerators';

    public $fillable = [
        'idcode',
        'name',
        'gender',
        'nrc_id',
        'dob',
        'address',
        'village',
        'village_tract',
        'township',
        'district',
        'state',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'idcode' => 'string',
        'name' => 'string',
        'gender' => 'string',
        'nrc_id' => 'string',
        'address' => 'string',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function village()
    {
        return $this->belongsTo(Location::class, 'village');
    }

    public function village_tract()
    {
        return $this->belongsTo(Location::class, 'village_tract');
    }

    public function township()
    {
        return $this->belongsTo(Location::class, 'township');
    }

    public function district()
    {
        return $this->belongsTo(Location::class, 'district');
    }

    public function state()
    {
        return $this->belongsTo(Location::class, 'state');
    }

    public function results()
    {
        return $this->morphMany(Sample::class, 'samplable');
    }

}
