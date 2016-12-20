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
        'name',
        'type',
        'unique',
        'extras'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return SampleData::class;
    }
}
