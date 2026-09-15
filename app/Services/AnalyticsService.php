<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get aggregate analytics for a specific exam.
     */
    public function getExamSummary(Exam $exam): array
    {
        // Get all attempts for this exam
        $stats = ExamAttempt::where('exam_id', $exam->id)
            ->selectRaw('
                COUNT(id) as total_participants,
                SUM(CASE WHEN status = "NOT_STARTED" THEN 1 ELSE 0 END) as not_started,
                SUM(CASE WHEN status = "IN_PROGRESS" THEN 1 ELSE 0 END) as started,
                SUM(CASE WHEN status = "SUBMITTED" THEN 1 ELSE 0 END) as submitted,
                SUM(CASE WHEN status = "AUTO_SUBMITTED" THEN 1 ELSE 0 END) as auto_submitted,
                SUM(CASE WHEN grading_status = "WAITING_MANUAL" THEN 1 ELSE 0 END) as waiting_manual,
                SUM(CASE WHEN grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND status IN ("SUBMITTED", "AUTO_SUBMITTED") THEN 1 ELSE 0 END) as final_count,
                
                -- Score metrics only for FINAL status and valid submit status
                AVG(CASE WHEN grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND status IN ("SUBMITTED", "AUTO_SUBMITTED") AND max_total_score > 0 THEN (total_score / max_total_score * 100) ELSE NULL END) as avg_score,
                MAX(CASE WHEN grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND status IN ("SUBMITTED", "AUTO_SUBMITTED") AND max_total_score > 0 THEN (total_score / max_total_score * 100) ELSE NULL END) as highest_score,
                MIN(CASE WHEN grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND status IN ("SUBMITTED", "AUTO_SUBMITTED") AND max_total_score > 0 THEN (total_score / max_total_score * 100) ELSE NULL END) as lowest_score
            ')
            ->first();

        // Calculate median for FINAL status attempts
        $finalScores = ExamAttempt::where('exam_id', $exam->id)
            ->whereIn('status', ['SUBMITTED', 'AUTO_SUBMITTED'])
            ->whereIn('grading_status', ['FINAL', 'AUTO_GRADED', 'GRADED'])
            ->where('max_total_score', '>', 0)
            ->get()
            ->map(function ($attempt) {
                return ($attempt->total_score / $attempt->max_total_score) * 100;
            })
            ->sort()
            ->values();
        
        $medianScore = null;
        if ($finalScores->isNotEmpty()) {
            $count = $finalScores->count();
            $mid = floor($count / 2);
            if ($count % 2 === 0) {
                $medianScore = ($finalScores[$mid - 1] + $finalScores[$mid]) / 2;
            } else {
                $medianScore = $finalScores[$mid];
            }
        }

        // Pass rate logic: Fixed to 60 as standard threshold.
        $passThreshold = 60;
        
        $passCount = $finalScores->filter(fn($score) => $score >= $passThreshold)->count();
        $failCount = $finalScores->filter(fn($score) => $score < $passThreshold)->count();
        $passPercentage = $stats->final_count > 0 ? ($passCount / $stats->final_count) * 100 : null;

        return [
            'total_participants' => (int) $stats->total_participants,
            'not_started' => (int) $stats->not_started,
            'started' => (int) $stats->started,
            'submitted' => (int) $stats->submitted,
            'auto_submitted' => (int) $stats->auto_submitted,
            'waiting_manual' => (int) $stats->waiting_manual,
            'final' => (int) $stats->final_count,
            'avg_score' => $stats->avg_score !== null ? (float) $stats->avg_score : null,
            'highest_score' => $stats->highest_score !== null ? (float) $stats->highest_score : null,
            'lowest_score' => $stats->lowest_score !== null ? (float) $stats->lowest_score : null,
            'median_score' => $medianScore !== null ? (float) $medianScore : null,
            'pass_count' => $passCount,
            'fail_count' => $failCount,
            'pass_percentage' => $passPercentage !== null ? (float) $passPercentage : null,
        ];
    }
    
    /**
     * Get aggregate analytics grouped by class.
     */
    public function getClassAnalytics(array $filters = [])
    {
        $query = DB::table('school_classes')
            ->join('students', 'school_classes.id', '=', 'students.school_class_id')
            ->join('exam_attempts', 'students.id', '=', 'exam_attempts.student_id')
            ->join('exams', 'exam_attempts.exam_id', '=', 'exams.id')
            ->select('school_classes.id', 'school_classes.name', 'school_classes.major_id', 'school_classes.grade')
            ->selectRaw('COUNT(DISTINCT exam_attempts.id) as attempt_count')
            ->selectRaw('COUNT(DISTINCT students.id) as student_count')
            ->selectRaw('SUM(CASE WHEN exam_attempts.status IN ("SUBMITTED", "AUTO_SUBMITTED") THEN 1 ELSE 0 END) as completed_count')
            ->selectRaw('AVG(CASE WHEN exam_attempts.grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND exam_attempts.status IN ("SUBMITTED", "AUTO_SUBMITTED") AND exam_attempts.max_total_score > 0 THEN (exam_attempts.total_score / exam_attempts.max_total_score * 100) ELSE NULL END) as avg_score')
            ->selectRaw('MAX(CASE WHEN exam_attempts.grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND exam_attempts.status IN ("SUBMITTED", "AUTO_SUBMITTED") AND exam_attempts.max_total_score > 0 THEN (exam_attempts.total_score / exam_attempts.max_total_score * 100) ELSE NULL END) as highest_score')
            ->selectRaw('MIN(CASE WHEN exam_attempts.grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND exam_attempts.status IN ("SUBMITTED", "AUTO_SUBMITTED") AND exam_attempts.max_total_score > 0 THEN (exam_attempts.total_score / exam_attempts.max_total_score * 100) ELSE NULL END) as lowest_score')
            ->selectRaw('SUM(CASE WHEN exam_attempts.grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND exam_attempts.status IN ("SUBMITTED", "AUTO_SUBMITTED") AND exam_attempts.max_total_score > 0 AND (exam_attempts.total_score / exam_attempts.max_total_score * 100) >= 60 THEN 1 ELSE 0 END) as pass_count')
            ->selectRaw('SUM(CASE WHEN exam_attempts.grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND exam_attempts.status IN ("SUBMITTED", "AUTO_SUBMITTED") THEN 1 ELSE 0 END) as final_count');

        if (!empty($filters['exam_id'])) {
            $query->where('exam_attempts.exam_id', $filters['exam_id']);
        }
        
        if (!empty($filters['academic_year_id'])) {
            $query->where('school_classes.academic_year_id', $filters['academic_year_id']);
        }

        if (!empty($filters['subject_id'])) {
            $query->where('exams.subject_id', $filters['subject_id']);
        }

        return $query->groupBy('school_classes.id', 'school_classes.name', 'school_classes.major_id', 'school_classes.grade')
            ->orderBy('school_classes.grade')
            ->orderBy('school_classes.name')
            ->get()
            ->map(function ($row) {
                $row->completion_rate = $row->attempt_count > 0 ? ($row->completed_count / $row->attempt_count) * 100 : 0;
                $row->pass_rate = $row->final_count > 0 ? ($row->pass_count / $row->final_count) * 100 : 0;
                return $row;
            });
    }

    /**
     * Get aggregate analytics grouped by subject.
     */
    public function getSubjectAnalytics(array $filters = [])
    {
        $query = DB::table('subjects')
            ->join('exams', 'subjects.id', '=', 'exams.subject_id')
            ->join('exam_attempts', 'exams.id', '=', 'exam_attempts.exam_id')
            ->select('subjects.id', 'subjects.name', 'subjects.code')
            ->selectRaw('COUNT(DISTINCT exams.id) as exam_count')
            ->selectRaw('COUNT(DISTINCT exam_attempts.student_id) as participant_count')
            ->selectRaw('AVG(CASE WHEN exam_attempts.grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND exam_attempts.status IN ("SUBMITTED", "AUTO_SUBMITTED") AND exam_attempts.max_total_score > 0 THEN (exam_attempts.total_score / exam_attempts.max_total_score * 100) ELSE NULL END) as avg_score')
            ->selectRaw('SUM(CASE WHEN exam_attempts.grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND exam_attempts.status IN ("SUBMITTED", "AUTO_SUBMITTED") AND exam_attempts.max_total_score > 0 AND (exam_attempts.total_score / exam_attempts.max_total_score * 100) >= 60 THEN 1 ELSE 0 END) as pass_count')
            ->selectRaw('SUM(CASE WHEN exam_attempts.grading_status IN ("FINAL", "AUTO_GRADED", "GRADED") AND exam_attempts.status IN ("SUBMITTED", "AUTO_SUBMITTED") THEN 1 ELSE 0 END) as final_count');

        if (!empty($filters['academic_year_id'])) {
            $query->where('subjects.academic_year_id', $filters['academic_year_id']);
        }

        return $query->groupBy('subjects.id', 'subjects.name', 'subjects.code')
            ->orderBy('subjects.name')
            ->get()
            ->map(function ($row) {
                $row->pass_rate = $row->final_count > 0 ? ($row->pass_count / $row->final_count) * 100 : 0;
                return $row;
            });
    }
}
