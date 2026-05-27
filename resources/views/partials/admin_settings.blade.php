{{-- resources/views/partials/admin_settings.blade.php --}}

<div class="settings-overlay" id="adminSettingsOverlay" onclick="closeAdminSettings()"></div>

<div class="admin-settings-panel" id="adminSettingsPanel">

    {{-- Header --}}
    <div class="settings-header">
        <h2><i class="fas fa-shield-alt"></i> Admin Settings</h2>
        <button class="settings-close-btn" onclick="closeAdminSettings()">&times;</button>
    </div>

    {{-- Success alert --}}
    @if(session('admin_settings_success'))
        <div class="settings-alert-success">
            <i class="fas fa-check-circle"></i> {{ session('admin_settings_success') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="settings-tabs">
        <button class="settings-tab active" id="tab-btn-profile" onclick="switchAdminTab('profile', this)">
            <i class="fas fa-user"></i> Profile
        </button>
        <button class="settings-tab" id="tab-btn-password" onclick="switchAdminTab('password', this)">
            <i class="fas fa-lock"></i> Password
        </button>
        <button class="settings-tab" id="tab-btn-account" onclick="switchAdminTab('account', this)">
            <i class="fas fa-info-circle"></i> Account
        </button>
    </div>

    {{-- ── Tab: Profile ── --}}
    <div class="settings-tab-content active" id="admin-tab-profile">
        <form action="{{ route('admin.updateProfile') }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="settings-field">
                <label>Full Name</label>
                <input type="text" name="full_name"
                       value="{{ old('full_name', auth()->user()->full_name) }}"
                       required placeholder="Your full name">
                @error('full_name')
                    <span class="settings-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="settings-field">
                <label>Department</label>
                <input type="text" name="department"
                       value="{{ old('department', auth()->user()->department) }}"
                       placeholder="e.g. IT Department">
                @error('department')
                    <span class="settings-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="settings-field">
                <label>Username</label>
                <input type="text" value="{{ auth()->user()->username }}" disabled>
                <span class="settings-hint">Username cannot be changed.</span>
            </div>

            <button type="submit" class="settings-save-btn">
                <i class="fas fa-save"></i> Save Profile
            </button>
        </form>
    </div>

    {{-- Tab: Password --}}
    <div class="settings-tab-content" id="admin-tab-password">
        <form action="{{ route('admin.updatePassword') }}" method="POST">
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

    {{-- Tab: Account Info --}}
    <div class="settings-tab-content" id="admin-tab-account">
        <div class="account-info-card admin-card">
            <div class="account-avatar">
                {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
            </div>
            <h3>{{ auth()->user()->full_name }}</h3>
            <span class="account-role-badge">Administrator</span>
        </div>

        <div class="account-info-list">
            <div class="account-info-item">
                <span class="info-label"><i class="fas fa-id-badge"></i> Username</span>
                <span class="info-value">{{ auth()->user()->username }}</span>
            </div>
            <div class="account-info-item">
                <span class="info-label"><i class="fas fa-building"></i> Department</span>
                <span class="info-value">{{ auth()->user()->department ?? 'N/A' }}</span>
            </div>
            <div class="account-info-item">
                <span class="info-label"><i class="fas fa-shield-alt"></i> Role</span>
                <span class="info-value">Administrator</span>
            </div>
            <div class="account-info-item">
                <span class="info-label"><i class="fas fa-calendar-alt"></i> Member Since</span>
                <span class="info-value">{{ auth()->user()->created_at->format('F d, Y') }}</span>
            </div>
        </div>

        <div style="margin-top:16px; padding:12px 14px; background:#fff8ec; border-radius:8px; border:1px solid #f6a623; font-size:0.78rem; color:#8a6400; line-height:1.6;">
            <i class="fas fa-exclamation-triangle"></i>
            For security, change your password regularly and never share it with anyone.
        </div>
    </div>

</div>

<script>
    function openAdminSettings() {
        document.getElementById('adminSettingsPanel').classList.add('open');
        document.getElementById('adminSettingsOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeAdminSettings() {
        document.getElementById('adminSettingsPanel').classList.remove('open');
        document.getElementById('adminSettingsOverlay').classList.remove('open');
        document.body.style.overflow = '';
    }

    function switchAdminTab(tabName, btn) {
        document.querySelectorAll('#adminSettingsPanel .settings-tab-content')
            .forEach(t => t.classList.remove('active'));
        document.querySelectorAll('#adminSettingsPanel .settings-tab')
            .forEach(b => b.classList.remove('active'));
        document.getElementById('admin-tab-' + tabName).classList.add('active');
        btn.classList.add('active');
    }

    @if(session('admin_settings_success'))
        window.addEventListener('DOMContentLoaded', () => openAdminSettings());
    @endif

    @if($errors->hasAny(['current_password', 'new_password']))
        window.addEventListener('DOMContentLoaded', () => {
            openAdminSettings();
            switchAdminTab('password', document.getElementById('tab-btn-password'));
        });
    @endif

    @if($errors->hasAny(['full_name', 'department']))
        window.addEventListener('DOMContentLoaded', () => {
            openAdminSettings();
            switchAdminTab('profile', document.getElementById('tab-btn-profile'));
        });
    @endif
</script>