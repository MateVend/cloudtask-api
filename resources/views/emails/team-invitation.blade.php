<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Invitation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; text-align: center; }
        .content { padding: 30px; background: #f8f9fa; border-radius: 8px; margin-top: 20px; }
        .org-card { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
        .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
        .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
<div class="header">
    <h1>🎉 You've Been Invited!</h1>
</div>
<div class="content">
    <p>Hi {{ $invitedUser->name }},</p>
    <p><strong>{{ $inviter->name }}</strong> has invited you to join their team on CloudTask Pro.</p>

    <div class="org-card">
        <h3 style="margin: 0 0 10px 0;">{{ $organization->name }}</h3>
        @if($organization->description)
            <p style="margin: 0; color: #6b7280;">{{ $organization->description }}</p>
        @endif
    </div>

    <p>Start collaborating with your team on projects and tasks!</p>
    <a href="{{ config('app.frontend_url') }}/login" class="button">Accept Invitation</a>

    <p style="margin-top: 30px; font-size: 14px; color: #6b7280;">
        If you don't have an account yet, you'll be able to create one after clicking the button above.
    </p>
</div>
<div class="footer">
    <p>© {{ date('Y') }} CloudTask Pro. All rights reserved.</p>
    <p>You're receiving this email because {{ $inviter->name }} invited you to CloudTask Pro.</p>
</div>
</body>
</html>
