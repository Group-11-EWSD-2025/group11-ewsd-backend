@extends('emails.layouts.base')

@section('title', 'Password Reset Notification')

@section('header', 'Password Reset Notification')

@section('content')
    <p>Hello {{ $user->name }},</p>

    <p>You are receiving this email because we received a password reset request for your account.</p>

    <div class="alert alert-info">
        <p>Your new password is:</p>
        <div style="background-color: #f8f9fa; padding: 10px; margin: 10px 0; border-radius: 5px; font-family: monospace;">
            {{ $newPassword }}
        </div>
    </div>

    <div class="alert alert-warning">
        <p>For security reasons, we strongly recommend changing this password after logging in.</p>
    </div>

    <p>If you did not request a password reset, please contact our support team immediately.</p>

    <p>
        Best regards,<br>
        {{ config('app.name') }} Team
    </p>
@endsection
