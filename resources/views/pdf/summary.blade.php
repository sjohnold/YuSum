<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Video Summary - {{ $summary->video_id }}</title>
    <style>
        body { font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; line-height: 1.6; color: #333; padding: 40px; }
        .header { border-bottom: 2px solid #eee; margin-bottom: 30px; padding-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; color: #000; }
        .meta { font-size: 12px; color: #666; margin-top: 5px; }
        .summary-content { font-size: 14px; }
        .footer { margin-top: 50px; font-size: 10px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
        h1, h2, h3 { color: #000; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
        code { font-family: 'Courier New', Courier, monospace; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">YouTube Video Summary</div>
        <div class="meta">
            Video ID: {{ $summary->video_id }}<br>
            Generated on: {{ $summary->created_at->format('M d, Y H:i') }}<br>
            Tokens used: {{ $summary->tokens_used }}
        </div>
    </div>

    <div class="summary-content">
        {!! \Illuminate\Support\Str::markdown($summary->summary, ['html_input' => 'strip']) !!}
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} YouTube Summarizer SaaS. Generated for {{ $userEmail }}.
    </div>
</body>
</html>
