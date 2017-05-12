<?php

namespace App\Repositories;

use App\Models\Obeserver;
use InfyOm\Generator\Common\BaseRepository;

class ObeserverRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'code',
        'sample_id',
        'national_id',
        'phone_1',
        'phone_2',
        'address',
        'language',
        'ethnicity',
        'occupation',
        'gender',
        'dob',
        'education'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Obeserver::class;
    }
}
