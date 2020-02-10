<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
 
 	protected $table = 'transaction';   
    protected $fillable = [
        'tryout_id', 'player_id', 'team_id','leman_fees','tryout_fees'
    ];
}
