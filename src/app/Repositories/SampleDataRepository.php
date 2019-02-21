<?php

namespace App\Repositories;

use App\Models\SampleData;
use InfyOm\Generator\Common\BaseRepository;

class SampleDataRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'idcode',
        'type',
        'name',
        'gender',
        'nrc_id',
        'dob',
        'father',
        'mother',
        'address',
        'village',
        'village_tract',
        'township',
        'district',
        'state',
        'parent_id',
        'created_at',
        'updated_at'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return SampleData::class;
    }
}
