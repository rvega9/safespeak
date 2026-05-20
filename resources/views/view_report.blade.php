<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Details | SafeSpeak</title>
    <link rel="stylesheet" href="{{ asset('css/student_style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="student-body">
    <main class="content-container">
        <div class="main-display-card">
            <a href="{{ route('student.reports') }}" style="text-decoration:none; color:var(--primary-blue);">
                <i class="fas fa-arrow-left"></i> Back to My Reports
            </a>
            
            <h1 class="page-title" style="margin-top:20px;">Report Details</h1>
            
            <div style="text-align: left; background: #f8f9fa; padding: 25px; border-radius: 15px; border-left: 5px solid var(--primary-blue);">
                <p><strong>Case ID:</strong> {{ $report->case_id }}</p>
                <p><strong>Date Occurred:</strong> {{ \Carbon\Carbon::parse($report->occurred_at)->format('F d, Y') }}</p>
                <p><strong>Status:</strong> <span class="status-pill status-{{ $report->status }}">{{ $report->status }}</span></p>
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;">
                <p><strong>Description:</strong></p>
                <p style="white-space: pre-wrap; line-height: 1.6;">{{ $report->description }}</p>
            </div>
        </div>
    </main>
</body>
</html>