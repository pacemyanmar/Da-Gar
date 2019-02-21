<?php

namespace App\Repositories;

use App\Models\Section;
use InfyOm\Generator\Common\BaseRepository;

class SectionRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'sectionname',
        'sort',
        'project_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Section::class;
    }
}
