<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    const STATUS_NEW = 'NEW';
    const STATUS_PROCESSING = 'PROCESSING';
    const STATUS_DONE = 'DONE';
    const STATUS_ERROR = 'ERROR';

    public $timestamps = false;

}
