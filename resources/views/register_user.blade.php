@extends('layouts.admin')

@section('content')
<div style="padding: 40px; color: white;">
    <h1>Register User</h1>

    @if(session('success'))
        <p style="color: lightgreen;">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <div style="color: red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" style="margin-top: 20px;" action="{{ route('admin.registerUser') }}">
        @csrf
        <input type="text" name="username" placeholder="Username" value="{{ old('username') }}" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
            <option value="guidance" {{ old('role') == 'guidance' ? 'selected' : '' }}>Guidance Counselor</option>
        </select><br><br>
        <button type="submit">Register</button>
    </form>

    <div style="margin-top: 20px;">
        <a href="{{ url('/admin/dashboard') }}" class="btn admin-login-btn">Back to Dashboard</a>
    </div>
</div>
@endsection
