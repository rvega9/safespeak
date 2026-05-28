<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | SafeSpeak</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo2.png') }}">
    <link rel="stylesheet" href="{{ asset('css/student_style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    {{-- CSRF token for fetch() requests --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                {{-- Badge: updated in real-time by JS --}}
                <span class="nav-badge" id="navUnreadBadge" style="{{ (isset($globalUnreadCount) && $globalUnreadCount > 0) ? '' : 'display:none;' }}">
                    {{ $globalUnreadCount ?? 0 }}
                </span>
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

        {{-- LEFT: Conversation list --}}
        <section class="inbox-card">
            <h1 class="page-title" style="font-size:1.05rem;">Conversations</h1>
            <div class="report-list" id="conversationList">
                @forelse($reports as $report)
                    @php
                        $unread = $report->messages
                            ->where('is_read', false)
                            ->where('user_id', '!=', auth()->id())
                            ->count();
                    @endphp
                    <a href="{{ route('student.messages', ['report' => $report->report_id]) }}"
                       class="report-item {{ $selectedReport && $selectedReport->report_id == $report->report_id ? 'active' : '' }}"
                       data-report-id="{{ $report->report_id }}"
                       style="text-decoration: none; display: block; color: inherit;">
                        <div class="report-meta">
                            <strong>
                                {{ $report->case_id }}
                                {{-- Unread dot: toggled by JS --}}
                                <span class="unread-dot" id="dot-{{ $report->report_id }}"
                                      style="{{ $unread > 0 ? '' : 'display:none;' }}"></span>
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

        {{-- RIGHT: Chat window --}}
        <section class="conversation-card">
            @if($selectedReport)
                <h1 class="page-title" style="font-size:1rem; font-family:'DM Mono',monospace;">
                    Chat: {{ $selectedReport->case_id }}
                    {{-- Live indicator --}}
                    <span id="liveIndicator" style="font-size:0.7rem; font-family:sans-serif; font-weight:400;
                          color:#22c55e; margin-left:8px; opacity:0;">
                        <span style="display:inline-block; width:7px; height:7px; border-radius:50%;
                                     background:#22c55e; margin-right:3px; vertical-align:middle;"></span>Live
                    </span>
                </h1>

                <div class="chat-display" id="chatWindow">
                    {{-- Initial report as first bubble --}}
                    <div class="chat-bubble student-msg">
                        <div class="bubble-text">
                            <small><b>Your Initial Report</b></small><br>
                            {!! nl2br(e($selectedReport->description)) !!}
                        </div>
                    </div>

                    {{-- Existing messages rendered server-side on page load --}}
                    @php $lastMessageId = 0; @endphp
                    @foreach($selectedReport->messages as $msg)
                        @php
                            $isMe = ($msg->user_id == auth()->id());
                            if ($msg->message_id > $lastMessageId) $lastMessageId = $msg->message_id;
                        @endphp
                        <div class="chat-bubble {{ $isMe ? 'student-msg' : 'guidance-msg' }}">
                            <div class="bubble-text">
                                {!! nl2br(e($msg->message_text)) !!}
                                <small>{{ $msg->created_at->format('h:i A') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Chat input --}}
                <div class="chat-input-container">
                    <input type="hidden" id="reportId" value="{{ $selectedReport->report_id }}">
                    <div class="input-wrapper">
                        <textarea id="messageInput"
                                  placeholder="Type your message..."
                                  autocomplete="off"
                                  rows="1"
                                  style="resize:none; overflow:hidden;"></textarea>
                        <button id="sendBtn" class="send-btn" onclick="sendMessage()">Send</button>
                    </div>
                    {{-- Sending indicator --}}
                    <div id="sendingIndicator" style="font-size:0.75rem; color:#94a3b8; padding:2px 4px; display:none;">
                        <i class="fas fa-circle-notch fa-spin"></i> Sending...
                    </div>
                </div>

            @else
                <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center;
                            color:#b0b7c3; text-align:center; gap:10px;">
                    <i class="fas fa-comments" style="font-size:2.5rem; opacity:0.4;"></i>
                    <p style="font-size:0.88rem;">Select a conversation to start messaging.</p>
                </div>
            @endif
        </section>

    </main>

    @include('partials.student_settings')

    <script>
    // ─── CONFIG ───────────────────────────────────────────────────────────────
    const REPORT_ID    = {{ $selectedReport ? $selectedReport->report_id : 'null' }};
    const CURRENT_USER = {{ auth()->id() }};
    const POLL_URL     = "{{ route('messages.poll', ['reportId' => $selectedReport ? $selectedReport->report_id : 0]) }}";
    const SEND_URL     = "{{ route('messages.send') }}";
    const CSRF         = document.querySelector('meta[name="csrf-token"]').content;

    // Track the highest message_id we've rendered so polling only fetches new ones
    let lastMessageId = {{ $lastMessageId ?? 0 }};
    let pollTimer     = null;
    let isSending     = false;

    // ─── SCROLL HELPER ────────────────────────────────────────────────────────
    function scrollToBottom() {
        const win = document.getElementById('chatWindow');
        if (win) win.scrollTop = win.scrollHeight;
    }

    // ─── BUILD A CHAT BUBBLE ──────────────────────────────────────────────────
    function buildBubble(text, isMine, time) {
        const div = document.createElement('div');
        div.className = 'chat-bubble ' + (isMine ? 'student-msg' : 'guidance-msg');

        // Convert newlines to <br> (same as nl2br in blade)
        const escaped = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');

        div.innerHTML = `<div class="bubble-text">${escaped}<small>${time}</small></div>`;
        return div;
    }

    // ─── SEND MESSAGE ─────────────────────────────────────────────────────────
    async function sendMessage() {
        if (!REPORT_ID || isSending) return;

        const input = document.getElementById('messageInput');
        const text  = input.value.trim();
        if (!text) return;

        isSending = true;
        document.getElementById('sendBtn').disabled = true;
        document.getElementById('sendingIndicator').style.display = 'block';

        try {
            const res  = await fetch(SEND_URL, {
                method:  'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept':       'application/json',
                },
                body: JSON.stringify({
                    report_id:    REPORT_ID,
                    message_text: text,
                }),
            });

            const data = await res.json();

            if (data.success) {
                // Render the bubble immediately (don't wait for poll)
                const bubble = buildBubble(data.text, true, data.time);
                document.getElementById('chatWindow').appendChild(bubble);
                lastMessageId = Math.max(lastMessageId, data.message_id);
                input.value = '';
                input.style.height = 'auto';
                scrollToBottom();
            } else {
                alert('Failed to send message. Please try again.');
            }
        } catch (err) {
            console.error('Send error:', err);
            alert('Network error. Please check your connection.');
        } finally {
            isSending = false;
            document.getElementById('sendBtn').disabled = false;
            document.getElementById('sendingIndicator').style.display = 'none';
        }
    }

    // ─── POLL FOR NEW MESSAGES ────────────────────────────────────────────────
    async function pollMessages() {
        if (!REPORT_ID) return;

        try {
            const res  = await fetch(`${POLL_URL}?after=${lastMessageId}`, {
                headers: {
                    'Accept':       'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
            });

            const data = await res.json();

            if (data.messages && data.messages.length > 0) {
                const win = document.getElementById('chatWindow');
                data.messages.forEach(msg => {
                    // Skip messages we sent ourselves (already rendered via sendMessage())
                    if (msg.user_id === CURRENT_USER) {
                        lastMessageId = Math.max(lastMessageId, msg.message_id);
                        return;
                    }
                    const bubble = buildBubble(msg.text, msg.is_mine, msg.time);
                    win.appendChild(bubble);
                    lastMessageId = Math.max(lastMessageId, msg.message_id);
                });
                scrollToBottom();
            }

            // Update nav unread badge (guidance replies mark themselves as read on poll)
            updateNavBadge(data.unread_count ?? 0);

            // Show live indicator
            showLive();

        } catch (err) {
            // Silently fail — will retry on next tick
            console.warn('Poll error:', err);
        }
    }

    // ─── NAV BADGE ────────────────────────────────────────────────────────────
    function updateNavBadge(count) {
        const badge = document.getElementById('navUnreadBadge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }
    }

    // ─── LIVE INDICATOR ──────────────────────────────────────────────────────
    let liveTimer = null;
    function showLive() {
        const el = document.getElementById('liveIndicator');
        if (!el) return;
        el.style.opacity = '1';
        clearTimeout(liveTimer);
        liveTimer = setTimeout(() => el.style.opacity = '0', 3000);
    }

    // ─── SEND ON ENTER (Shift+Enter = newline) ────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('messageInput');
        if (!input) return;

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Auto-resize textarea
        input.addEventListener('input', function () {
            this.style.height = 'auto';
            const maxHeight = 120;
            if (this.scrollHeight <= maxHeight) {
                this.style.height = this.scrollHeight + 'px';
                this.style.overflowY = 'hidden';
            } else {
                this.style.height = maxHeight + 'px';
                this.style.overflowY = 'auto';
            }
        });

        // Initial scroll to bottom
        scrollToBottom();

        // Start polling every 3 seconds if a report is open
        if (REPORT_ID) {
            pollMessages(); // immediate first check
            pollTimer = setInterval(pollMessages, 3000);
        }
    });

    // Stop polling when user leaves the page (avoid orphan intervals)
    window.addEventListener('beforeunload', () => clearInterval(pollTimer));
    </script>
</body>
</html>