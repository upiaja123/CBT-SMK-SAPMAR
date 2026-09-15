<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;

class ExamResultController extends Controller
{
    public function index(Request $request, Exam $exam)
    {
        if ($request->user()->cannot('viewResults', $exam)) {
            abort(403);
        }

        $attempts = $exam->attempts()
            ->with(['student.user'])
            ->whereIn('status', ['SUBMITTED', 'AUTO_SUBMITTED'])
            ->latest('updated_at')
            ->paginate(20);

        return view('exams.results.index', [
            'exam' => $exam,
            'attempts' => $attempts,
        ]);
    }

    public function publishResults(Request $request, Exam $exam)
    {
        if ($request->user()->cannot('viewResults', $exam)) {
            abort(403);
        }

        // Additional check: Ensure all attempts are graded before publishing (optional, but good practice)
        $ungraded = $exam->attempts()->where('grading_status', 'WAITING_MANUAL')->count();
        if ($ungraded > 0) {
            return back()->with('error', 'Tidak dapat mempublikasi nilai. Terdapat ' . $ungraded . ' jawaban yang perlu dikoreksi manual.');
        }

        $exam->update([
            'results_published_at' => now(),
        ]);

        \App\Models\AuditLog::record(
            'results_published',
            $exam,
            null,
            null,
            'success',
            'User published results for exam'
        );

        return back()->with('success', 'Nilai ujian berhasil dipublikasikan. Siswa sekarang dapat melihat nilai mereka.');
    }
}
