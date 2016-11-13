<?php

namespace App\Repositories;

use App\Models\SurveyInput;
use InfyOm\Generator\Common\BaseRepository;

class SurveyInputRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'type',
        'name',
        'label',
        'value',
        'sort',
        'question_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return SurveyInput::class;
    }
}
