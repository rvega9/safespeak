<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        $students  = User::where('role', 'student')->orderBy('created_at', 'desc')->get();
        $guidances = User::where('role', 'guidance')->orderBy('created_at', 'desc')->get();

        return view('admin_dashboard', compact('students', 'guidances'));
    }

    public function registerStudent(Request $request)
    {
        $request->validate([
            'student_name'       => 'required|string',
            'student_id'         => 'required|string|unique:users,username',
            'student_department' => 'required|string',
            'student_password'   => 'required|string|min:6',
        ]);

        User::create([
            'full_name'  => $request->student_name,
            'username'   => $request->student_id,
            'department' => $request->student_department,
            'role'       => 'student',
            'password'   => Hash::make($request->student_password),
        ]);

        return back()->with('student_success', true);
    }

    public function registerGuidance(Request $request)
    {
        $request->validate([
            'guidance_name'    => 'required|string',
            'employee_id'      => 'required|string|unique:users,username',
            'guidance_password'=> 'required|string|min:6',
        ]);

        User::create([
            'full_name'  => $request->guidance_name,
            'username'   => $request->employee_id,
            'department' => 'Guidance Office',
            'role'       => 'guidance',
            'password'   => Hash::make($request->guidance_password),
        ]);

        return back()->with('guidance_success', true);
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('edit_user', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'full_name'  => 'required|string|max:255',
            'username'   => 'required|string|max:255|unique:users,username,' . $id,
            'department' => $user->role === 'student' ? 'required|string|max:255' : 'nullable',
            'password'   => 'nullable|min:6',
        ]);

        $user->full_name = $request->full_name;
        $user->username  = $request->username;

        if ($user->role === 'student') {
            $user->department = $request->department;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.manageAccounts')->with('msg', 'updated');
    }

    public function manageAccounts()
    {
        // Only active (non-soft-deleted) users appear in the table
        $users = User::orderBy('created_at', 'desc')->get();
        return view('manage_accounts', compact('users'));
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting admin accounts
        if ($user->role === 'admin') {
            return redirect()->route('admin.manageAccounts')
                ->with('error', 'Admin accounts cannot be deleted.');
        }

        // Soft delete (user is deactivated, but reports and messages remains)
        $user->delete();

        return redirect()->route('admin.manageAccounts')
            ->with('success', 'User "' . $user->full_name . '" has been deactivated. Their reports are preserved for documentation.');
    }

    public function showRegisterUserForm()
    {
        return view('register_user');
    }

    public function registerUser(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:student,guidance',
        ]);

        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return back()->with('success', 'User registered successfully!');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    // Admin: Update Profile
    public function updateProfile(Request $request)
    {
        $request->validate([
            'full_name'  => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $user->full_name  = $request->full_name;
        $user->department = $request->department;
        $user->save();

        return back()->with('admin_settings_success', 'Profile updated successfully.');
    }

    // Admin: Update Password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        // Verify current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password you entered is incorrect.',
            ]);
        }

        // Prevent reusing the same password
        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors([
                'new_password' => 'New password must be different from your current password.',
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('admin_settings_success', 'Password changed successfully.');
    }
}