<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\ExamAttempt;
use App\Exports\ExamResultExport;
use App\Exports\ClassResultExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class ExportController extends Controller
{
    /**
     * Sanitize a string for safe use as a filename.
     * Removes /, \, :, *, ?, ", <, >, | and replaces whitespace with _.
     */
    private function sanitizeFilename(string $name): string
    {
        $safe = preg_replace('/[\/\\\\:*?"<>|\s]+/', '_', $name);
        return trim($safe, '_');
    }

    public function exportExamExcel(Request $request, Exam $exam)
    {
        Gate::authorize('results.export');
        Gate::authorize('viewResults', $exam);

        \App\Models\AuditLog::record('export_exam_excel', $exam, null, ['scope' => 'all']);

        $filename = 'Hasil_Ujian_' . $this->sanitizeFilename($exam->title) . '_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new ExamResultExport($exam), $filename);
    }

    public function exportExamPdf(Request $request, Exam $exam)
    {
        Gate::authorize('results.export');
        Gate::authorize('viewResults', $exam);

        \App\Models\AuditLog::record('export_exam_pdf', $exam, null, ['scope' => 'all']);

        $attempts = ExamAttempt::with(['student.user', 'student.schoolClass'])
            ->where('exam_id', $exam->id)
            ->whereIn('status', ['SUBMITTED', 'AUTO_SUBMITTED'])
            ->whereIn('grading_status', ['FINAL', 'AUTO_GRADED', 'GRADED'])
            ->orderBy('id')
            ->get();

        $pdf = Pdf::loadView('exports.pdf.exam_result', compact('exam', 'attempts'));

        $filename = 'Hasil_Ujian_' . $this->sanitizeFilename($exam->title) . '_' . date('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportClassExcel(Request $request, Exam $exam, SchoolClass $schoolClass)
    {
        Gate::authorize('results.export');
        Gate::authorize('viewResults', $exam);

        \App\Models\AuditLog::record('export_class_excel', $exam, null, ['scope' => 'class', 'class_id' => $schoolClass->id]);

        $filename = 'Hasil_Ujian_' . $this->sanitizeFilename($exam->title)
            . '_Kelas_' . $this->sanitizeFilename($schoolClass->name)
            . '_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new ClassResultExport($exam, $schoolClass), $filename);
    }

    public function exportClassPdf(Request $request, Exam $exam, SchoolClass $schoolClass)
    {
        Gate::authorize('results.export');
        Gate::authorize('viewResults', $exam);

        \App\Models\AuditLog::record('export_class_pdf', $exam, null, ['scope' => 'class', 'class_id' => $schoolClass->id]);

        $attempts = ExamAttempt::with(['student.user'])
            ->where('exam_id', $exam->id)
            ->whereHas('student', function ($q) use ($schoolClass) {
                $q->where('school_class_id', $schoolClass->id);
            })
            ->whereIn('status', ['SUBMITTED', 'AUTO_SUBMITTED'])
            ->whereIn('grading_status', ['FINAL', 'AUTO_GRADED', 'GRADED'])
            ->orderBy('id')
            ->get();

        $pdf = Pdf::loadView('exports.pdf.class_result', compact('exam', 'schoolClass', 'attempts'));

        $filename = 'Hasil_Ujian_' . $this->sanitizeFilename($exam->title)
            . '_Kelas_' . $this->sanitizeFilename($schoolClass->name)
            . '_' . date('Ymd_His') . '.pdf';
        return $pdf->download($filename);
    }
}
