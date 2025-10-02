<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Comment</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; text-align: center; }
        .content { padding: 30px; background: #f8f9fa; border-radius: 8px; margin-top: 20px; }
        .comment-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #667eea; }
        .button { display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
        .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
<div class="header">
    <h1>💬 New Comment</h1>
</div>
<div class="content">
    <p><strong>{{ $commenter->name }}</strong> commented on a task:</p>

    <div class="comment-box">
        <h3 style="margin: 0 0 15px 0;">{{ $task->title }}</h3>
        <p style="margin: 0; background: #f8f9fa; padding: 15px; border-radius: 6px;">
            "{{ $comment->comment }}"
        </p>
        <p style="margin: 15px 0 0 0; font-size: 14px; color: #6b7280;">
            Project: {{ $task->project->name }}
        </p>
    </div>

    <a href="{{ config('app.frontend_url') }}/projects/{{ $task->project_id }}" class="button">View Task</a>
</div>
<div class="footer">
    <p>© {{ date('Y') }} CloudTask Pro. All rights reserved.</p>
</div>
</body>
</html>
