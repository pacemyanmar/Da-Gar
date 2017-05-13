<?php

namespace App\Repositories;

use App\Models\Observer;
use InfyOm\Generator\Common\BaseRepository;

class ObserverRepository extends BaseRepository
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
        'education',
        'created_at',
        'updated_at'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Observer::class;
    }
}
