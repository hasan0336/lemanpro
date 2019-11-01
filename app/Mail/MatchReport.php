<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;
class MatchReport extends Mailable
{
    use Queueable, SerializesModels;
    public $game;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($game)
    {
        $this->game = $game;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('dev.appsnado@gmail.com')
        ->view('emails.match_report')
        ->with(['game' => $this->game]);
    }
}
