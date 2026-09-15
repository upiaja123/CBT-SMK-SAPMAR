<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\IntegrityEvent;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class IntegrityReviewController extends Controller
{
    private function authorizeMonitor(Exam $exam)
    {
        if (! Gate::allows('monitor', $exam)) {
            abort(403, 'Unauthorized to monitor this exam.');
        }
    }

    public function show(Exam $exam, ExamAttempt $attempt)
    {
        $this->authorizeMonitor($exam);
        
        if ($attempt->exam_id !== $exam->id) {
            abort(404);
        }

        $attempt->load(['student.user', 'reviewNotes.reviewer']);

        return view('exams.monitoring.review', compact('exam', 'attempt'));
    }

    public function events(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        $this->authorizeMonitor($exam);

        if ($attempt->exam_id !== $exam->id) {
            abort(404);
        }

        $query = $attempt->integrityEvents()->orderBy('server_received_at', 'asc');

        if ($request->filled('filter') && $request->filter !== 'all') {
            if ($request->filter === 'focus') {
                $query->whereIn('event_type', ['TAB_HIDDEN', 'TAB_VISIBLE', 'WINDOW_BLUR', 'WINDOW_FOCUS']);
            } elseif ($request->filter === 'fullscreen') {
                $query->whereIn('event_type', ['FULLSCREEN_EXIT', 'FULLSCREEN_ENTER']);
            } elseif ($request->filter === 'network') {
                $query->whereIn('event_type', ['NETWORK_OFFLINE', 'NETWORK_ONLINE']);
            }
        }

        $events = $query->paginate(50);

        // Map events to neutral text
        $events->getCollection()->transform(function ($event) {
            $neutralMap = [
                'TAB_HIDDEN' => 'Tab disembunyikan',
                'TAB_VISIBLE' => 'Tab diaktifkan kembali',
                'WINDOW_BLUR' => 'Perubahan fokus keluar',
                'WINDOW_FOCUS' => 'Fokus kembali ke ujian',
                'FULLSCREEN_EXIT' => 'Keluar dari mode fullscreen',
                'FULLSCREEN_ENTER' => 'Masuk ke mode fullscreen',
                'NETWORK_OFFLINE' => 'Koneksi terputus',
                'NETWORK_ONLINE' => 'Koneksi tersambung',
            ];

            $event->description = $neutralMap[$event->event_type] ?? $event->event_type;
            return $event;
        });

        // Summary calculations
        $totalEvents = $attempt->integrityEvents()->count();
        
        // Count specific categories
        $focusCount = $attempt->integrityEvents()->whereIn('event_type', ['TAB_HIDDEN', 'WINDOW_BLUR'])->count();
        $fullscreenCount = $attempt->integrityEvents()->whereIn('event_type', ['FULLSCREEN_EXIT'])->count();
        $networkCount = $attempt->integrityEvents()->whereIn('event_type', ['NETWORK_OFFLINE'])->count();

        $firstEvent = $attempt->integrityEvents()->orderBy('server_received_at', 'asc')->value('server_received_at');
        $lastEvent = $attempt->integrityEvents()->orderBy('server_received_at', 'desc')->value('server_received_at');

        return response()->json([
            'events' => $events,
            'summary' => [
                'total' => $totalEvents,
                'focus_loss' => $focusCount,
                'fullscreen_exit' => $fullscreenCount,
                'network_drop' => $networkCount,
                'first_event' => $firstEvent,
                'last_event' => $lastEvent,
            ]
        ]);
    }

    public function updateState(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        $this->authorizeMonitor($exam);

        if ($attempt->exam_id !== $exam->id) {
            abort(404);
        }

        $request->validate([
            'review_status' => 'required|in:UNREVIEWED,REVIEWED,NOTED'
        ]);

        $oldStatus = $attempt->review_status;
        $newStatus = $request->review_status;

        $attempt->update(['review_status' => $newStatus]);

        AuditLog::record(
            'update_review_state',
            $attempt,
            null,
            null,
            'success',
            "Updated review state from {$oldStatus} to {$newStatus}"
        );

        return response()->json(['message' => 'Review state updated successfully', 'review_status' => $newStatus]);
    }

    public function storeNote(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        $this->authorizeMonitor($exam);

        if ($attempt->exam_id !== $exam->id) {
            abort(404);
        }

        $request->validate([
            'note' => 'required|string|max:1000'
        ]);

        $note = $attempt->reviewNotes()->create([
            'user_id' => $request->user()->id,
            'note' => $request->note,
        ]);

        AuditLog::record(
            'create_review_note',
            $attempt,
            null,
            null,
            'success',
            "Added a review note"
        );

        $note->load('reviewer');

        return response()->json(['message' => 'Review note added successfully', 'note' => $note]);
    }
}
