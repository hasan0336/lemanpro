<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'roster_id','to','from','type','title','message','is_read',
    ];
}
