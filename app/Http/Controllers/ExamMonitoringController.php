<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;

class ExamMonitoringController extends Controller
{
    public function index(Request $request, Exam $exam)
    {
        if ($request->user()->cannot('monitor', $exam)) {
            abort(403);
        }

        if (! $request->wantsJson()) {
            return view('exams.monitoring.index', compact('exam'));
        }

        // Return JSON monitoring data
        $attempts = ExamAttempt::with(['student.user', 'integrityEvents' => function ($query) {
            $query->latest('server_received_at')->limit(1);
        }])
        ->where('exam_id', $exam->id)
        ->get();

        $data = $attempts->map(function ($attempt) {
            return [
                'id' => $attempt->id,
                'student_id' => $attempt->student_id,
                'student_name' => $attempt->student->user->name,
                'status' => $attempt->status,
                'connection_status' => $attempt->connection_status, // via accessor
                'last_seen_at' => $attempt->last_seen_at ? $attempt->last_seen_at->toIso8601String() : null,
                'last_integrity_event' => $attempt->integrityEvents->first(),
                'integrity_event_count' => $attempt->integrityEvents()->count(),
                'is_locked' => $attempt->is_locked,
                'deadline_at' => $attempt->deadline_at ? $attempt->deadline_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'exam' => [
                'id' => $exam->id,
                'title' => $exam->title,
                'status' => $exam->status,
            ],
            'attempts' => $data,
        ]);
    }
}
