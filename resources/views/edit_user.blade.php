<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | SafeSpeak</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo2.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin_style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">

    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('assets/logo2.png') }}" alt="SafeSpeak Logo" class="sidebar-logo">
            <h2 class="sidebar-brand">
                <span class="safe-text">SAFE</span><span class="speak-text">SPEAK</span>
            </h2>
        </div>
        <nav class="nav-menu">
            <p class="nav-label">ADMIN PANEL</p>
            <a href="{{ route('admin.dashboard') }}" class="nav-item"><i class="fas fa-th-large"></i> Dashboard Overview</a>
            <a href="{{ route('admin.manageAccounts') }}" class="nav-item active"><i class="fas fa-plus-circle"></i> Manage Accounts</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <div class="welcome-text" style="font-weight: bold; font-size: 1.2rem; color: var(--primary-blue);">Edit User Account</div>
            <a href="{{ route('admin.manageAccounts') }}" class="logout-btn" style="background: #64748b;">Cancel</a>
        </header>

        <div class="manage-accounts-container" style="max-width: 600px; margin: 0 auto;">
            <h3 style="margin-bottom: 20px;">Update Information for: <span style="color: var(--primary-blue);">{{ $user->username }}</span></h3>

        <form action="{{ route('admin.updateUser', $user->id) }}" method="POST">
            @csrf
            
            {{-- Full Name --}}
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" required>
            </div>

            {{-- Username (USN/ID) --}}
            <div class="input-group">
                <label>Username / ID Number</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" required>
            </div>

            {{-- Department --}}
            @if($user->role === 'student')
            <div class="input-group">
                <label>Department</label>
                <input type="text" name="department" value="{{ old('department', $user->department) }}" required>
            </div>
            @endif

            {{-- Password Reset --}}
            <div class="input-group">
                <label>New Password (Leave blank to keep current)</label>
                <input type="password" name="password" placeholder="Enter new password">
                <small style="color: #64748b; font-size: 0.75rem;">Password must be at least 6 characters.</small>
            </div>

            <button type="submit" class="action-btn" style="margin-top: 20px;">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
        </div>
    </main>
</body>
</html>