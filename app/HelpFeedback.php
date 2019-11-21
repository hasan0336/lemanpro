<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpFeedback extends Model
{
	protected $table = 'help_feedbacks';
	protected $fillable = [
        'user_id','subject','description',
    ];


    public function helpfeedbackimage()
    {
    	return $this->hasmany('App/helpfeedbackimage');
    }
}

