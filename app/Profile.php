<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent;
use DB;
class Profile extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id', 'first_name', 'last_name','image','dob','gender','cob','cop','height','weight','team_name','club_address','city','state','zip_code','pitch_type','capacity','website','instagram','twitter','coach_name','address','longitude','latitude','home_field_address','home_field_city','home_field_state','home_field_zipcode',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function game()
    {
    	return $this->hasmany('App\Game','team_id');
    }

    public function match()
    {
        return $this->hasmany('App\match','palyer_id');
    }

    public function roster()
    {
        return $this->hasmany('App\Rosters');
    }
}
