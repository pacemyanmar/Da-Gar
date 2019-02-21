<?php

namespace App\Repositories;

use App\Models\SampleDetails;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class SampleDetailsRepository
 * @package App\Repositories
 * @version September 6, 2018, 10:00 am UTC
 *
 * @method SampleDetails findWithoutFail($id, $columns = ['*'])
 * @method SampleDetails find($id, $columns = ['*'])
 * @method SampleDetails first($columns = ['*'])
*/
class SampleDetailsRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'project_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return SampleDetails::class;
    }
}
