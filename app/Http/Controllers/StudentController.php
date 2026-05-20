<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report; 
use App\Models\Response;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    /**
     * Compute unread message count for the nav badge.
     * Reused by dashboard() and messagesIndex().
     */
    private function getUnreadCount()
    {
        $studentId   = Auth::id();
        $myReportIds = Report::where('user_id', $studentId)->pluck('report_id');

        return Response::whereIn('report_id', $myReportIds)
            ->where('user_id', '!=', $studentId)  // sent by guidance, not the student
            ->where('is_read', false)
            ->count();
    }

    /**
     * Student dashboard — Report Concern page.
     * Now passes globalUnreadCount so the nav badge works here too.
     */
    public function dashboard(Request $request)
        {
            $globalUnreadCount = $this->getUnreadCount();

            return view('student_dashboard', compact('globalUnreadCount'));
        }

        /**
     * Handle the "Share your concern" form submission.
     */
    public function submitReport(Request $request)
    {
        $request->validate([
            'incident_date' => 'required|date',
            'description'   => 'required|string',
        ]);

        Report::create([
            'user_id'     => Auth::id(),
            'occurred_at' => $request->incident_date,
            'description' => $request->description,
            'status'      => 'pending',
        ]);

        return back()->with('success', 'Your concern has been submitted successfully!');
    }

    /**
     * My Reports page.
     */
    public function myReports()
    {
        $reports = Report::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        $globalUnreadCount = $this->getUnreadCount();

        return view('my_reports', compact('reports', 'globalUnreadCount'));
    }

    /**
     * View a specific report.
     */
    public function viewReport($id)
    {
        $report = Report::where('report_id', $id)->firstOrFail();

        if ($report->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        $globalUnreadCount = $this->getUnreadCount();

        return view('view_report', compact('report', 'globalUnreadCount'));
    }

    /**
     * Messages page.
     */
    public function messagesIndex(Request $request)
    {
        $studentId = Auth::id();

        $reports = Report::where('user_id', $studentId)
                    ->with('messages')
                    ->orderBy('created_at', 'desc')
                    ->get();

        $selectedReportId = $request->query('report');
        $selectedReport   = $reports->where('report_id', $selectedReportId)->first() ?? $reports->first();

        // Mark guidance messages as read when student opens the conversation
        if ($selectedReport) {
            Response::where('report_id', $selectedReport->report_id)
                ->where('user_id', '!=', $studentId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        // Recount AFTER marking as read so badge is accurate
        $globalUnreadCount = $this->getUnreadCount();

        return view('student_messages', compact('reports', 'selectedReport', 'globalUnreadCount'));
    }

    /**
     * Send a message (used by both student and guidance).
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'report_id'    => 'required|exists:reports,report_id',
            'message_text' => 'required|string',
        ]);

        Response::create([
            'report_id'    => $request->report_id,
            'user_id'      => Auth::id(),
            'message_text' => $request->message_text,
            'is_read'      => false,
        ]);

        return back()->with('success', 'Message sent!');
    }

    public function withdrawReport($id)
    {
        $report = Report::where('report_id', $id)->firstOrFail();

        // Make sure only the owner can withdraw
        if ($report->user_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

        // Only allow withdrawal if still pending
        if ($report->status !== 'pending') {
            return back()->with('error', 'You can only withdraw reports that are still pending.');
        }

        $report->status = 'withdrawn';
        $report->save();

        return back()->with('success', 'Your report has been withdrawn successfully.');
    }

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

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'new_password'     => 'required|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => bcrypt($request->new_password),
        ]);

        return back()->with('settings_success', 'Password changed successfully.');
    }
}