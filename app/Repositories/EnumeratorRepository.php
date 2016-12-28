<?php

namespace App\Repositories;

use App\Models\Enumerator;
use InfyOm\Generator\Common\BaseRepository;

class EnumeratorRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'idcode',
        'name',
        'gender',
        'nrc_id',
        'dob',
        'address'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Enumerator::class;
    }
}
