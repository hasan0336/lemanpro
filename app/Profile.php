<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent;
use DB;
class Profile extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id', 'first_name', 'last_name','image','dob','gender','cob','cop','height','weight','team_name','club_address','city','state','zip_code','pitch_type','capacity','website','instagram','twitter','coach_name',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
