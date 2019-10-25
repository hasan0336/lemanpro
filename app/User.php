<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password','email_token','login_type','device_type','device_token','role_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function profile()
    {
        return $this->hasOne('App\Profile');
    }

    public function tryout()
    {
        return $this->hasmany('App\Tryout','team_id');
    }

    public function tryoutplayers()
    {
        return $this->hasmany('App\TryoutPlayers');
    }

    public function rosters()
    {
        return $this->hasmany('App\Rosters','player_id');
    }

    public function match()
    {
        return $this->hasmany('App\match');
    }
}
