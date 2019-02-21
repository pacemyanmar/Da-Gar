<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    public $primaryKey = 'phone';
    public $table = 'phones';

    public $incrementing = false;
    public $timestamps = false;
    public $fillable = [
        'phone',
        'encoding',
    ];
}
