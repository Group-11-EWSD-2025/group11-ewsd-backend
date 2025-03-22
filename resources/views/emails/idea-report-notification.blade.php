@extends('emails.layouts.base')

@section('title', 'Idea Report Notification')

@section('header', 'Idea Reported for Review')

@section('content')
    <p>Hello {{ $qmUser->name }},</p>

    <p>An idea has been reported and requires your immediate attention.</p>

    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin-top: 0; color: #2d3748;">Idea Details:</h3>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #4a5568; width: 120px;">Title:</td>
                <td style="padding: 8px 0; color: #2d3748;"><strong>{{ $idea->title }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #4a5568;">Author:</td>
                <td style="padding: 8px 0; color: #2d3748;">{{ $idea->user->name }}</td>
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

    <div style="background-color: #fee2e2; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h4 style="margin-top: 0; color: #991b1b;">Report Details:</h4>
        <div style="background-color: #ffffff; padding: 15px; border-radius: 6px; margin-top: 10px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #4a5568; width: 120px;">Reported By:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $reporter->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #4a5568;">Reason:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $report->reason }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #4a5568;">Description:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $report->description }}</td>
                </tr>
            </table>
        </div>
        <p style="color: #6b7280; font-size: 12px; margin-top: 8px; text-align: right;">
            Reported {{ $report->created_at->diffForHumans() }}
        </p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}/ideas/{{ $idea->id }}"
           style="background-color: #dc2626; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
            Review Reported Idea
        </a>
    </div>

    <div class="alert alert-warning" style="margin-top: 20px;">
        <p>Please review this report and take appropriate action as soon as possible.</p>
        <ul style="margin-top: 10px;">
            <li>Review the idea content</li>
            <li>Assess the report reason</li>
            <li>Take necessary moderation actions</li>
            <li>Update the report status</li>
        </ul>
    </div>

    <p>
        Best regards,<br>
        {{ config('app.name') }} Team
    </p>
@endsection

@section('footer')
    @parent
    <p style="font-size: 12px; color: #6b7280;">
        You're receiving this email because you are designated as a Quality Manager.
    </p>
@endsection
