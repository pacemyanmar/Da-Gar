<?php

namespace App\Repositories;

use App\Models\Sample;
use InfyOm\Generator\Common\BaseRepository;

class SampleRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'samplable_id',
        'samplable_type',
        'form_id',
        'project_id',
        'user_id',
        'update_user_id',
        'qc_user_id',
        'extras'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return Sample::class;
    }
}
