<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TryoutPlayers extends Model
{
   	protected $primaryKey = 'id';
    protected $fillable = [
        'tryout_id','player_id',
    ];

    public function tryout()
    {
    	return $this->hasone('App\tryout');
    }

    public function profile()
    {
    	return $this->hasone('App\Profile','user_id','player_id');
    }
}
