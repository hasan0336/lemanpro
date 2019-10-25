<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'game_id','player_id','yellow','red','goals','trophies','time',
    ];


    public function user()
    {
    	return $this->belongsto('App/user');
    }
}
