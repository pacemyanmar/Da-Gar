<?php

namespace App\Repositories;

use App\Models\Voter;
use App\Traits\VueTablesTrait;
use InfyOm\Generator\Common\BaseRepository;

class VoterRepository extends BaseRepository
{
    use VueTablesTrait;
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'name',
        'gender',
        'nrc_id',
        'father',
        'mother',
        'address',
        'dob'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Voter::class;
    }
}
