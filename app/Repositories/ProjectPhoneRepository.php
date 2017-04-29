<?php

namespace App\Repositories;

use App\Models\ProjectPhone;
use InfyOm\Generator\Common\BaseRepository;

class ProjectPhoneRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'phone',
        'project_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return ProjectPhone::class;
    }
}
