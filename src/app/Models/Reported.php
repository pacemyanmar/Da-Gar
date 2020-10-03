<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reported extends Model
{
    public $table = 'reported';

    //public $timestamps = false;

    public $fillable = [
        'channel',
        'inputid',
        'sid',
        'scode',
        'report_number',
        'followup',
        'project_id'
    ];
}
