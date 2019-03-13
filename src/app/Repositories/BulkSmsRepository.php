<?php

namespace App\Repositories;

use App\Models\BulkSms;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class BulkSmsRepository
 * @package App\Repositories
 * @version March 11, 2019, 4:58 pm UTC
 *
 * @method BulkSms findWithoutFail($id, $columns = ['*'])
 * @method BulkSms find($id, $columns = ['*'])
 * @method BulkSms first($columns = ['*'])
*/
class BulkSmsRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'phone',
        'name',
        'message'
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return BulkSms::class;
    }
}
