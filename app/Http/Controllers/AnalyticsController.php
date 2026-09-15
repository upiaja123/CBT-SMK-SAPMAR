<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        // For Super Admin and Kurikulum, show overall dashboard.
        // For Teacher, they only see stats for their subjects or exams. 
        // We will restrict in UI/Query.
        
        $filters = $request->only(['academic_year_id', 'subject_id']);
        
        // Subject analytics
        $subjectAnalytics = $this->analyticsService->getSubjectAnalytics($filters);

        return view('analytics.index', [
            'subjectAnalytics' => $subjectAnalytics,
        ]);
    }

    public function exam(Request $request, Exam $exam)
    {
        // Authorization:
        // Super Admin / Kurikulum can view
        // Teacher can view if they created it (or maybe assigned to the subject, but let's stick to simple policy or check 'viewResults' policy)
        if ($request->user()->cannot('viewResults', $exam)) {
            abort(403);
        }

        $summary = $this->analyticsService->getExamSummary($exam);
        
        $classAnalytics = $this->analyticsService->getClassAnalytics(['exam_id' => $exam->id]);

        return view('analytics.exam', [
            'exam' => $exam,
            'summary' => $summary,
            'classAnalytics' => $classAnalytics,
        ]);
    }
}
