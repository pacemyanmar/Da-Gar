<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class Location
 * @package App\Models
 * @version December 28, 2016, 1:04 pm UTC
 */
class Location extends Model
{
    public $table = 'locations';

    public $timestamps = false;

    public $fillable = [
        'idcode',
        'name',
        'type',
        'lat_long',
        'parent_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'idcode' => 'string',
        'name' => 'string',
        'type' => 'string',
        'lat_long' => 'string',
        'parent_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function enuVillage()
    {
        return $this->hasMany(Enumerator::class, 'village');
    }

    public function enuVillageTract()
    {
        return $this->hasMany(Enumerator::class, 'village_tract');
    }

    public function enuTownship()
    {
        return $this->hasMany(Enumerator::class, 'township');
    }

    public function enuDistrict()
    {
        return $this->hasMany(Enumerator::class, 'district');
    }

    public function enuState()
    {
        return $this->hasMany(Enumerator::class, 'state');
    }

    public function results()
    {
        return $this->morphMany(Sample::class, 'samplable');
    }

}
