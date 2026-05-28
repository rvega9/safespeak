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

            {{-- Tabs --}}
            @php
                $studentCount  = $students->total();
                $guidanceCount = $guidances->total();
            @endphp

            <div class="account-tabs">
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'students', 'students_page' => 1]) }}"
                class="account-tab {{ $tab === 'students' ? 'active' : '' }}">
                    <i class="fas fa-user-graduate"></i>
                    Students
                    <span class="tab-badge">{{ $students->total() }}</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tab' => 'guidance', 'guidances_page' => 1]) }}"
                class="account-tab guidance-tab {{ $tab === 'guidance' ? 'active' : '' }}">
                    <i class="fas fa-user-tie"></i>
                    Guidance Counselors
                    <span class="tab-badge">{{ $guidances->total() }}</span>
                </a>
            </div>

            {{-- STUDENTS TAB --}}
            {{-- Shared search form --}}
            <form method="GET" action="{{ route('admin.manageAccounts') }}" class="tab-search" id="searchForm">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ $tab === 'students' ? 'Search by USN / ID or name...' : 'Search by Employee ID or name...' }}"
                    oninput="debounceSearch()">
            </form>
            <div class="tab-panel {{ $tab === 'students' ? 'active' : '' }}" id="tab-students">
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
                            @forelse($students as $user)
                                <tr>
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
                                <tr><td colspan="5" class="no-data">No students found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($students->hasPages())
                    <div class="pagination-wrapper">
                        {{ $students->links('partials.pagination') }}
                    </div>
                @endif
            </div>

            {{-- GUIDANCE TAB --}}
            <div class="tab-panel {{ $tab === 'guidance' ? 'active' : '' }}" id="tab-guidance">
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
                            @forelse($guidances as $user)
                                <tr>
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
                                <tr><td colspan="5" class="no-data">No counselors found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($guidances->hasPages())
                    <div class="pagination-wrapper">
                        {{ $guidances->links('partials.pagination') }}
                    </div>
                @endif
            </div>

        </div>
    </main>

    <script>
        // Tab switching is now handled by URL — no JS needed for tabs

        // Debounced server-side search
        let searchTimer;
        function debounceSearch() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                document.getElementById('searchForm').submit();
            }, 400); // submits 400ms after user stops typing
        }

        // Hamburger
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