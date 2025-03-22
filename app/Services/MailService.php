<?php

namespace App\Services;

use App\Mail\ForgotPasswordMail;
use App\Mail\IdeaReportNotificationMail;
use App\Mail\NewCommentNotificationMail;
use App\Mail\NewIdeaNotificationMail;
use App\Mail\NewUserAccountMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Send new user account creation email
     */
    public function sendNewUserAccountMail($user, $password, $creator)
    {
        try {
            Mail::to($user->email)
                ->send(new NewUserAccountMail(
                    $user,
                    $password,
                    $creator
                ));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send new user account email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'creator_id' => $creator->id
            ]);
            return false;
        }
    }

    /**
     * Send forgot password email
     */
    public function sendForgotPasswordMail($user, $newPassword)
    {
        try {
            Mail::to($user->email)
                ->send(new ForgotPasswordMail(
                    $user,
                    $newPassword
                ));

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send forgot password email: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);
            return false;
        }
    }

    /**
     * Send new idea notification to QA users
     */
    public function sendNewIdeaNotification($idea, $creator, $qaUsers)
    {
        $failedEmails = [];

        foreach ($qaUsers as $qaUser) {
            try {
                Mail::to($qaUser->email)
                    ->send(new NewIdeaNotificationMail(
                        $idea,
                        $creator,
                        $qaUser
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send new idea notification email: ' . $e->getMessage(), [
                    'idea_id' => $idea->id,
                    'qa_user_id' => $qaUser->id
                ]);
                $failedEmails[] = $qaUser->email;
            }
        }

        return empty($failedEmails) ? true : $failedEmails;
    }

    /**
     * Send new comment notification
     */
    public function sendNewCommentNotification($idea, $comment, $commenter, $recipients)
    {
        $failedEmails = [];

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)
                    ->send(new NewCommentNotificationMail(
                        $idea,
                        $comment,
                        $commenter,
                        $recipient
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send new comment notification email: ' . $e->getMessage(), [
                    'idea_id' => $idea->id,
                    'comment_id' => $comment->id,
                    'recipient_id' => $recipient->id
                ]);
                $failedEmails[] = $recipient->email;
            }
        }

        return empty($failedEmails) ? true : $failedEmails;
    }

    /**
     * Send idea report notification to QM users
     */
    public function sendIdeaReportNotification($idea, $reporter, $report, $qmUsers)
    {
        $failedEmails = [];

        foreach ($qmUsers as $qmUser) {
            try {
                Mail::to($qmUser->email)
                    ->send(new IdeaReportNotificationMail(
                        $idea,
                        $reporter,
                        $report,
                        $qmUser
                    ));
            } catch (\Exception $e) {
                Log::error('Failed to send idea report notification email: ' . $e->getMessage(), [
                    'idea_id' => $idea->id,
                    'report_id' => $report->id,
                    'qm_user_id' => $qmUser->id
                ]);
                $failedEmails[] = $qmUser->email;
            }
        }

        return empty($failedEmails) ? true : $failedEmails;
    }

    /**
     * Get users by role
     */
    private function getUsersByRole($role)
    {
        return \App\Models\User::where('role', $role)->get();
    }
}