<?php

namespace App\Repositories;

use App\Models\SurveyResult;
use InfyOm\Generator\Common\BaseRepository;

class SurveyResultRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'value',
        'qnum',
        'sort'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return SurveyResult::class;
    }
}
