<?php

namespace App\Repositories;

use App\Models\LogicalCheck;
use InfyOm\Generator\Common\BaseRepository;

class LogicalCheckRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'leftval',
        'rightval',
        'operator',
        'scope'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return LogicalCheck::class;
    }
}
