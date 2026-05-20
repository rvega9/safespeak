<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | SafeSpeak</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo2.png') }}">
    <link rel="stylesheet" href="{{ asset('css/student_style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="student-body">

    <nav class="top-nav">
        <div class="nav-logo">
            <img src="{{ asset('assets/logo2.png') }}" alt="Logo" class="nav-logo-img">
            <span class="brand-text">
                <span class="safe-text">SAFE</span><span class="speak-text">SPEAK</span>
            </span>
        </div>
        <div class="nav-center">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Report Concern</a>
            <a href="{{ route('student.reports') }}" class="nav-link {{ request()->routeIs('student.reports') ? 'active' : '' }}">My Reports</a>
            <a href="{{ route('student.messages') }}" class="nav-link {{ request()->routeIs('student.messages') ? 'active' : '' }}">
                Messages
                @if(!empty($globalUnreadCount) && $globalUnreadCount > 0)
                    <span class="nav-badge">{{ $globalUnreadCount }}</span>
                @endif
            </a>
        </div>
        <div class="nav-user">
            <span class="nav-greeting">Hello, <span class="user-highlight">{{ auth()->user()->full_name }}</span>!</span>
            <i class="fas fa-cog settings-icon" onclick="openSettings()"></i>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </nav>

    <main class="content-container">
        <div class="main-display-card">
            <h1 class="page-title">Share your concern</h1>

            @if(session('success'))
                <div style="background: #d1edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align:center; border: 1px solid #c3e6cb;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('report.submit') }}" method="POST" class="concern-form">
                @csrf
                <div class="input-inline-group">
                    <label>When did this occur?</label>
                    <input type="date" name="incident_date" required value="{{ date('Y-m-d') }}">
                </div>
                <div class="input-block-group">
                    <label>Tell us about your concern</label>
                    <textarea name="description" placeholder="Type your message here..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="submit-btn-premium">Submit Report</button>
                </div>
            </form>
        </div>
    </main>

    @include('partials.student_settings')

</body>
</html>