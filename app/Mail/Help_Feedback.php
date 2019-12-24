<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;
class Help_Feedback extends Mailable
{
    use Queueable, SerializesModels;
    public $help_feedback;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($help_feedback)
    {
        $this->help_feedback = $help_feedback;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('dev.appsnado@gmail.com')
        ->view('emails.help_feedback')
        ->with(['help_feedback' => $this->help_feedback]);
    }
}
