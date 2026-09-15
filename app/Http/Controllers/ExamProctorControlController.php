<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExamProctorControlController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ExamAttemptService $attemptService)
    {
    }

    private function resolveAttempt(Exam $exam, ExamAttempt $attempt): ExamAttempt
    {
        // Ensure attempt belongs to this exam
        if ($attempt->exam_id !== $exam->id) {
            abort(404, 'Attempt does not belong to this exam.');
        }
        return $attempt;
    }

    public function addExtraTime(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        $this->authorize('control', $exam);
        $attempt = $this->resolveAttempt($exam, $attempt);

        $validated = $request->validate([
            'minutes' => 'required|integer|min:1|max:120',
        ]);

        try {
            $this->attemptService->addExtraTime($attempt, $validated['minutes'], $request->user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Extra time added successfully.',
            'deadline_at' => $attempt->fresh()->deadline_at?->toIso8601String(),
        ]);
    }

    public function lock(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        $this->authorize('control', $exam);
        $attempt = $this->resolveAttempt($exam, $attempt);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->attemptService->lockAttempt($attempt, $request->user(), $validated['reason'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Attempt locked.']);
    }

    public function unlock(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        $this->authorize('control', $exam);
        $attempt = $this->resolveAttempt($exam, $attempt);

        try {
            $this->attemptService->unlockAttempt($attempt, $request->user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Attempt unlocked.']);
    }

    public function forceSubmit(Request $request, Exam $exam, ExamAttempt $attempt)
    {
        $this->authorize('control', $exam);
        $attempt = $this->resolveAttempt($exam, $attempt);

        try {
            $this->attemptService->forceSubmit($attempt, $request->user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Attempt force-submitted successfully.']);
    }
}
