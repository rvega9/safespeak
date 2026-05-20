<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reports | SafeSpeak</title>
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
            <h1 class="page-title">My Reports</h1>

            @if(session('success'))
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            @if($reports->isEmpty())
                <div style="text-align:center; padding: 60px 0; color: #b0b7c3;">
                    <i class="fas fa-folder-open" style="font-size: 2.5rem; margin-bottom: 12px; display:block;"></i>
                    <p style="font-size: 0.92rem;">You have not submitted any reports yet.</p>
                </div>
            @else
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Case ID</th>
                            <th>Date Submitted</th>
                            <th>Incident Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td data-label="Case ID">
                                <a href="javascript:void(0)"
                                   class="report-link"
                                   data-report-id="{{ $report->report_id }}"
                                   data-case-id="{{ $report->case_id }}"
                                   data-incident-date="{{ \Carbon\Carbon::parse($report->occurred_at)->format('F d, Y') }}"
                                   data-submitted-date="{{ \Carbon\Carbon::parse($report->created_at)->format('F d, Y h:i A') }}"
                                   data-description="{{ $report->description }}"
                                   data-status="{{ $report->status }}">
                                    {{ $report->case_id }}
                                </a>
                            </td>
                            <td data-label="Submitted">{{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y') }}</td>
                            <td data-label="Incident Date">{{ \Carbon\Carbon::parse($report->occurred_at)->format('M d, Y') }}</td>
                            <td data-label="Status">
                                <span class="status-pill status-{{ $report->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </main>

    {{-- Report Detail Modal --}}
    <div id="reportModal" class="modal-overlay" onclick="closeReportModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 id="modalCaseId">Case Details</h2>
                <span class="close-btn" onclick="closeReportModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p><strong>Date Submitted:</strong> <span id="modalSubmitted"></span></p>
                <p><strong>Incident Date:</strong> <span id="modalDate"></span></p>
                <div style="margin-top: 6px;">
                    <strong>Status:</strong> <span id="modalStatus" class="status-pill"></span>
                </div>
                <hr style="margin: 16px 0; border: 0; border-top: 1px solid #eee;">
                <p style="font-weight:600; margin-bottom:8px;">Report Description:</p>
                <div id="modalDescription" class="description-box"></div>

                <div id="withdrawSection" style="margin-top: 20px; display: none;">
                    <hr style="border: 0; border-top: 1px solid #f0e0e0; margin-bottom: 16px;">
                    <p style="font-size: 0.8rem; color: #999; margin-bottom: 10px;">
                        <i class="fas fa-info-circle"></i>
                        You can withdraw this report while it is still <strong>Pending</strong>.
                        Once withdrawn, it cannot be undone.
                    </p>
                    <form id="withdrawForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="button" class="withdraw-btn" onclick="confirmWithdraw()">
                            <i class="fas fa-undo-alt"></i> Withdraw Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Withdraw Confirmation Modal --}}
    <div id="withdrawConfirm" class="modal-overlay" onclick="cancelWithdraw()" style="display:none; z-index: 1100;">
        <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 400px; text-align: center;">
            <div style="font-size: 2.5rem; color: #c0392b; margin-bottom: 12px;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 style="margin-bottom: 10px; color: #1a1a2e;">Withdraw this report?</h3>
            <p style="font-size: 0.85rem; color: #777; margin-bottom: 24px;">
                This will mark your report as <strong>Withdrawn</strong>.
                The guidance office will be notified. This action cannot be undone.
            </p>
            <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                <button class="withdraw-btn" onclick="submitWithdraw()">
                    <i class="fas fa-undo-alt"></i> Yes, Withdraw
                </button>
                <button class="cancel-withdraw-btn" onclick="cancelWithdraw()">Cancel</button>
            </div>
        </div>
    </div>

    @include('partials.student_settings')

    <script>
        let currentReportId = null;

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.report-link').forEach(function (link) {
                link.addEventListener('click', function () {
                    openReportModal(
                        this.dataset.reportId,
                        this.dataset.caseId,
                        this.dataset.incidentDate,
                        this.dataset.submittedDate,
                        this.dataset.description,
                        this.dataset.status
                    );
                });
            });
        });

        function openReportModal(reportId, caseId, incidentDate, submittedDate, description, status) {
            currentReportId = reportId;
            document.getElementById('modalCaseId').innerText      = caseId;
            document.getElementById('modalDate').innerText        = incidentDate;
            document.getElementById('modalSubmitted').innerText   = submittedDate;
            document.getElementById('modalDescription').innerText = description;

            const statusEl = document.getElementById('modalStatus');
            statusEl.innerText = status === 'in_progress' ? 'In Progress' : status.charAt(0).toUpperCase() + status.slice(1);
            statusEl.className = 'status-pill status-' + status;

            const withdrawSection = document.getElementById('withdrawSection');
            if (status === 'pending') {
                document.getElementById('withdrawForm').action = '/my-reports/' + reportId + '/withdraw';
                withdrawSection.style.display = 'block';
            } else {
                withdrawSection.style.display = 'none';
            }

            document.getElementById('reportModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeReportModal() {
            document.getElementById('reportModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function confirmWithdraw() { document.getElementById('withdrawConfirm').style.display = 'flex'; }
        function cancelWithdraw()  { document.getElementById('withdrawConfirm').style.display = 'none'; }
        function submitWithdraw()  { document.getElementById('withdrawForm').submit(); }

        window.onkeydown = function(e) {
            if (e.key === "Escape") { cancelWithdraw(); closeReportModal(); }
        };
    </script>
</body>
</html>