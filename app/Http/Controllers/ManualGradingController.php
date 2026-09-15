<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ParticipantAnswer;
use App\Services\ManualGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ManualGradingController extends Controller
{
    protected ManualGradingService $gradingService;

    public function __construct(ManualGradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Display a listing of attempts for an exam that need grading.
     */
    public function index(Exam $exam)
    {
        Gate::authorize('grade', $exam);

        // Fetch attempts for this exam that are either WAITING_MANUAL or FINAL, 
        // essentially attempts that have been finalized and graded.
        $attempts = $exam->attempts()
            ->whereIn('status', ['SUBMITTED', 'AUTO_SUBMITTED'])
            ->with('student.user')
            ->orderByRaw("FIELD(grading_status, 'WAITING_MANUAL', 'NOT_GRADED', 'AUTO_GRADED', 'FINAL')") // Prioritize WAITING_MANUAL
            ->orderBy('submitted_at', 'desc')
            ->paginate(15);

        return view('exams.grading.index', compact('exam', 'attempts'));
    }

    /**
     * Display the manual grading dashboard for a specific attempt.
     */
    public function show(Exam $exam, ExamAttempt $attempt)
    {
        Gate::authorize('grade', $exam);

        // Ensure attempt belongs to exam
        if ($attempt->exam_id !== $exam->id) {
            abort(404);
        }

        // Fetch answers that require manual grading, plus their question snapshots
        $answers = $attempt->participantAnswers()
            ->whereHas('questionSnapshot', function ($query) {
                // Focus on essay for manual grading UI
                $query->where('question_type', 'essay');
            })
            ->with('questionSnapshot')
            ->get();

        return view('exams.grading.show', compact('exam', 'attempt', 'answers'));
    }

    /**
     * Save the manual grade for a participant answer.
     */
    public function update(Request $request, Exam $exam, ExamAttempt $attempt, ParticipantAnswer $participantAnswer)
    {
        Gate::authorize('grade', $exam);

        // Validate relations
        if ($attempt->exam_id !== $exam->id || $participantAnswer->exam_attempt_id !== $attempt->id) {
            abort(404);
        }

        $validated = $request->validate([
            'awarded_score' => ['required', 'numeric', 'min:0', 'max:' . $participantAnswer->max_score],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->gradingService->saveGrade(
                $participantAnswer,
                (float) $validated['awarded_score'],
                $validated['feedback'] ?? null,
                $request->user()
            );

            return redirect()->back()->with('success', 'Nilai berhasil disimpan.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
