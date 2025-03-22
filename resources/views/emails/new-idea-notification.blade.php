@extends('emails.layouts.base')

@section('title', 'New Idea Notification')

@section('header', 'New Idea Submitted')

@section('content')
    <p>Hello {{ $qaUser->name }},</p>

    <p>A new idea has been submitted and requires your review.</p>

    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin-top: 0; color: #2d3748;">Idea Details:</h3>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #4a5568; width: 120px;">Title:</td>
                <td style="padding: 8px 0; color: #2d3748;"><strong>{{ $idea->title }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #4a5568;">Created By:</td>
                <td style="padding: 8px 0; color: #2d3748;">{{ $creator->name }}</td>
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
        <h4 style="margin-top: 0; color: #2d3748;">Description:</h4>
        <p style="color: #4a5568; margin-bottom: 0;">{{ $idea->description }}</p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}/ideas/{{ $idea->id }}"
           style="background-color: #4a5568; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
            View Idea Details
        </a>
    </div>

    <div class="alert alert-info" style="margin-top: 20px;">
        <p>Please review this idea and provide your feedback as soon as possible.</p>
    </div>

    <p>
        Best regards,<br>
        {{ config('app.name') }} Team
    </p>
@endsection

@section('footer')
    @parent
    <p style="font-size: 12px; color: #6b7280;">
        You're receiving this email because you are designated as a QA reviewer.
    </p>
@endsection
