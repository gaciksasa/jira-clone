<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Project Invitation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #0052cc;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            background-color: #f9f9f9;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0052cc;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            margin: 20px 0;
        }
        .footer {
            font-size: 12px;
            color: #777;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Project Invitation</h2>
        </div>
        <div class="content">
            <p>Hello,</p>
            
            <p>{{ $inviter->name }} has invited you to join the project "{{ $project->name }}" in the {{ config('app.name') }} system.</p>
            
            <p>If you already have an account, you'll be added to the project when you accept this invitation. If you don't have an account yet, you'll be guided through the registration process.</p>
            
            <p style="text-align: center;">
                <a href="{{ route('invitation.accept', $token) }}" class="button">Accept Invitation</a>
            </p>
            
            <p>If you're having trouble with the button above, copy and paste this URL into your browser:</p>
            <p>{{ route('invitation.accept', $token) }}</p>
            
            <p>This invitation will expire in 7 days.</p>
            
            <p>Best regards,<br>
            The {{ config('app.name') }} Team</p>
        </div>
        <div class="footer">
            <p>If you did not request this invitation, you can safely ignore this email.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>