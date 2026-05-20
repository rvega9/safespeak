<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accounts | SafeSpeak</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo2.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin_style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('assets/logo2.png') }}" alt="SafeSpeak Logo" class="sidebar-logo">
            <h2 class="sidebar-brand">
                <span class="safe-text">SAFE</span><span class="speak-text">SPEAK</span>
            </h2>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <nav class="nav-menu" id="navMenu">
            <p class="nav-label">Admin Panel</p>
            <a href="{{ route('admin.dashboard') }}" class="nav-item">
                <i class="fas fa-th-large"></i> Dashboard Overview
            </a>
            <a href="{{ route('admin.manageAccounts') }}" class="nav-item active">
                <i class="fas fa-users-cog"></i> Manage Accounts
            </a>
        </nav>

        <div class="sidebar-footer" id="sidebarFooter">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-logout">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="main-content">

        <header class="top-bar">
            <span class="top-bar-title">
                <i class="fas fa-users-cog" style="color:#2b7cd3; margin-right:6px;"></i>
                Manage Accounts
            </span>
            <span class="top-bar-user">
                Hello, <span>{{ auth()->user()->full_name }}</span>!
            </span>
            <button onclick="openAdminSettings()"
                    style="background:none; border:none; cursor:pointer; color:#6b7a90; font-size:1.05rem; padding:4px 8px; border-radius:6px; transition:color 0.2s; line-height:1;"
                    title="Settings">
                <i class="fas fa-cog"></i>
            </button>
        </header>

        @include('partials.admin_settings')

        <h1 class="page-heading">
            <i class="fas fa-users"></i> Manage Accounts
        </h1>

        @if(session('success') || session('msg'))
            <div class="alert-success auto-dismiss">
                <i class="fas fa-check-circle"></i>
                {{ session('success') ?? 'User updated successfully.' }}
            </div>
        @endif

        <div class="manage-accounts-container">

            {{-- ── Tabs ── --}}
            @php
                $studentCount  = $users->where('role', 'student')->count();
                $guidanceCount = $users->where('role', 'guidance')->count();
            @endphp

            <div class="account-tabs">
                <button class="account-tab active" onclick="switchTab('students', this)">
                    <i class="fas fa-user-graduate"></i>
                    Students
                    <span class="tab-badge">{{ $studentCount }}</span>
                </button>
                <button class="account-tab guidance-tab" onclick="switchTab('guidance', this)">
                    <i class="fas fa-user-tie"></i>
                    Guidance Counselors
                    <span class="tab-badge">{{ $guidanceCount }}</span>
                </button>
            </div>

            {{-- ══ STUDENTS TAB ══ --}}
            <div class="tab-panel active" id="tab-students">
                <div class="tab-search">
                    <input type="text" id="searchStudents"
                           placeholder="Search by USN / ID or name..."
                           oninput="filterTable('studentsTable', this.value)">
                </div>
                <div class="table-wrapper">
                    <table class="custom-table" id="studentsTable">
                        <thead>
                            <tr>
                                <th class="col-left">USN / ID</th>
                                <th class="col-left">Full Name</th>
                                <th>Department</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users->where('role', 'student') as $user)
                                <tr class="searchable-row">
                                    <td class="username-cell col-left">{{ $user->username }}</td>
                                    <td class="col-left">{{ $user->full_name }}</td>
                                    <td>{{ $user->department ?: 'N/A' }}</td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="{{ route('admin.editUser', $user->id) }}" class="btn-edit">
                                                <i class="fas fa-edit"></i> <span>Edit</span>
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.deleteUser', $user->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-delete"
                                                            onclick="return confirm('Delete {{ $user->full_name }}? This cannot be undone.')">
                                                        <i class="fas fa-trash"></i> <span>Delete</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="no-data">No students registered yet.</td></tr>
                            @endforelse
                            <tr class="no-results" id="noResultsStudents">
                                <td colspan="5">No results match your search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ══ GUIDANCE TAB ══ --}}
            <div class="tab-panel" id="tab-guidance">
                <div class="tab-search">
                    <input type="text" id="searchGuidance"
                           placeholder="Search by Employee ID or name..."
                           oninput="filterTable('guidanceTable', this.value)">
                </div>
                <div class="table-wrapper">
                    <table class="custom-table" id="guidanceTable">
                        <thead>
                            <tr>
                                <th class="col-left">Employee ID</th>
                                <th class="col-left">Full Name</th>
                                <th>Department</th>
                                <th>Date Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users->where('role', 'guidance') as $user)
                                <tr class="searchable-row">
                                    <td class="username-cell col-left">{{ $user->username }}</td>
                                    <td class="col-left">{{ $user->full_name }}</td>
                                    <td>{{ $user->department ?: 'Guidance Office' }}</td>
                                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="actions-cell">
                                            <a href="{{ route('admin.editUser', $user->id) }}" class="btn-edit">
                                                <i class="fas fa-edit"></i> <span>Edit</span>
                                            </a>
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('admin.deleteUser', $user->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-delete"
                                                            onclick="return confirm('Delete {{ $user->full_name }}? This cannot be undone.')">
                                                        <i class="fas fa-trash"></i> <span>Delete</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="no-data">No counselors registered yet.</td></tr>
                            @endforelse
                            <tr class="no-results" id="noResultsGuidance">
                                <td colspan="5">No results match your search.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <script>
        // ── Tab switching ──
        function switchTab(tabName, btn) {
            document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.account-tab').forEach(b => b.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            btn.classList.add('active');
        }

        // ── Live search filter ──
        function filterTable(tableId, query) {
            const table    = document.getElementById(tableId);
            const rows     = table.querySelectorAll('tr.searchable-row');
            const noResult = table.querySelector('.no-results');
            const q        = query.toLowerCase().trim();
            let visible    = 0;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(q)) {
                    row.style.display = '';
                    visible++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noResult) noResult.style.display = visible === 0 ? 'table-row' : 'none';
        }

        // ── Hamburger ──
        const toggle = document.getElementById('sidebarToggle');
        const navMenu = document.getElementById('navMenu');
        const sidebarFooter = document.getElementById('sidebarFooter');
        if (toggle) {
            toggle.addEventListener('click', () => {
                navMenu.classList.toggle('open');
                sidebarFooter.classList.toggle('open');
                toggle.querySelector('i').classList.toggle('fa-bars');
                toggle.querySelector('i').classList.toggle('fa-times');
            });
        }

        // ── Auto-dismiss alerts ──
        setTimeout(() => {
            document.querySelectorAll('.auto-dismiss').forEach(el => {
                el.style.transition = 'opacity 0.4s';
                el.style.opacity = '0';
                setTimeout(() => el.style.display = 'none', 400);
            });
        }, 4000);
    </script>
</body>
</html>