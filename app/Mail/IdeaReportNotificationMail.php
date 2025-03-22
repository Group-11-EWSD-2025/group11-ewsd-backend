<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IdeaReportNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $idea;
    public $reporter;
    public $report;
    public $qmUser;

    /**
     * Create a new message instance.
     */
    public function __construct($idea, $reporter, $report, $qmUser)
    {
        $this->idea = $idea;
        $this->reporter = $reporter;
        $this->report = $report;
        $this->qmUser = $qmUser;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Idea Reported: ' . $this->idea->title)
                    ->view('emails.idea-report-notification');
    }
}
