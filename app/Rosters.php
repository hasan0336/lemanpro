<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rosters extends Model
{
	protected $primaryKey = 'id';
    protected $fillable = [
        'team_id', 'player_id', 'request',
    ];

    public function user()
    {
    	return $this->belongsTo('App\User');
    }
    
    public function profile()
    {
    	return $this->belongsTo('App\Profile');
    }
}
