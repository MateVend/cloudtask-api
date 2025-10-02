<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Assigned</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 20px -30px;
        }
        h1 {
            margin: 0;
            font-size: 24px;
        }
        .task-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
        .label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .value {
            margin-top: 5px;
            font-size: 16px;
        }
        .priority {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .priority-urgent { background: #fee2e2; color: #991b1b; }
        .priority-high { background: #fef3c7; color: #92400e; }
        .priority-medium { background: #dbeafe; color: #1e40af; }
        .priority-low { background: #e5e7eb; color: #374151; }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>📋 New Task Assigned</h1>
    </div>

    <p>Hi there!</p>
    <p><strong>{{ $assignerName }}</strong> has assigned you a new task in <strong>{{ $projectName }}</strong>.</p>

    <div class="task-info">
        <div style="margin-bottom: 15px;">
            <div class="label">Task Title</div>
            <div class="value">{{ $taskTitle }}</div>
        </div>

        @if($taskDescription)
            <div style="margin-bottom: 15px;">
                <div class="label">Description</div>
                <div class="value">{{ $taskDescription }}</div>
            </div>
        @endif

        <div style="margin-bottom: 15px;">
            <div class="label">Priority</div>
            <div class="value">
                <span class="priority priority-{{ $priority }}">{{ ucfirst($priority) }}</span>
            </div>
        </div>

        @if($dueDate)
            <div>
                <div class="label">Due Date</div>
                <div class="value">{{ $dueDate }}</div>
            </div>
        @endif
    </div>

    <a href="{{ config('app.frontend_url') }}/projects" class="button">View Task</a>

    <div class="footer">
        <p>You're receiving this email because you're a member of CloudTask Pro.</p>
        <p>© {{ date('Y') }} CloudTask Pro. All rights reserved.</p>
    </div>
</div>
</body>
</html>
