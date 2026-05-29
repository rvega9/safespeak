<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report; 
use App\Models\Response;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller
{
    // Compute unread message count for the nav badge. Reused by dashboard() and messagesIndex().
    private function getUnreadCount()
    {
        $studentId   = Auth::id();
        $myReportIds = Report::where('user_id', $studentId)->pluck('report_id');

        return Response::whereIn('report_id', $myReportIds)
            ->where('user_id', '!=', $studentId)  // sent by guidance, not the student
            ->where('is_read', false)
            ->count();
    }

    // Student dashboard — Report Concern page.
    public function dashboard(Request $request)
    {
        $globalUnreadCount = $this->getUnreadCount();
        return view('student_dashboard', compact('globalUnreadCount'));
    }

    // Handle the "Share your concern" form submission.
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

    // My Reports page.
    public function myReports()
    {
        $reports = Report::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        $globalUnreadCount = $this->getUnreadCount();

        return view('my_reports', compact('reports', 'globalUnreadCount'));
    }

    // View a specific report
    public function viewReport($id)
    {
        $report = Report::where('report_id', $id)->firstOrFail();

        if ($report->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        $globalUnreadCount = $this->getUnreadCount();

        return view('view_report', compact('report', 'globalUnreadCount'));
    }

    // Messages page
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
     * Send a message — returns JSON (used by fetch() for real-time chat).
     * Works for both student and guidance since both share this route.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'report_id'    => 'required|exists:reports,report_id',
            'message_text' => 'required|string|max:5000',
        ]);

        // Make sure the user actually belongs to this report (student = owner, guidance = any)
        $report = Report::findOrFail($request->report_id);
        $user   = auth()->user();

        if ($user->role === 'student' && $report->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message = Response::create([
            'report_id'    => $request->report_id,
            'user_id'      => $user->id,
            'message_text' => $request->message_text,
            'is_read'      => false,
        ]);

        // Return the saved message as JSON so the frontend can render it immediately
        return response()->json([
            'success'    => true,
            'message_id' => $message->message_id,
            'user_id'    => $message->user_id,
            'text'       => $message->message_text,
            'time'       => $message->created_at->format('h:i A'),
            'sender'     => $user->role, // 'student' or 'guidance'
        ]);
    }

    /**
     * Poll for new messages after a given message_id.
     * Called every 3 seconds by the frontend JS.
     */
    public function pollMessages(Request $request, $reportId)
    {
        $report = Report::findOrFail($reportId);
        $user   = auth()->user();

        // Security: students can only poll their own reports
        if ($user->role === 'student' && $report->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $afterId = $request->query('after', 0); // last message_id the client already has

        $messages = Response::where('report_id', $reportId)
            ->where('message_id', '>', $afterId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($user) {
                return [
                    'message_id' => $msg->message_id,
                    'user_id'    => $msg->user_id,
                    'text'       => $msg->message_text,
                    'time'       => $msg->created_at->format('h:i A'),
                    'is_mine'    => ($msg->user_id === $user->id),
                ];
            });

        // Auto-mark incoming messages as read while polling
        Response::where('report_id', $reportId)
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Return updated unread count for nav badge (students only)
        $unreadCount = 0;
        if ($user->role === 'student') {
            $myReportIds = Report::where('user_id', $user->id)->pluck('report_id');
            $unreadCount = Response::whereIn('report_id', $myReportIds)
                ->where('user_id', '!=', $user->id)
                ->where('is_read', false)
                ->count();
        }

        return response()->json([
            'messages'     => $messages,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark all messages in a report as read (called when switching conversations).
     */
    public function markAsRead(Request $request, $reportId)
    {
        $user = auth()->user();

        Response::where('report_id', $reportId)
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * Sidebar poll — returns latest message preview, unread count, and last
     * activity timestamp for every conversation visible to the logged-in user.
     * Called every 3 seconds to keep the sidebar live without a page reload.
     *
     * NOTE: We deliberately avoid using the Report::messages() eager-load for
     * the "latest message" lookup because that relationship has orderBy('asc')
     * baked in. Instead we query the messages table directly per report so we
     * are guaranteed to get the true latest message regardless of relationship
     * ordering. This is the only reliable fix.
     */
    public function sidebarPoll(Request $request)
    {
        $user = auth()->user();

        // 1. Get the report IDs this user is allowed to see
        if ($user->role === 'student') {
            $reportIds = Report::where('user_id', $user->id)->pluck('report_id');
        } else {
            // Guidance sees all non-archived reports
            $reportIds = Report::where('is_archived', false)->pluck('report_id');
        }

        if ($reportIds->isEmpty()) {
            return response()->json(['conversations' => [], 'total_unread' => 0]);
        }

        // 2. For each report, get the single latest message via a subquery
        //    (one DB round-trip using a self-join on max message_id per report)
        $latestMessages = \DB::table('messages as m1')
            ->join(\DB::raw('(SELECT report_id, MAX(message_id) as max_id FROM messages GROUP BY report_id) as m2'),
                function ($join) {
                    $join->on('m1.report_id', '=', 'm2.report_id')
                         ->on('m1.message_id', '=', 'm2.max_id');
                })
            ->whereIn('m1.report_id', $reportIds)
            ->select('m1.report_id', 'm1.message_id', 'm1.user_id', 'm1.message_text', 'm1.created_at')
            ->get()
            ->keyBy('report_id'); // index by report_id for O(1) lookup

        // 3. Count unread messages per report (sent by the other party)
        $unreadCounts = \DB::table('messages')
            ->whereIn('report_id', $reportIds)
            ->where('user_id', '!=', $user->id)
            ->where('is_read', false)
            ->select('report_id', \DB::raw('COUNT(*) as unread'))
            ->groupBy('report_id')
            ->get()
            ->keyBy('report_id');

        // 4. Also get the reports themselves for fallback timestamps
        $reports = Report::whereIn('report_id', $reportIds)
            ->select('report_id', 'created_at')
            ->get()
            ->keyBy('report_id');

        // 5. Build the response array
        $conversations = $reportIds->map(function ($reportId) use ($user, $latestMessages, $unreadCounts, $reports) {
            $latest = $latestMessages->get($reportId);
            $unread = $unreadCounts->get($reportId)?->unread ?? 0;

            // Sender label
            $senderLabel = null;
            if ($latest) {
                if ((int)$latest->user_id === $user->id) {
                    $senderLabel = 'You';
                } elseif ($user->role === 'guidance') {
                    $senderLabel = 'Student';
                } else {
                    $senderLabel = 'Guidance';
                }
            }

            $lastActivity = $latest
                ? \Carbon\Carbon::parse($latest->created_at)->timestamp
                : ($reports->get($reportId)?->created_at?->timestamp ?? 0);

            return [
                'report_id'     => $reportId,
                'unread'        => (int) $unread,
                'latest_text'   => $latest?->message_text ?? null,
                'latest_sender' => $senderLabel,
                'latest_time'   => $latest
                    ? \Carbon\Carbon::parse($latest->created_at)->diffForHumans()
                    : null,
                'last_activity' => $lastActivity,
            ];
        });

        return response()->json([
            'conversations' => $conversations->keyBy('report_id'),
            'total_unread'  => $conversations->sum('unread'),
        ]);
    }

    public function withdrawReport($id)
    {
        $report = Report::where('report_id', $id)->firstOrFail();

        if ($report->user_id !== auth()->id()) {
            abort(403, 'Unauthorized.');
        }

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