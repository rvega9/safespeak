<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | SafeSpeak</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo2.png') }}">
    <link rel="stylesheet" href="{{ asset('css/student_style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <span class="nav-badge" id="navUnreadBadge"
                      style="{{ (isset($globalUnreadCount) && $globalUnreadCount > 0) ? '' : 'display:none;' }}">
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
                        $latestMsg    = $report->messages->last();
                        $unread       = $report->messages->where('is_read', false)->where('user_id', '!=', auth()->id())->count();
                        $preview      = $latestMsg ? Str::limit($latestMsg->message_text, 38) : Str::limit($report->description, 38);
                        $previewLabel = $latestMsg ? ($latestMsg->user_id == auth()->id() ? 'You: ' : 'Guidance: ') : '';
                        $lastActivity = $latestMsg ? $latestMsg->created_at->timestamp : $report->created_at->timestamp;
                    @endphp
                    <a href="{{ route('student.messages', ['report' => $report->report_id]) }}"
                       class="report-item {{ $selectedReport && $selectedReport->report_id == $report->report_id ? 'active' : '' }}"
                       id="conv-{{ $report->report_id }}"
                       data-report-id="{{ $report->report_id }}"
                       data-last-activity="{{ $lastActivity }}"
                       style="text-decoration:none; display:block; color:inherit;">
                        <div class="report-meta">
                            <strong id="conv-title-{{ $report->report_id }}"
                                    style="{{ $unread > 0 ? 'font-weight:700;' : '' }}">
                                {{ $report->case_id }}
                                {{-- Numbered unread badge (same style as guidance) --}}
                                <span class="unread-badge" id="dot-{{ $report->report_id }}"
                                      style="{{ $unread > 0 ? '' : 'display:none;' }}">
                                    {{ $unread ?: '' }}
                                </span>
                            </strong>
                            <span id="conv-time-{{ $report->report_id }}"
                                  style="font-size:0.75rem; color:#94a3b8;">
                                {{ $latestMsg ? $latestMsg->created_at->diffForHumans() : $report->created_at->format('M d') }}
                            </span>
                        </div>
                        <p class="summary-preview" id="conv-preview-{{ $report->report_id }}"
                           style="{{ $unread > 0 ? 'font-weight:700;' : '' }}">
                            <span style="color:#94a3b8; font-size:0.8rem;">{{ $previewLabel }}</span>{{ $preview }}
                        </p>
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
                    <span id="liveIndicator" style="font-size:0.7rem; font-family:sans-serif; font-weight:400;
                          color:#22c55e; margin-left:8px; opacity:0; transition:opacity 0.4s;">
                        <span style="display:inline-block; width:7px; height:7px; border-radius:50%;
                                     background:#22c55e; margin-right:3px; vertical-align:middle;"></span>Live
                    </span>
                </h1>

                <div class="chat-display" id="chatWindow">
                    <div class="chat-bubble student-msg">
                        <div class="bubble-text">
                            <small><b>Your Initial Report</b></small><br>
                            {!! nl2br(e($selectedReport->description)) !!}
                        </div>
                    </div>

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
    const REPORT_ID      = {{ $selectedReport ? $selectedReport->report_id : 'null' }};
    const CURRENT_USER   = {{ auth()->id() }};
    const POLL_URL       = "{{ route('messages.poll', ['reportId' => $selectedReport ? $selectedReport->report_id : 0]) }}";
    const SIDEBAR_URL    = "{{ route('messages.sidebar-poll') }}";
    const SEND_URL       = "{{ route('messages.send') }}";
    const CSRF           = document.querySelector('meta[name="csrf-token"]').content;

    let lastMessageId    = {{ $lastMessageId ?? 0 }};
    let pollTimer        = null;
    let sidebarTimer     = null;
    let isSending        = false;

    // ─── SCROLL ───────────────────────────────────────────────────────────────
    function scrollToBottom() {
        const win = document.getElementById('chatWindow');
        if (win) win.scrollTop = win.scrollHeight;
    }

    // ─── BUILD BUBBLE ─────────────────────────────────────────────────────────
    function buildBubble(text, isMine, time) {
        const div = document.createElement('div');
        div.className = 'chat-bubble ' + (isMine ? 'student-msg' : 'guidance-msg');
        const escaped = text
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\n/g, '<br>');
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
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ report_id: REPORT_ID, message_text: text }),
            });
            const data = await res.json();

            if (data.success) {
                document.getElementById('chatWindow').appendChild(buildBubble(data.text, true, data.time));
                lastMessageId = Math.max(lastMessageId, data.message_id);
                input.value = '';
                input.style.height = 'auto';
                scrollToBottom();
                // Update this conversation's preview immediately in the sidebar
                updateSidebarItem(REPORT_ID, 'You: ' + data.text, 'Just now', 0, Date.now() / 1000);
            } else {
                alert('Failed to send message. Please try again.');
            }
        } catch (err) {
            alert('Network error. Please check your connection.');
        } finally {
            isSending = false;
            document.getElementById('sendBtn').disabled = false;
            document.getElementById('sendingIndicator').style.display = 'none';
        }
    }

    // ─── POLL CHAT WINDOW (new messages in open conversation) ─────────────────
    async function pollMessages() {
        if (!REPORT_ID) return;
        try {
            const res  = await fetch(`${POLL_URL}?after=${lastMessageId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();

            if (data.messages && data.messages.length > 0) {
                const win = document.getElementById('chatWindow');
                data.messages.forEach(msg => {
                    if (msg.user_id === CURRENT_USER) { lastMessageId = Math.max(lastMessageId, msg.message_id); return; }
                    win.appendChild(buildBubble(msg.text, false, msg.time));
                    lastMessageId = Math.max(lastMessageId, msg.message_id);
                });
                scrollToBottom();
            }
            updateNavBadge(data.unread_count ?? 0);
            showLive();
        } catch (err) { console.warn('Poll error:', err); }
    }

    // ─── POLL SIDEBAR (all conversations — previews, unread badges, ordering) ─
    async function pollSidebar() {
        try {
            const res  = await fetch(SIDEBAR_URL, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            });
            const data = await res.json();
            if (!data.conversations) return;

            Object.values(data.conversations).forEach(conv => {
                const label       = conv.latest_sender ? conv.latest_sender + ': ' : '';
                const previewText = conv.latest_text   ? label + conv.latest_text : null;
                updateSidebarItem(conv.report_id, previewText, conv.latest_time,
                                  conv.unread, conv.last_activity);
            });

            sortSidebar();
            updateNavBadge(data.total_unread ?? 0);

        } catch (err) { console.warn('Sidebar poll error:', err); }
    }

    // ─── UPDATE ONE SIDEBAR ITEM ──────────────────────────────────────────────
    function updateSidebarItem(reportId, previewText, timeText, unreadCount, lastActivity) {
        const item      = document.getElementById('conv-' + reportId);
        const badgeEl   = document.getElementById('dot-' + reportId);   // reusing dot id for badge
        const titleEl   = document.getElementById('conv-title-' + reportId);
        const previewEl = document.getElementById('conv-preview-' + reportId);
        const timeEl    = document.getElementById('conv-time-' + reportId);
        if (!item) return;

        // Always update last-activity for sorting
        if (lastActivity) item.dataset.lastActivity = lastActivity;

        const isActive  = (parseInt(reportId) === REPORT_ID);
        const hasUnread = (!isActive && unreadCount > 0);

        // Numbered unread badge
        if (badgeEl) {
            if (hasUnread) {
                badgeEl.textContent    = unreadCount;
                badgeEl.style.display  = '';
            } else {
                badgeEl.style.display  = 'none';
            }
        }

        // Bold case ID + preview when unread
        if (titleEl)   titleEl.style.fontWeight   = hasUnread ? '700' : '400';
        if (previewEl) previewEl.style.fontWeight = hasUnread ? '700' : '400';

        // Always overwrite preview text with whatever the server says is latest
        if (previewEl && previewText) {
            const limit   = 38;
            const trimmed = previewText.length > limit ? previewText.substring(0, limit) + '…' : previewText;
            previewEl.textContent = trimmed;
        }

        // Always update time label
        if (timeEl && timeText) timeEl.textContent = timeText;
    }

    // ─── SORT SIDEBAR BY LAST ACTIVITY (highest = most recent = top) ─────────
    function sortSidebar() {
        const list = document.getElementById('conversationList');
        if (!list) return;
        const items = Array.from(list.querySelectorAll('a.report-item'));
        items.sort((a, b) => parseFloat(b.dataset.lastActivity || 0) - parseFloat(a.dataset.lastActivity || 0));
        items.forEach(item => list.appendChild(item));
    }

    // ─── NAV BADGE ────────────────────────────────────────────────────────────
    function updateNavBadge(count) {
        const badge = document.getElementById('navUnreadBadge');
        if (!badge) return;
        if (count > 0) { badge.textContent = count; badge.style.display = ''; }
        else badge.style.display = 'none';
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

    // ─── INIT ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('messageInput');
        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
            });
            input.addEventListener('input', function () {
                this.style.height = 'auto';
                const max = 120;
                this.style.height = Math.min(this.scrollHeight, max) + 'px';
                this.style.overflowY = this.scrollHeight > max ? 'auto' : 'hidden';
            });
        }

        scrollToBottom();
        sortSidebar(); // initial sort on page load

        // Chat window polling (only when a conversation is open)
        if (REPORT_ID) {
            pollMessages();
            pollTimer = setInterval(pollMessages, 3000);
        }

        // Sidebar polling — always runs so unread dots update even without open chat
        pollSidebar();
        sidebarTimer = setInterval(pollSidebar, 3000);
    });

    window.addEventListener('beforeunload', () => {
        clearInterval(pollTimer);
        clearInterval(sidebarTimer);
    });
    </script>
</body>
</html>