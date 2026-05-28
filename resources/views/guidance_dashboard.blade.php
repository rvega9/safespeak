<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guidance Dashboard | SafeSpeak</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo2.png') }}">
    <link rel="stylesheet" href="{{ asset('css/guidance_style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    {{-- CSRF token for fetch() requests --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="guidance-body">

    <nav class="top-nav">
        <div class="nav-logo">
            <img src="{{ asset('assets/logo2.png') }}" alt="Logo" class="nav-logo-img">
            <span class="brand-text">
                <span class="safe-text">SAFE</span><span class="speak-text">SPEAK</span>
            </span>
        </div>

        {{-- Stats inside nav --}}
        <div class="nav-stats">
            <div class="nav-stat-item total">
                <i class="fas fa-file-alt"></i>
                <span class="nav-stat-number">{{ $stats['total'] }}</span>
                <span class="nav-stat-label">Total</span>
            </div>
            <div class="nav-stat-item pending">
                <i class="fas fa-clock"></i>
                <span class="nav-stat-number">{{ $stats['pending'] }}</span>
                <span class="nav-stat-label">Pending</span>
            </div>
            <div class="nav-stat-item in-progress">
                <i class="fas fa-spinner"></i>
                <span class="nav-stat-number">{{ $stats['in_progress'] }}</span>
                <span class="nav-stat-label">In Progress</span>
            </div>
            <div class="nav-stat-item resolved">
                <i class="fas fa-check-circle"></i>
                <span class="nav-stat-number">{{ $stats['resolved'] }}</span>
                <span class="nav-stat-label">Resolved</span>
            </div>
        </div>

        <div class="nav-user">
            <span>Hello, <span class="user-highlight">{{ auth()->user()->full_name }}</span>!</span>
            <i class="fas fa-cog settings-icon" onclick="openSettings()"></i>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </nav>

    <main class="dashboard-container">

        {{-- LEFT: INBOX --}}
        <section class="main-display-card">

            {{-- Section Toggle --}}
            <div class="section-toggle">
                <a href="{{ route('guidance.dashboard', ['section' => 'inbox']) }}"
                   class="section-btn {{ $section === 'inbox' ? 'active' : '' }}">
                    <i class="fas fa-inbox"></i> Inbox
                    <span class="badge">{{ $stats['total'] }}</span>
                </a>
                <a href="{{ route('guidance.dashboard', ['section' => 'archived']) }}"
                   class="section-btn {{ $section === 'archived' ? 'active' : '' }}">
                    <i class="fas fa-archive"></i> Archived
                    <span class="badge">{{ $stats['archived'] }}</span>
                </a>
            </div>

            {{-- Search --}}
            <div class="search-bar">
                <form method="GET" action="{{ route('guidance.dashboard') }}">
                    <input type="hidden" name="section" value="{{ $section }}">
                    @if(request('report'))
                        <input type="hidden" name="report" value="{{ request('report') }}">
                    @endif
                    @if(request('filter'))
                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                    @endif
                    <input type="text" name="search" placeholder="Search case ID or keyword..." value="{{ $search }}">
                </form>
            </div>

            {{-- Filter Tabs (inbox only) --}}
            @if($section === 'inbox')
            <div class="filter-tabs">
                @foreach(['all' => 'All', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'resolved' => 'Resolved'] as $key => $label)
                    <a href="{{ route('guidance.dashboard', array_merge(request()->except('filter'), ['filter' => $key])) }}"
                       class="filter-tab {{ $filter === $key ? 'active' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            @endif

            {{-- Report List --}}
            <div class="report-list">
                @forelse($reports as $report)
                    @php
                        $unreadCount = $report->messages
                            ->where('is_read', false)
                            ->where('user_id', '!=', auth()->id())
                            ->count();
                    @endphp
                    <a href="{{ route('guidance.dashboard', array_merge(request()->query(), ['report' => $report->report_id])) }}"
                       class="report-item {{ $selectedReport && $selectedReport->report_id == $report->report_id ? 'active' : '' }}"
                       data-report-id="{{ $report->report_id }}">
                        <div class="report-meta">
                            <strong>
                                {{ $report->case_id }}
                                {{-- Unread badge: toggled in real-time by JS --}}
                                <span class="unread-badge" id="badge-{{ $report->report_id }}"
                                      style="{{ $unreadCount > 0 ? '' : 'display:none;' }}">
                                    {{ $unreadCount }}
                                </span>
                            </strong>
                            <span>{{ $report->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="report-item-content">
                            <p class="summary-preview">{{ Str::limit($report->description, 25) }}</p>
                            @if($section === 'archived')
                                <span class="archived-label"><i class="fas fa-archive"></i> Archived</span>
                            @else
                                <span class="status-badge {{ $report->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                </span>
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="empty-state">
                        <i class="fas {{ $section === 'archived' ? 'fa-archive' : 'fa-inbox' }}"></i>
                        <p>{{ $section === 'archived' ? 'No archived cases.' : 'No reports found.' }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- RIGHT: CONVERSATION --}}
        <section class="main-display-card">
            @if($selectedReport)

                @if(session('success'))
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                {{-- Compact conversation header --}}
                <div class="conv-header">
                    <div class="conv-header-left">
                        <div class="conv-header-title">
                            <span class="conv-case-id">{{ $selectedReport->case_id }}</span>
                            <span class="status-badge {{ $selectedReport->status }}">
                                {{ ucfirst(str_replace('_', ' ', $selectedReport->status)) }}
                            </span>
                            {{-- Live indicator --}}
                            <span id="liveIndicator" style="font-size:0.7rem; font-weight:400; color:#22c55e;
                                  margin-left:8px; opacity:0; transition: opacity 0.4s;">
                                <span style="display:inline-block; width:7px; height:7px; border-radius:50%;
                                             background:#22c55e; margin-right:3px; vertical-align:middle;"></span>Live
                            </span>
                        </div>
                        <div class="conv-header-meta">
                            <span><i class="fas fa-calendar-plus"></i> {{ $selectedReport->created_at->format('M d, Y h:i A') }}</span>
                            <span class="conv-meta-divider">·</span>
                            <span><i class="fas fa-calendar-day"></i> Incident: {{ \Carbon\Carbon::parse($selectedReport->occurred_at)->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="conv-header-actions">
                        {{-- Status Update (inbox only) --}}
                        @if(!$selectedReport->is_archived)
                        <form action="{{ route('guidance.updateStatus', $selectedReport->report_id) }}"
                              method="POST" class="status-form">
                            @csrf
                            @method('PATCH')
                            <select name="status">
                                <option value="pending"     {{ $selectedReport->status === 'pending'     ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $selectedReport->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved"    {{ $selectedReport->status === 'resolved'    ? 'selected' : '' }}>Resolved</option>
                            </select>
                            <button type="submit"><i class="fas fa-save"></i> Update Status</button>
                        </form>
                        @endif

                        {{-- Archive / Unarchive --}}
                        @if($selectedReport->is_archived)
                            <form action="{{ route('guidance.unarchive', $selectedReport->report_id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="unarchive-btn">
                                    <i class="fas fa-inbox"></i> Restore to Inbox
                                </button>
                            </form>
                        @else
                            <button type="button" class="archive-btn"
                                    onclick="showArchiveConfirm('{{ $selectedReport->case_id }}', '{{ $selectedReport->report_id }}')">
                                <i class="fas fa-archive"></i> Archive Case
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Chat Window --}}
                <div class="chat-display" id="chatWindow">
                    <div class="chat-bubble student-msg">
                        <div class="bubble-text">
                            <small><strong>Report Description:</strong></small><br>
                            {!! nl2br(e($selectedReport->description)) !!}
                        </div>
                    </div>

                    @php $lastMessageId = 0; @endphp
                    @foreach($selectedReport->messages as $msg)
                        @php
                            $isMe = ($msg->user_id == auth()->id());
                            if ($msg->message_id > $lastMessageId) $lastMessageId = $msg->message_id;
                        @endphp
                        <div class="chat-bubble {{ $isMe ? 'guidance-msg' : 'student-msg' }}"
                             data-sender="{{ $isMe ? 'You' : 'Student' }}">
                            <div class="bubble-text">
                                {!! nl2br(e($msg->message_text)) !!}
                                <small>{{ $msg->created_at->format('h:i A') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Reply Form --}}
                @if(!$selectedReport->is_archived)
                <div class="chat-input-container">
                    <input type="hidden" id="reportId" value="{{ $selectedReport->report_id }}">
                    <div class="input-wrapper">
                        <textarea id="messageInput"
                                  placeholder="Type your response here..."
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
                <div style="text-align:center; padding:12px; background:#fff3cd; border-radius:8px;
                            font-size:0.82rem; color:#8a6400; margin-top:8px;">
                    <i class="fas fa-lock"></i> This case is archived. Restore it to inbox to send messages.
                </div>
                @endif

            @else
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <p>Select a report to start the conversation.</p>
                </div>
            @endif
        </section>
    </main>

    {{-- Archive Confirm Modal --}}
    <div class="confirm-overlay" id="archiveConfirm">
        <div class="confirm-box">
            <i class="fas fa-archive"></i>
            <h3>Archive this case?</h3>
            <p id="confirmText">This will move the case to the archived section. You can restore it anytime.</p>
            <div class="confirm-actions">
                <button class="btn-cancel" onclick="hideArchiveConfirm()">Cancel</button>
                <form id="archiveForm" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-confirm">Yes, Archive It</button>
                </form>
            </div>
        </div>
    </div>

    <div class="settings-overlay" id="settingsOverlay" onclick="closeSettings()"></div>
 
    {{-- Settings Panel --}}
    <div class="settings-panel" id="settingsPanel">
    
        {{-- Header --}}
        <div class="settings-header">
            <h2><i class="fas fa-cog"></i> Settings</h2>
            <button class="settings-close-btn" onclick="closeSettings()">&times;</button>
        </div>
    
        {{-- Success Alert --}}
        @if(session('settings_success'))
            <div class="settings-alert-success">
                <i class="fas fa-check-circle"></i> {{ session('settings_success') }}
            </div>
        @endif
    
        {{-- Tabs --}}
        <div class="settings-tabs">
            <button class="settings-tab active" onclick="switchTab('profile', this)">
                <i class="fas fa-user"></i> Profile
            </button>
            <button class="settings-tab" onclick="switchTab('password', this)">
                <i class="fas fa-lock"></i> Password
            </button>
            <button class="settings-tab" onclick="switchTab('account', this)">
                <i class="fas fa-info-circle"></i> Account
            </button>
        </div>
    
        {{-- Tab: Update Profile --}}
        <div class="settings-tab-content active" id="tab-profile">
            <form action="{{ route('guidance.updateProfile') }}" method="POST">
                @csrf
                @method('PATCH')
    
                <div class="settings-field">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="{{ auth()->user()->full_name }}" required>
                    @error('full_name')
                        <span class="settings-error">{{ $message }}</span>
                    @enderror
                </div>
    
                <div class="settings-field">
                    <label>Username</label>
                    <input type="text" value="{{ auth()->user()->username }}" disabled>
                    <small class="settings-hint">Username cannot be changed.</small>
                </div>
    
                <div class="settings-field">
                    <label>Department</label>
                    <input type="text" name="department" value="{{ auth()->user()->department }}" placeholder="e.g. Guidance Office">
                    @error('department')
                        <span class="settings-error">{{ $message }}</span>
                    @enderror
                </div>
    
                <button type="submit" class="settings-save-btn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    
        {{-- Tab: Change Password --}}
        <div class="settings-tab-content" id="tab-password">
            <form action="{{ route('guidance.updatePassword') }}" method="POST">
                @csrf
                @method('PATCH')
    
                <div class="settings-field">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required placeholder="Enter current password">
                    @error('current_password')
                        <span class="settings-error">{{ $message }}</span>
                    @enderror
                </div>
    
                <div class="settings-field">
                    <label>New Password</label>
                    <input type="password" name="new_password" required placeholder="At least 8 characters">
                    @error('new_password')
                        <span class="settings-error">{{ $message }}</span>
                    @enderror
                </div>
    
                <div class="settings-field">
                    <label>Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" required placeholder="Repeat new password">
                </div>
    
                <button type="submit" class="settings-save-btn">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </form>
        </div>
    
        {{-- Tab: Account Information --}}
        <div class="settings-tab-content" id="tab-account">
            <div class="account-info-card">
                <div class="account-avatar">
                    {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                </div>
                <h3>{{ auth()->user()->full_name }}</h3>
                <span class="account-role-badge">{{ ucfirst(auth()->user()->role) }}</span>
            </div>
    
            <div class="account-info-list">
                <div class="account-info-item">
                    <span class="info-label"><i class="fas fa-id-badge"></i> Username</span>
                    <span class="info-value">{{ auth()->user()->username }}</span>
                </div>
                <div class="account-info-item">
                    <span class="info-label"><i class="fas fa-building"></i> Department</span>
                    <span class="info-value">{{ auth()->user()->department ?? 'Not set' }}</span>
                </div>
                <div class="account-info-item">
                    <span class="info-label"><i class="fas fa-shield-alt"></i> Role</span>
                    <span class="info-value">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
                <div class="account-info-item">
                    <span class="info-label"><i class="fas fa-calendar-alt"></i> Member Since</span>
                    <span class="info-value">{{ auth()->user()->created_at->format('F d, Y') }}</span>
                </div>
            </div>
        </div>
    
    </div>

    <script>
    // ─── CONFIG ───────────────────────────────────────────────────────────────
    const REPORT_ID    = {{ $selectedReport ? $selectedReport->report_id : 'null' }};
    const CURRENT_USER = {{ auth()->id() }};
    const POLL_URL     = "{{ route('messages.poll', ['reportId' => $selectedReport ? $selectedReport->report_id : 0]) }}";
    const SEND_URL     = "{{ route('messages.send') }}";
    const CSRF         = document.querySelector('meta[name="csrf-token"]').content;

    let lastMessageId = {{ $lastMessageId ?? 0 }};
    let pollTimer     = null;
    let isSending     = false;

    // ─── SCROLL HELPER ────────────────────────────────────────────────────────
    function scrollToBottom() {
        const win = document.getElementById('chatWindow');
        if (win) win.scrollTop = win.scrollHeight;
    }

    // ─── BUILD A CHAT BUBBLE ──────────────────────────────────────────────────
    function buildBubble(text, isMine, time, sender) {
        const div = document.createElement('div');
        div.className = 'chat-bubble ' + (isMine ? 'guidance-msg' : 'student-msg');
        if (sender) div.setAttribute('data-sender', sender);

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
                const bubble = buildBubble(data.text, true, data.time, 'You');
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

    // ─── POLL FOR NEW MESSAGES (student replies) ──────────────────────────────
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
                    // Skip own messages already rendered via sendMessage()
                    if (msg.user_id === CURRENT_USER) {
                        lastMessageId = Math.max(lastMessageId, msg.message_id);
                        return;
                    }
                    const bubble = buildBubble(msg.text, false, msg.time, 'Student');
                    win.appendChild(bubble);
                    lastMessageId = Math.max(lastMessageId, msg.message_id);

                    // Clear unread badge for this report in the left sidebar
                    const badge = document.getElementById('badge-' + REPORT_ID);
                    if (badge) badge.style.display = 'none';
                });
                scrollToBottom();
            }

            showLive();

        } catch (err) {
            console.warn('Poll error:', err);
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

    // ─── SEND ON ENTER ────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('messageInput');
        if (input) {
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
        }

        scrollToBottom();

        // Start polling
        if (REPORT_ID) {
            pollMessages();
            pollTimer = setInterval(pollMessages, 3000);
        }
    });

    window.addEventListener('beforeunload', () => clearInterval(pollTimer));

    // ─── EXISTING GUIDANCE JS (archive modal, settings panel) ────────────────
    function showArchiveConfirm(caseId, reportId) {
        document.getElementById('confirmText').innerText =
            'This will move ' + caseId + ' to the archived section. You can restore it anytime.';
        document.getElementById('archiveForm').action =
            '/guidance/report/' + reportId + '/archive';
        document.getElementById('archiveConfirm').classList.add('show');
    }

    function hideArchiveConfirm() {
        document.getElementById('archiveConfirm').classList.remove('show');
    }

    document.getElementById('archiveConfirm').addEventListener('click', function(e) {
        if (e.target === this) hideArchiveConfirm();
    });

    function openSettings() {
        document.getElementById('settingsPanel').classList.add('open');
        document.getElementById('settingsOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';

        @if(session('settings_success') && str_contains(session('settings_success'), 'Password'))
            switchTab('password', document.querySelectorAll('.settings-tab')[1]);
        @endif
    }

    function closeSettings() {
        document.getElementById('settingsPanel').classList.remove('open');
        document.getElementById('settingsOverlay').classList.remove('open');
        document.body.style.overflow = '';
    }

    function switchTab(tabName, btn) {
        document.querySelectorAll('.settings-tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
        document.getElementById('tab-' + tabName).classList.add('active');
        btn.classList.add('active');
    }

    @if(session('settings_success') || $errors->any())
        window.addEventListener('DOMContentLoaded', () => openSettings());
    @endif

    @if($errors->has('current_password') || $errors->has('new_password'))
        window.addEventListener('DOMContentLoaded', () => {
            openSettings();
            switchTab('password', document.querySelectorAll('.settings-tab')[1]);
        });
    @endif

    @if($errors->has('full_name') || $errors->has('department'))
        window.addEventListener('DOMContentLoaded', () => {
            openSettings();
            switchTab('profile', document.querySelectorAll('.settings-tab')[0]);
        });
    @endif
    </script>
</body>
</html>