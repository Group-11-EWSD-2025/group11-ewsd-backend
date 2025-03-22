<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $creator;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $password, $creator)
    {
        $this->user = $user;
        $this->password = $password;
        $this->creator = $creator;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Welcome to ' . config('app.name') . ' - Your Account Details')
                    ->view('emails.new-user-account');
    }
}
