<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'team_id',
    ];
}
