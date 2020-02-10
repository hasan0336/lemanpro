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
    public $role;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($game,$role,$team_name,$opponent_name)
    {
        $this->game = $game;
        $this->role = $role;
        $this->team_name = $team_name;
        $this->opponent = $opponent_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if($this->role == 1)
        {
            return $this->from('dev.appsnado@gmail.com')
            ->view('emails.match_report')
            ->with(['game' => $this->game,'team_name' => $this->team_name,'opponent'=>$this->opponent]);
        }
        else
        {
            return $this->from('dev.appsnado@gmail.com')
            ->view('emails.player_match_report')
            ->with(['game' => $this->game,'team_name' => $this->team_name,'opponent'=>$this->opponent]);
        }
    }
}
