<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\IntegrityEvent;
use App\Models\ProctorExamAssignment;
use App\Services\ExamAttemptService;
use Illuminate\Http\Request;

class ExamMonitoringController extends Controller
{
    public function __construct(private ExamAttemptService $attemptService) {}

    /**
     * Daftar semua ujian yang bisa dimonitor — entry point dari sidebar.
     */
    public function list(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('super_admin') || $user->hasRole('kurikulum')) {
            $exams = Exam::whereIn('status', ['PUBLISHED', 'ACTIVE', 'PAUSED'])
                ->with(['subject'])->latest()->get();

        } elseif ($user->hasRole('proktor')) {
            $examIds = ProctorExamAssignment::where('proctor_id', $user->id)
                ->where('active', true)->pluck('exam_id');
            $exams = Exam::whereIn('id', $examIds)
                ->whereIn('status', ['PUBLISHED', 'ACTIVE', 'PAUSED'])
                ->with(['subject'])->latest()->get();

        } elseif ($user->hasRole('guru')) {
            $exams = Exam::where('created_by', $user->id)
                ->whereIn('status', ['PUBLISHED', 'ACTIVE', 'PAUSED'])
                ->with(['subject'])->latest()->get();

        } else {
            abort(403, 'Anda tidak memiliki akses monitoring.');
        }

        $exams->each(function ($exam) {
            $exam->active_count  = $exam->attempts()->where('status', 'IN_PROGRESS')->count();
            $exam->total_count   = $exam->attempts()->count();
            $exam->alert_count   = IntegrityEvent::whereHas('attempt', fn($q) => $q->where('exam_id', $exam->id))->count();
            $exam->locked_count  = $exam->attempts()->whereNotNull('locked_at')->where('status', 'IN_PROGRESS')->count();
        });

        return view('exams.monitoring.list', compact('exams'));
    }

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
            'exam'     => ['id' => $exam->id, 'title' => $exam->title, 'status' => $exam->status],
            'attempts' => $data,
        ]);
    }

    /**
     * Buka kunci sesi siswa langsung dari halaman Review Integritas.
     */
    public function unlockFromReview(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        if ($request->user()->cannot('control', $exam)) {
            abort(403);
        }

        if ($attempt->exam_id !== $exam->id) {
            abort(404);
        }

        try {
            $this->attemptService->unlockAttempt($attempt, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Sesi ujian {$attempt->student->user->name} berhasil dibuka kunci. Siswa dapat melanjutkan ujian.");
    }

    /**
     * Buka kembali sesi ujian yang sudah dikumpulkan secara otomatis (AUTO_SUBMITTED) atau (SUBMITTED).
     */
    public function reopenFromReview(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        if ($request->user()->cannot('control', $exam)) {
            abort(403);
        }

        if ($attempt->exam_id !== $exam->id) {
            abort(404);
        }

        try {
            $this->attemptService->reopenAttempt($attempt, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Sesi ujian {$attempt->student->user->name} berhasil dikembalikan ke status IN_PROGRESS. Siswa dapat melanjutkan ujian.");
    }
}

