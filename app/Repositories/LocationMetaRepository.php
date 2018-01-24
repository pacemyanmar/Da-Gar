<?php

namespace App\Repositories;

use App\Models\LocationMeta;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class LocationMetaRepository
 * @package App\Repositories
 * @version December 3, 2017, 8:23 am UTC
 *
 * @method LocationMeta findWithoutFail($id, $columns = ['*'])
 * @method LocationMeta find($id, $columns = ['*'])
 * @method LocationMeta first($columns = ['*'])
*/
class LocationMetaRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'field_name',
        'field_type',
        'project_id'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return LocationMeta::class;
    }
}
