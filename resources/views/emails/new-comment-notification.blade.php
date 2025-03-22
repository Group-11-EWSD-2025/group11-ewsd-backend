@extends('emails.layouts.base')

@section('title', 'New Comment Notification')

@section('header', 'New Comment on Idea')

@section('content')
    <p>Hello {{ $recipient->name }},</p>

    <p>A new comment has been added to an idea you're involved with.</p>

    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin-top: 0; color: #2d3748;">Idea Details:</h3>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #4a5568; width: 120px;">Title:</td>
                <td style="padding: 8px 0; color: #2d3748;"><strong>{{ $idea->title }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #4a5568;">Department:</td>
                <td style="padding: 8px 0; color: #2d3748;">{{ $idea->department->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #4a5568;">Category:</td>
                <td style="padding: 8px 0; color: #2d3748;">{{ $idea->category->name }}</td>
            </tr>
        </table>
    </div>

    <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h4 style="margin-top: 0; color: #2d3748;">New Comment by {{ $commenter->name }}:</h4>
        <div style="background-color: #ffffff; padding: 15px; border-radius: 6px; margin-top: 10px;">
            <p style="color: #4a5568; margin: 0;">{{ $comment->content }}</p>
        </div>
        <p style="color: #6b7280; font-size: 12px; margin-top: 8px; text-align: right;">
            Commented {{ $comment->created_at->diffForHumans() }}
        </p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}/ideas/{{ $idea->id }}#comment-{{ $comment->id }}"
           style="background-color: #4a5568; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
            View Comment
        </a>
    </div>

    @if($recipient->id === $idea->user_id)
        <div class="alert alert-info" style="margin-top: 20px;">
            <p>You're receiving this notification because you're the author of this idea.</p>
        </div>
    @elseif($recipient->role === 'QA')
        <div class="alert alert-info" style="margin-top: 20px;">
            <p>You're receiving this notification because you're assigned as a QA reviewer.</p>
        </div>
    @endif

    <p>
        Best regards,<br>
        {{ config('app.name') }} Team
    </p>
@endsection

@section('footer')
    @parent
    <p style="font-size: 12px; color: #6b7280;">
        To stop receiving these notifications, you can update your notification preferences in your account settings.
    </p>
@endsection
