<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; text-align: center; }
        .content { padding: 30px; background: #f8f9fa; border-radius: 8px; margin-top: 20px; }
        .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
        .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 14px; }
        ul { padding-left: 20px; }
        li { margin-bottom: 8px; }
    </style>
</head>
<body>
<div class="header">
    <h1>🎉 Welcome to CloudTask Pro!</h1>
</div>
<div class="content">
    <p>Hi {{ $user->name }},</p>
    <p>Thank you for joining CloudTask Pro! We're excited to have you on board.</p>
    <p><strong>Here's what you can do:</strong></p>
    <ul>
        <li>📁 Create and manage unlimited projects</li>
        <li>✅ Track tasks with our intuitive Kanban board</li>
        <li>👥 Invite team members to collaborate</li>
        <li>📊 View analytics and insights</li>
        <li>⚙️ Customize your workspace</li>
    </ul>
    <p>Get started by creating your first project!</p>
    <a href="{{ config('app.frontend_url') }}/dashboard" class="button">Go to Dashboard</a>
    <p style="margin-top: 30px; font-size: 14px; color: #6b7280;">
        Need help getting started? Check out our <a href="{{ config('app.frontend_url') }}" style="color: #667eea;">documentation</a> or reply to this email.
    </p>
</div>
<div class="footer">
    <p>© {{ date('Y') }} CloudTask Pro. All rights reserved.</p>
    <p>You're receiving this email because you registered for CloudTask Pro.</p>
</div>
</body>
</html>
