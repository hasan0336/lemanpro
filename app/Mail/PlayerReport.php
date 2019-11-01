<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;
class PlayerReport extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // dd($this->user->email);
        return $this->from('dev.appsnado@gmail.com')
        ->view('emails.player_report')
        ->with(['email' => $this->user->email,'yellow' => $this->user->yellow, 'red' => $this->user->red, 'goals' => $this->user->goals,'trophies' => $this->user->trophies,'time' => $this->user->time]);
        // return $this->view('view.name');
    }
}
