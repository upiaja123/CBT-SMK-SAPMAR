<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Exam;
use App\Models\QuestionBank;
use App\Models\Question;
use App\Models\ExamAttempt;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        $stats = [];
        $view = 'dashboard.index';

        if ($user->hasRole('super_admin') || $user->hasRole('kurikulum')) {
            $stats = $this->getAdminStats();
            $view = 'dashboard.admin';
        } elseif ($user->hasRole('guru')) {
            $stats = $this->getGuruStats($user);
            $view = 'dashboard.guru';
        } elseif ($user->hasRole('proktor')) {
            $stats = $this->getProktorStats($user);
            $view = 'dashboard.proktor';
        } elseif ($user->hasRole('siswa')) {
            $stats = $this->getSiswaStats($user);
            $view = 'dashboard.siswa';
        } else {
            $view = 'dashboard';
        }

        return view($view, compact('stats'));
    }

    private function getAdminStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_students' => Student::count(),
            'total_teachers' => Teacher::count(),
            'active_exams' => Exam::where('status', 'PUBLISHED')->where('start_at', '<=', now())->where('end_at', '>=', now())->count(),
            'total_question_banks' => QuestionBank::count(),
            'total_questions' => Question::count(),
            'ongoing_attempts' => ExamAttempt::where('status', 'IN_PROGRESS')->count(),
            'recent_exams' => Exam::with('subject')->latest()->take(5)->get(),
        ];
    }

    private function getGuruStats(User $user): array
    {
        $teacherId = $user->teacher?->id;
        
        $myBanks = QuestionBank::where('teacher_id', $teacherId)->get();
        $bankIds = $myBanks->pluck('id');
        
        return [
            'my_question_banks' => $myBanks->count(),
            'my_questions' => Question::whereIn('question_bank_id', $bankIds)->count(),
            'my_exams' => Exam::where('created_by', $user->id)->count(),
            'active_exams' => Exam::where('created_by', $user->id)->where('status', 'PUBLISHED')->where('start_at', '<=', now())->where('end_at', '>=', now())->count(),
            'recent_banks' => $myBanks->sortByDesc('created_at')->take(5),
        ];
    }

    private function getProktorStats(User $user): array
    {
        return [
            'active_exams' => Exam::where('status', 'PUBLISHED')->where('start_at', '<=', now())->where('end_at', '>=', now())->count(),
            'ongoing_attempts' => ExamAttempt::where('status', 'IN_PROGRESS')->count(),
            'recent_exams' => Exam::with('subject')->where('status', 'PUBLISHED')->latest()->take(5)->get(),
        ];
    }

    private function getSiswaStats(User $user): array
    {
        $student = $user->student;

        $availableExams = Exam::whereHas('participants', function($q) use ($student) {
                $q->where(function($q2) use ($student) {
                    $q2->where('student_id', $student?->id)
                       ->orWhere('school_class_id', $student?->school_class_id);
                });
            })
            ->where('status', 'PUBLISHED')
            ->where(function($q) use ($student) {
                $q->where('end_at', '>=', now())
                  ->orWhereHas('participants', function($q2) use ($student) {
                      $q2->where('student_id', $student?->id)
                         ->where('is_susulan', true)
                         ->where('susulan_end_at', '>=', now());
                  });
            })
            ->with(['subject', 'attempts' => function($q) use ($student) {
                $q->where('student_id', $student?->id);
            }])
            ->orderBy('start_at', 'asc')
            ->get();

        $completedExams = ExamAttempt::where('student_id', $student?->id)->where('status', 'FINALIZED')->count();

        return [
            'available_exams' => $availableExams,
            'completed_exams' => $completedExams,
        ];
    }
}
