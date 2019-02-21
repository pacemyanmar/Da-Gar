<?php

namespace App\Repositories;

use App\Models\SmsLog;
use InfyOm\Generator\Common\BaseRepository;

class SmsLogRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        
    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return SmsLog::class;
    }
}
