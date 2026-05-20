

{{-- Overlay --}}
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
    </div>

    {{-- ── Tab: Update Profile ── --}}
    <div class="settings-tab-content active" id="tab-profile">
        <form action="{{ route('student.updateProfile') }}" method="POST">
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
                <label>Student ID (USN)</label>
                <input type="text" value="{{ auth()->user()->username }}" disabled>
                <small class="settings-hint">Student ID cannot be changed.</small>
            </div>

            <div class="settings-field">
                <label>Course / Department</label>
                <input type="text" name="department" value="{{ auth()->user()->department }}" placeholder="e.g. BSIT, BSCS">
                @error('department')
                    <span class="settings-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="settings-save-btn">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>

    {{-- ── Tab: Change Password ── --}}
    <div class="settings-tab-content" id="tab-password">
        <form action="{{ route('student.updatePassword') }}" method="POST">
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

</div>

<script>
    function openSettings() {
        document.getElementById('settingsPanel').classList.add('open');
        document.getElementById('settingsOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
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

    // Auto-open on success
    @if(session('settings_success'))
        window.addEventListener('DOMContentLoaded', () => {
            openSettings();
            @if(str_contains(session('settings_success') ?? '', 'Password'))
                switchTab('password', document.querySelectorAll('.settings-tab')[1]);
            @endif
        });
    @endif

    // Auto-open on validation errors and switch to the right tab
    @if($errors->hasAny(['full_name', 'department']))
        window.addEventListener('DOMContentLoaded', () => {
            openSettings();
            switchTab('profile', document.querySelectorAll('.settings-tab')[0]);
        });
    @endif

    @if($errors->hasAny(['current_password', 'new_password']))
        window.addEventListener('DOMContentLoaded', () => {
            openSettings();
            switchTab('password', document.querySelectorAll('.settings-tab')[1]);
        });
    @endif
</script>