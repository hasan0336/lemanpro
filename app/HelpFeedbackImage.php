<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpFeedbackImage extends Model
{
	protected $table = 'help_feedback_images';
	protected $fillable = [
        'help_feedback_id','help_feedback_image',
    ];
    public function helpfeedback()
    {
    	return $this->belongsto('App/helpfeedback');
    }
}
