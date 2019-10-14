<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tryout extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'team_id', 'street', 'state','zipcode','timeoftryout','dateoftryout','costoftryout',
    ];

    public function user()
    {
    	return $this->belongsTo('App\User');
    }

    public function players()
    {
        return $this->hasmany('App\Tryout','player_id');
    }

    public function tryoutplayers()
    {
        return $this->hasmany('App\TryoutPlayers','tryout_id');
    }
}
