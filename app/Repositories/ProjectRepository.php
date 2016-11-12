<?php

namespace App\Repositories;

use App\Models\Project;
use App\Traits\VueTablesTrait;
use InfyOm\Generator\Common\BaseRepository;

class ProjectRepository extends BaseRepository
{
    use VueTablesTrait;
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'project',
        'type',
        'sections',
        'dblink'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Project::class;
    }
}
