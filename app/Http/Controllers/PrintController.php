<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\View\View;

class PrintController extends Controller
{
    /**
     * Print exam cards for a specific class
     */
    public function printCards(SchoolClass $class): View
    {
        // Make sure user has permission (Admin/Kurikulum)
        $this->authorize('users.manage');

        $students = Student::with('user')->where('school_class_id', $class->id)->get();

        return view('prints.exam_cards', compact('class', 'students'));
    }

    /**
     * Print attendance list (Daftar Hadir) for an exam
     */
    public function printAttendance(Request $request, Exam $exam)
    {
        // Must be authorized to view this exam (Admin/Kurikulum or Guru who created it)
        if (!auth()->user()->hasRole('super_admin') && !auth()->user()->hasRole('kurikulum') && $exam->created_by !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->query('format') === 'excel') {
            $safeTitle = preg_replace('/[\/\\\\:*?"<>|\s]+/', '_', $exam->title);
            $safeTitle = trim($safeTitle, '_');
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\AttendanceExport($exam),
                'Daftar_Hadir_' . $safeTitle . '.xlsx'
            );
        }

        // Get all eligible students for the attendance list
        $students = $exam->eligible_students;
        
        return view('prints.attendance', compact('exam', 'students'));
    }

    /**
     * Print Exam Minutes (Berita Acara)
     */
    public function printBeritaAcara(Exam $exam): View
    {
        if (!auth()->user()->hasRole('super_admin') && !auth()->user()->hasRole('kurikulum') && $exam->created_by !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $participants = $exam->participants()->get();
        $attempts = ExamAttempt::where('exam_id', $exam->id)->get();

        return view('prints.berita_acara', compact('exam', 'participants', 'attempts'));
    }
}
