<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'team_id','title','description','is_admin'
    ];

}
