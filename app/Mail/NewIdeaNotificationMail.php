<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewIdeaNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $idea;
    public $creator;
    public $qaUser;

    /**
     * Create a new message instance.
     */
    public function __construct($idea, $creator, $qaUser)
    {
        $this->idea = $idea;
        $this->creator = $creator;
        $this->qaUser = $qaUser;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Idea Submitted for Review')
                    ->view('emails.new-idea-notification');
    }
}
