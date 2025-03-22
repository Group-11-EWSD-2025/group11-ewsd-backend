@extends('emails.layouts.base')

@section('title', 'Welcome to ' . config('app.name'))

@section('header', 'Your Account Has Been Created')

@section('content')
    <p>Hello {{ $user->name }},</p>

    <p>Welcome to {{ config('app.name') }}! An account has been created for you by {{ $creator->name }}.</p>

    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h3 style="margin-top: 0; color: #2d3748;">Your Account Details:</h3>

        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 8px 0; color: #4a5568; width: 120px;">Name:</td>
                <td style="padding: 8px 0; color: #2d3748;"><strong>{{ $user->name }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #4a5568;">Email:</td>
                <td style="padding: 8px 0; color: #2d3748;">{{ $user->email }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; color: #4a5568;">Role:</td>
                <td style="padding: 8px 0; color: #2d3748;">{{ ucfirst($user->role) }}</td>
            </tr>
            @if($user->department)
            <tr>
                <td style="padding: 8px 0; color: #4a5568;">Department:</td>
                <td style="padding: 8px 0; color: #2d3748;">{{ $user->department->name }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div style="background-color: #e8f4fd; padding: 20px; border-radius: 8px; margin: 20px 0;">
        <h4 style="margin-top: 0; color: #1e40af;">Login Credentials:</h4>
        <div style="background-color: #ffffff; padding: 15px; border-radius: 6px; margin-top: 10px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #4a5568; width: 120px;">Email:</td>
                    <td style="padding: 8px 0; color: #2d3748;">{{ $user->email }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #4a5568;">Password:</td>
                    <td style="padding: 8px 0; color: #2d3748; font-family: monospace;">{{ $password }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ config('app.url') }}/login"
           style="background-color: #4a5568; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
            Login to Your Account
        </a>
    </div>

    <div class="alert alert-warning" style="margin-top: 20px;">
        <p><strong>Important Security Notice:</strong></p>
        <ul style="margin-top: 10px;">
            <li>Please change your password immediately after logging in</li>
            <li>Keep your login credentials secure</li>
            <li>Never share your password with others</li>
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
        If you did not expect this account creation, please contact your administrator immediately.
    </p>
@endsection