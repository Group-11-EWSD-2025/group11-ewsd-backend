<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewCommentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $idea;
    public $comment;
    public $commenter;
    public $recipient;

    /**
     * Create a new message instance.
     */
    public function __construct($idea, $comment, $commenter, $recipient)
    {
        $this->idea = $idea;
        $this->comment = $comment;
        $this->commenter = $commenter;
        $this->recipient = $recipient;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Comment on Idea: ' . $this->idea->title)
                    ->view('emails.new-comment-notification');
    }
}
