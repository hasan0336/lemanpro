<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\User;
class NewOtp extends Mailable
{
    use Queueable, SerializesModels;
    public $new_pwd;
    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($new_pwd, User $user)
    {
        $this->new_pwd = $new_pwd;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->from('dev.appsnado@gmail.com')
        ->view('emails.otp')
        ->with([
                'password' => $this->new_pwd,
                'email' => $this->user->email,
        ]);;
    }
}
