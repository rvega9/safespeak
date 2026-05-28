<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SafeSpeak</title>
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
            <a href="{{ route('admin.dashboard') }}"
               class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i> Dashboard Overview
            </a>
            <a href="{{ route('admin.manageAccounts') }}"
               class="nav-item {{ request()->routeIs('admin.manageAccounts') ? 'active' : '' }}">
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
                <i class="fas fa-th-large" style="color:#2b7cd3; margin-right:6px;"></i>
                Dashboard Overview
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
            <i class="fas fa-shield-alt"></i> Admin Dashboard
        </h1>

        @if(session('student_success'))
            <div class="alert-success auto-dismiss">
                <i class="fas fa-check-circle"></i> Student registered successfully!
            </div>
        @endif
        @if(session('guidance_success'))
            <div class="alert-success auto-dismiss">
                <i class="fas fa-check-circle"></i> Counselor registered successfully!
            </div>
        @endif
        @if($errors->any())
            <div class="alert-error auto-dismiss">
                <i class="fas fa-exclamation-circle"></i>
                <ul style="margin:0; padding-left:16px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Register Cards --}}
        <section class="dashboard-grid">

            <div class="card student-bg">
                <h3><i class="fas fa-user-graduate"></i> Register New Student</h3>
                <form method="POST" action="{{ route('admin.registerStudent') }}">
                    @csrf
                    <div class="input-group">
                        <label>Full Name</label>
                        <input type="text" name="student_name" value="{{ old('student_name') }}" placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                    <div class="input-group">
                        <label>Student ID (USN)</label>
                        <input type="text" name="student_id" value="{{ old('student_id') }}" placeholder="e.g. 20240000110" required>
                    </div>
                    <div class="input-group">
                        <label>Department</label>
                        <input type="text" name="student_department" value="{{ old('student_department') }}" placeholder="e.g. Computer Studies" required>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="student_password" placeholder="Min. 6 characters" required>
                    </div>
                    
                    <div class="input-group card-spacer" aria-hidden="true"></div>
                    <button type="submit" class="action-btn">
                        <i class="fas fa-user-plus"></i> Register Student
                    </button>
                </form>
            </div>

            <div class="card guidance-bg">
                <h3><i class="fas fa-user-tie"></i> Register New Guidance Counselor</h3>
                <form method="POST" action="{{ route('admin.registerGuidance') }}">
                    @csrf
                    <div class="input-group">
                        <label>Full Name</label>
                        <input type="text" name="guidance_name" value="{{ old('guidance_name') }}" placeholder="e.g. Maria Santos" required>
                    </div>
                    <div class="input-group">
                        <label>Employee ID</label>
                        <input type="text" name="employee_id" value="{{ old('employee_id') }}" placeholder="e.g. EMP0001" required>
                    </div>
                    <div class="input-group">
                        <label>Password</label>
                        <input type="password" name="guidance_password" placeholder="Min. 6 characters" required>
                    </div>
                    {{-- two spacers to match the student card's 4 fields --}}
                    <div class="input-group card-spacer" aria-hidden="true"></div>
                    <div class="input-group card-spacer" aria-hidden="true"></div>
                    <button type="submit" class="action-btn guidance-btn">
                        <i class="fas fa-user-plus"></i> Register Counselor
                    </button>
                </form>
            </div>

        </section>

        {{-- Overview tables --}}
        <div class="manage-accounts-container">
            <div class="manage-accounts-title">
                <i class="fas fa-users" style="color:#2b7cd3;"></i>
                Overview: Registered Accounts
            </div>

            <div class="table-section">

                <div>
                    <div class="table-headerCard">
                        <i class="fas fa-user-graduate" style="color:#4caf50;"></i>
                        Registered Students
                    </div>
                    <div class="table-wrapper">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th class="col-left">USN / ID</th>
                                    <th class="col-left">Full Name</th>
                                    <th>Department</th>
                                    <th>Date Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $s)
                                    <tr>
                                        <td class="username-cell col-left">{{ $s->username }}</td>
                                        <td class="col-left">{{ $s->full_name }}</td>
                                        <td>{{ $s->department }}</td>
                                        <td>{{ $s->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="no-data">No students registered yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    @if($students->hasPages())
                        <div class="pagination-wrapper">
                            {{ $students->links('partials.pagination') }}
                        </div>
                    @endif
                </div>

                <div>
                    <div class="table-headerCard">
                        <i class="fas fa-user-tie" style="color:#3dbfbf;"></i>
                        Registered Counselors
                    </div>
                    <div class="table-wrapper">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th class="col-left">Employee ID</th>
                                    <th class="col-left">Full Name</th>
                                    <th>Date Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($guidances as $g)
                                    <tr>
                                        <td class="username-cell col-left">{{ $g->username }}</td>
                                        <td class="col-left">{{ $g->full_name }}</td>
                                        <td>{{ $g->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="no-data">No counselors registered yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    @if($guidances->hasPages())
                        <div class="pagination-wrapper">
                            {{ $guidances->links('partials.pagination') }}
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </main>

    <script>
        // Hamburger toggle
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

        // Auto-dismiss alerts
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