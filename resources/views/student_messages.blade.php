<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | SafeSpeak</title>
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
            <a href="{{ route('student.reports') }}" class="nav-link {{ request()->routeIs('student.reports*') ? 'active' : '' }}">My Reports</a>
            <a href="{{ route('student.messages') }}" class="nav-link {{ request()->routeIs('student.messages') ? 'active' : '' }}">
                Messages
                @if(isset($globalUnreadCount) && $globalUnreadCount > 0)
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

    <main class="dashboard-container">

        <section class="inbox-card">
            <h1 class="page-title" style="font-size:1.05rem;">Conversations</h1>
            <div class="report-list">
                @forelse($reports as $report)
                    @php
                        $unread = $report->messages
                            ->where('is_read', false)
                            ->where('user_id', '!=', auth()->id())
                            ->count();
                    @endphp
                    <a href="{{ route('student.messages', ['report' => $report->report_id]) }}"
                       class="report-item {{ $selectedReport && $selectedReport->report_id == $report->report_id ? 'active' : '' }}"
                       style="text-decoration: none; display: block; color: inherit;">
                        <div class="report-meta">
                            <strong>
                                {{ $report->case_id }}
                                @if($unread > 0)
                                    <span class="unread-dot"></span>
                                @endif
                            </strong>
                            <span>{{ $report->created_at->format('M d') }}</span>
                        </div>
                        <p class="summary-preview">{{ Str::limit($report->description, 38) }}</p>
                    </a>
                @empty
                    <div style="text-align:center; padding:40px 0; color:#b0b7c3; font-size:0.85rem;">
                        <i class="fas fa-comments" style="font-size:2rem; margin-bottom:10px; display:block; opacity:0.4;"></i>
                        No conversations yet.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="conversation-card">
            @if($selectedReport)
                <h1 class="page-title" style="font-size:1rem; font-family:'DM Mono',monospace;">
                    Chat: {{ $selectedReport->case_id }}
                </h1>

                <div class="chat-display" id="chatWindow">
                    <div class="chat-bubble student-msg">
                        <div class="bubble-text">
                            <small><b>Your Initial Report</b></small><br>
                            {!! nl2br(e($selectedReport->description)) !!}
                        </div>
                    </div>

                    @foreach($selectedReport->messages as $msg)
                        @php $isMe = ($msg->user_id == auth()->id()); @endphp
                        <div class="chat-bubble {{ $isMe ? 'student-msg' : 'guidance-msg' }}">
                            <div class="bubble-text">
                                {!! nl2br(e($msg->message_text)) !!}
                                <small>{{ $msg->created_at->format('h:i A') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('messages.send') }}" method="POST" class="chat-input-container">
                    @csrf
                    <input type="hidden" name="report_id" value="{{ $selectedReport->report_id }}">
                    <div class="input-wrapper">
                        <textarea name="message_text" placeholder="Type your message..." required autocomplete="off" rows="1" style="resize:none; overflow:hidden;"></textarea>
                        <button type="submit" class="send-btn">Send</button>
                    </div>
                </form>
            @else
                <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#b0b7c3; text-align:center; gap:10px;">
                    <i class="fas fa-comments" style="font-size:2.5rem; opacity:0.4;"></i>
                    <p style="font-size:0.88rem;">Select a conversation to start messaging.</p>
                </div>
            @endif
        </section>

    </main>

    @include('partials.student_settings')

    <script>
        const chatWindow = document.getElementById('chatWindow');
        if (chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight;

        document.addEventListener('input', function(e) {
            if (e.target.name === 'message_text') {
                e.target.style.height = 'auto';
                const maxHeight = 120;
                if (e.target.scrollHeight <= maxHeight) {
                    e.target.style.height = e.target.scrollHeight + 'px';
                    e.target.style.overflowY = 'hidden';
                } else {
                    e.target.style.height = maxHeight + 'px';
                    e.target.style.overflowY = 'auto';
                }
            }
        });
    </script>
</body>
</html>