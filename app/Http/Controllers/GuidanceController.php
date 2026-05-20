<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GuidanceController extends Controller
{
    public function index(Request $request)
    {
        $filter  = $request->query('filter', 'all');
        $search  = $request->query('search', '');
        $section = $request->query('section', 'inbox');

        $query = Report::with('messages')->orderBy('created_at', 'desc');

        if ($section === 'archived') {
            $query->where('is_archived', true);
        } else {
            $query->where('is_archived', false);
        }

        if ($filter !== 'all' && $section !== 'archived') {
            $query->where('status', $filter);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('case_id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $reports = $query->get();

        $activeReports = Report::where('is_archived', false)->get();
        $stats = [
            'total'       => $activeReports->count(),
            'pending'     => $activeReports->where('status', 'pending')->count(),
            'in_progress' => $activeReports->where('status', 'in_progress')->count(),
            'resolved'    => $activeReports->where('status', 'resolved')->count(),
            'archived'    => Report::where('is_archived', true)->count(),
        ];

        $selectedReportId = $request->query('report');
        $selectedReport   = $reports->where('report_id', $selectedReportId)->first() ?? $reports->first();

        if ($selectedReport) {
            $selectedReport->messages()
                ->where('is_read', false)
                ->where('user_id', '!=', auth()->id())
                ->update(['is_read' => true]);
        }

        return view('guidance_dashboard', compact(
            'reports', 'selectedReport', 'stats', 'filter', 'search', 'section'
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved',
        ]);

        $report         = Report::findOrFail($id);
        $report->status = $request->status;
        $report->save();

        return back()->with('success', 'Status updated successfully.');
    }

    public function archive($id)
    {
        $report              = Report::findOrFail($id);
        $report->is_archived = true;
        $report->save();

        return redirect()->route('guidance.dashboard')
            ->with('success', 'Case ' . $report->case_id . ' has been archived.');
    }

    public function unarchive($id)
    {
        $report              = Report::findOrFail($id);
        $report->is_archived = false;
        $report->save();

        return redirect()->route('guidance.dashboard', ['section' => 'archived'])
            ->with('success', 'Case ' . $report->case_id . ' has been restored to inbox.');
    }

    // ── Update Profile (Full Name + Department) ──────────────────────────────
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

        return back()->with('settings_success', 'Profile updated successfully.');
    }

    // ── Update Password ───────────────────────────────────────────────────────
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
            ])->withInput();
        }

        // Prevent reusing the same password
        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors([
                'new_password' => 'New password must be different from your current password.',
            ])->withInput();
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('settings_success', 'Password changed successfully.');
    }
}