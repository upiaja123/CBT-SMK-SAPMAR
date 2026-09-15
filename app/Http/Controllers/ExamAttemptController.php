<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExamAttemptController extends Controller
{
    protected $attemptService;

    public function __construct(\App\Services\ExamAttemptService $attemptService)
    {
        $this->attemptService = $attemptService;
    }

    public function store(Request $request, \App\Models\Exam $exam)
    {
        $user = $request->user();
        if (!$user->hasRole('siswa') || !$user->student) {
            return back()->with('error', 'Hanya siswa yang dapat memulai ujian.');
        }

        try {
            $attempt = $this->attemptService->startAttempt($exam, $user->student);

            return redirect()->route('exams.attempts.session', ['exam' => $exam->id, 'attempt' => $attempt->id]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return back()->with('error', 'tidak bisa masuk silahkan hubungi admin');
        }
    }

    public function show(Request $request, \App\Models\Exam $exam, \App\Models\ExamAttempt $attempt)
    {
        // 1. Authorization
        if ($request->user()->cannot('view', $attempt)) {
            abort(403);
        }

        // 2. Fetch snapshots and answers
        $attempt->load(['questionSnapshots.optionSnapshots', 'questionSnapshots.participantAnswer', 'questionSnapshots.originalVersion.media']);

        // 3. Map for student reading (do NOT expose is_correct or correct_answer)
        $questions = $attempt->questionSnapshots->map(function ($q) {
            $answerPayload = $q->participantAnswer ? $q->participantAnswer->answer : null;
            $leftItems = [];
            $rightItems = [];

            if ($q->question_type === 'matching' && $q->scoring_metadata && isset($q->scoring_metadata['pairs'])) {
                foreach ($q->scoring_metadata['pairs'] as $pair) {
                    $leftItems[] = $pair['left'];
                    $rightItems[] = $pair['right'];
                }
                shuffle($rightItems); // Randomize the right side for the student
            }

            return [
                'id' => $q->id,
                'content' => $q->content,
                'question_type' => $q->question_type,
                'order' => $q->order,
                'participant_answer' => $answerPayload,
                'left_items' => $leftItems,
                'right_items' => $rightItems,
                'media' => $q->originalVersion ? $q->originalVersion->media->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'url' => route('media.show', $m->id),
                        'mime_type' => $m->mime_type,
                    ];
                }) : [],
                'options' => $q->optionSnapshots->map(function ($opt) {
                    return [
                        'id' => $opt->id,
                        'content' => $opt->content,
                        'order' => $opt->order,
                        // STRIPPED: is_correct
                        // STRIPPED: weight
                    ];
                })
            ];
        });

        return response()->json([
            'attempt_id' => $attempt->id,
            'status' => $attempt->status,
            'is_locked' => $attempt->is_locked,
            'started_at' => $attempt->started_at,
            'deadline_at' => $attempt->deadline_at,
            'server_now' => now(),
            'questions' => $questions
        ]);
    }

    public function session(Request $request, \App\Models\Exam $exam, \App\Models\ExamAttempt $attempt)
    {
        if ($request->user()->cannot('view', $attempt)) {
            abort(403);
        }

        $serverNow = now();

        // Proactive finalization on view if past deadline
        if ($attempt->status === 'IN_PROGRESS' && $attempt->deadline_at && $serverNow->gt($attempt->deadline_at)) {
            $this->attemptService->finalizeAttempt($attempt, true);
            $attempt->refresh();
        }

        // Prevent accessing session if already finalized/submitted
        if (in_array($attempt->status, ['SUBMITTED', 'AUTO_SUBMITTED', 'FINALIZED'])) {
            return redirect()->route('dashboard')->with('success', 'Ujian telah selesai dikerjakan.');
        }

        $attempt->load('exam');

        return view('exams.session', [
            'exam' => $attempt->exam,
            'attempt' => $attempt,
        ]);
    }

    public function saveAnswer(Request $request, \App\Models\Exam $exam, \App\Models\ExamAttempt $attempt)
    {
        // 1. Authorization
        if ($request->user()->cannot('view', $attempt)) {
            abort(403);
        }

        if ($exam->status === 'PAUSED') {
            return response()->json(['message' => 'Ujian sedang dijeda oleh panitia. Mohon tunggu.'], 403);
        }

        // 2. Validate request
        $validated = $request->validate([
            'attempt_question_snapshot_id' => 'required|integer',
            'answer' => 'required',
            'client_timestamp' => 'required|integer',
        ]);

        // 3. Delegate to service
        try {
            $this->attemptService->saveAnswer(
                $attempt,
                $validated['attempt_question_snapshot_id'],
                $validated['answer'],
                $validated['client_timestamp']
            );

            return response()->json(['message' => 'Answer saved successfully.']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function submit(Request $request, \App\Models\Exam $exam, \App\Models\ExamAttempt $attempt)
    {
        if ($request->user()->cannot('view', $attempt)) {
            abort(403);
        }

        if ($exam->status === 'PAUSED') {
            return response()->json(['message' => 'Ujian sedang dijeda oleh panitia. Mohon tunggu.'], 403);
        }

        $this->attemptService->finalizeAttempt($attempt, false);

        return response()->json(['message' => 'Ujian berhasil dikumpulkan.']);
    }

    public function result(Request $request, \App\Models\Exam $exam, \App\Models\ExamAttempt $attempt)
    {
        // Must be able to view result (policy ensures status is SUBMITTED/AUTO_SUBMITTED and correct ownership)
        if ($request->user()->cannot('viewResult', $attempt)) {
            abort(403);
        }

        $attempt->load([
            'exam', 
            'questionSnapshots.optionSnapshots', 
            'questionSnapshots.participantAnswer'
        ]);

        // Audit logging if the student is viewing it
        if ($request->user()->hasRole('siswa')) {
            \App\Models\AuditLog::record(
                'exam_result_viewed',
                $attempt,
                null,
                null,
                'success',
                'Student viewed exam result'
            );
        }

        return view('exams.result', [
            'exam' => $attempt->exam,
            'attempt' => $attempt,
        ]);
    }

    public function heartbeat(Request $request, \App\Models\Exam $exam, \App\Models\ExamAttempt $attempt)
    {
        if ($request->user()->cannot('heartbeat', $attempt)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($attempt->exam_id !== $exam->id) {
            return response()->json(['message' => 'Invalid attempt'], 400);
        }

        if ($attempt->status !== 'IN_PROGRESS') {
            return response()->json(['message' => 'Attempt is not active'], 400);
        }

        $previousStatus = $attempt->connection_status;

        $attempt->update(['last_seen_at' => now()]);

        if ($previousStatus !== 'ONLINE') {
            event(new \App\Events\ExamAttemptPresenceUpdated($attempt));
        }

        return response()->json([
            'status' => 'success',
            'server_time' => now()->toIso8601String()
        ]);
    }

    public function storeIntegrityEvent(Request $request, \App\Models\Exam $exam, \App\Models\ExamAttempt $attempt)
    {
        if ($request->user()->cannot('storeIntegrityEvent', $attempt)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($attempt->exam_id !== $exam->id) {
            return response()->json(['message' => 'Invalid attempt'], 400);
        }

        if ($attempt->status !== 'IN_PROGRESS') {
            return response()->json(['message' => 'Attempt is not active'], 400);
        }

        $validated = $request->validate([
            'event_type' => 'required|string|in:TAB_HIDDEN,TAB_VISIBLE,WINDOW_BLUR,WINDOW_FOCUS,FULLSCREEN_EXIT,FULLSCREEN_ENTER,NETWORK_OFFLINE,NETWORK_ONLINE',
            'occurred_at' => 'required|date',
            'metadata' => 'nullable|array'
        ]);

        // Rate limiting or duplication prevention:
        // Do not insert if the same event_type occurred in the last 2 seconds.
        $recentEvent = \App\Models\IntegrityEvent::where('exam_attempt_id', $attempt->id)
            ->where('event_type', $validated['event_type'])
            ->where('server_received_at', '>=', now()->subSeconds(2))
            ->exists();

        if ($recentEvent) {
            // Silently ignore rapid duplicates
            return response()->json(['status' => 'ignored', 'reason' => 'duplicate']);
        }

        $event = \App\Models\IntegrityEvent::create([
            'exam_attempt_id' => $attempt->id,
            'student_id' => $attempt->student_id,
            'event_type' => $validated['event_type'],
            'occurred_at' => \Carbon\Carbon::parse($validated['occurred_at']),
            'server_received_at' => now(),
            'metadata' => $validated['metadata'] ?? null,
        ]);

        event(new \App\Events\IntegrityEventRecorded($event));

        return response()->json(['status' => 'success']);
    }
}
