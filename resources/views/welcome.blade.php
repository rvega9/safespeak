<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafeSpeak | Welcome</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo2.png') }}">
    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <div class="admin-access">
        <button class="admin-btn" onclick="openModal('adminModal')">Admin Portal</button>
    </div>

    <div class="welcome-container">
        <div class="content">
            <img src="{{ asset('assets/logo2.png') }}" alt="SafeSpeak Logo" class="logo">

            <h1><span class="safe">SAFE</span><span class="speak">SPEAK</span></h1>
            <p class="subtitle">Your Voice Always Matters.</p>

            <div class="buttons">
                <button class="btn student" onclick="openModal('studentModal')">Student Login</button>
                <button class="btn guidance" onclick="openModal('guidanceModal')">Guidance Login</button>
            </div>

            <div class="footer">
                <p class="school">ACLC College of Ormoc, INC.</p>
                <p>&copy; 2026 SafeSpeak System. All rights reserved.</p>
            </div>
        </div>
    </div>

    {{-- ══ STUDENT MODAL ══ --}}
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal('studentModal')">&times;</span>
            <h2>Student Login</h2>

            {{-- Show error only when this portal was attempted --}}
            @if($errors->any() && old('role') === 'student')
                <div class="login-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('username') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="student">
                <div class="form-group">
                    <label>Student ID (USN)</label>
                    <input type="text" name="username"
                           placeholder="Enter USN"
                           value="{{ old('role') === 'student' ? old('username') : '' }}"
                           required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </div>

    {{-- ══ GUIDANCE MODAL ══ --}}
    <div id="guidanceModal" class="modal">
        <div class="modal-content guidance-theme">
            <span class="close-btn" onclick="closeModal('guidanceModal')">&times;</span>
            <h2>Guidance Login</h2>

            @if($errors->any() && old('role') === 'guidance')
                <div class="login-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('username') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="guidance">
                <div class="form-group">
                    <label>Employee ID</label>
                    <input type="text" name="username"
                           placeholder="Enter ID"
                           value="{{ old('role') === 'guidance' ? old('username') : '' }}"
                           required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="login-btn guidance-btn">Login</button>
            </form>
        </div>
    </div>

    {{-- ══ ADMIN MODAL ══ --}}
    <div id="adminModal" class="modal">
        <div class="modal-content admin-theme">
            <span class="close-btn" onclick="closeModal('adminModal')">&times;</span>
            <h2>Admin Portal</h2>

            @if($errors->any() && old('role') === 'admin')
                <div class="login-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first('username') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="admin">
                <div class="form-group">
                    <label>Admin Username</label>
                    <input type="text" name="username"
                           value="{{ old('role') === 'admin' ? old('username') : '' }}"
                           required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="login-btn admin-login-btn">Secure Login</button>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).style.display = "flex";
        }
        function closeModal(id) {
            document.getElementById(id).style.display = "none";
        }

        // Close modal if clicking outside the box
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = "none";
            }
        }

        // Auto-reopen the correct modal if login failed
        // Laravel flashes the old 'role' input back so we know which portal was used
        @if($errors->any() && old('role'))
            window.addEventListener('DOMContentLoaded', function () {
                const failedPortal = '{{ old('role') }}';
                const modalMap = {
                    'student'  : 'studentModal',
                    'guidance' : 'guidanceModal',
                    'admin'    : 'adminModal',
                };
                if (modalMap[failedPortal]) {
                    openModal(modalMap[failedPortal]);
                }
            });
        @endif
    </script>
</body>
</html>