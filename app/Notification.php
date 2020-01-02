<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'roster_id','to','from','type','title','message','is_read','news_id','tryout_id','game_id','is_reject','is_accept',
    ];
}
