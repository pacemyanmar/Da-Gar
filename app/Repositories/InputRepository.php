<?php

namespace App\Repositories;

use App\Models\Input;
use InfyOm\Generator\Common\BaseRepository;

class InputRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'type',
        'name',
        'label',
        'default',
        'sort',
        'question_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Input::class;
    }
}
