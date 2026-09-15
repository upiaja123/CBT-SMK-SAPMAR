<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Scope the exams based on role
        $query = Exam::with(['subject', 'creator'])
                     ->withCount('attempts as total_participants')
                     ->withCount(['attempts as finished_participants' => function ($q) {
                         $q->whereIn('status', ['SUBMITTED', 'AUTO_SUBMITTED', 'FINALIZED']);
                     }]);

        if ($user->hasRole('siswa')) {
            $attempts = \App\Models\ExamAttempt::with(['exam.subject', 'exam.creator'])
                        ->where('student_id', $user->student->id ?? null)
                        ->whereIn('status', ['SUBMITTED', 'AUTO_SUBMITTED', 'FINALIZED'])
                        ->orderBy('updated_at', 'desc')
                        ->paginate(15);
            return view('reports.student', compact('attempts'));
        }

        if ($user->hasRole('guru')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id);
                if ($user->teacher) {
                    $q->orWhereHas('subject.teachers', function ($subQ) use ($user) {
                        $subQ->where('teachers.id', $user->teacher->id);
                    });
                }
            });
        } elseif (!$user->hasAnyRole(['super_admin', 'kurikulum'])) {
            abort(403, 'Akses ditolak.');
        }

        $exams = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('reports.index', compact('exams'));
    }
}
