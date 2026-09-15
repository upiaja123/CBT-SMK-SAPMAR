<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamParticipant;
use App\Models\ExamQuestion;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExamBuilderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Exam::class);
        $user = $request->user();
        
        $query = Exam::with(['subject', 'creator'])->latest();
        
        if ($user->hasRole('guru')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id);
                if ($user->teacher) {
                    $q->orWhereHas('subject.teachers', function ($subQ) use ($user) {
                        $subQ->where('teachers.id', $user->teacher->id);
                    });
                }
            });
        }
        
        $exams = $query->paginate(15);
        return view('exams.index', compact('exams'));
    }

    public function create()
    {
        $this->authorize('create', Exam::class);
        if (auth()->user()->hasRole('guru') && auth()->user()->teacher) {
            $subjects = auth()->user()->teacher->subjects()->where('is_active', true)->get();
            $classes = auth()->user()->teacher->classes()->with('major')->get();
            $students = \App\Models\Student::whereIn('school_class_id', $classes->pluck('id'))->with('user', 'schoolClass')->get();
        } else {
            $subjects = \App\Models\Subject::where('is_active', true)->get();
            $classes = \App\Models\SchoolClass::with('major')->get();
            $students = \App\Models\Student::with('user', 'schoolClass')->get();
        }
        
        // Only load question banks user has access to
        $banksQuery = \App\Models\QuestionBank::with(['questions.currentVersion.options', 'subject']);
        if (auth()->user()->hasRole('guru')) {
            $banksQuery->where('teacher_id', auth()->user()->teacher?->id);
        }
        $banks = $banksQuery->get();

        return view('exams.builder', compact('subjects', 'classes', 'students', 'banks'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Exam::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|unique:exams,code',
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
            'exam_type' => 'nullable|string',
            'grade' => 'nullable|in:X,XI,XII',
            'duration' => 'required|integer|min:1',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'random_question' => 'boolean',
            'random_option' => 'boolean',
            'review_summary' => 'boolean',
        ]);

        $validated['token'] = strtoupper(Str::random(6));
        $validated['status'] = 'DRAFT';
        $validated['created_by'] = auth()->id();

        $exam = Exam::create($validated);

        AuditLog::record('exam.create', $exam, null, $exam->toArray(), 'success', 'Ujian dibuat');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Ujian berhasil dibuat.',
                'exam' => $exam
            ]);
        }

        return redirect()->route('exams.show', $exam)->with('success', 'Ujian berhasil dibuat.');
    }

    public function show(Exam $exam)
    {
        $this->authorize('view', $exam);
        $exam->load(['subject', 'participants.schoolClass', 'participants.student.user', 'examQuestions.questionVersion.question']);
        return view('exams.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        $this->authorize('update', $exam);

        if (auth()->user()->hasRole('guru') && auth()->user()->teacher) {
            $subjects = auth()->user()->teacher->subjects()->where('is_active', true)->get();
        } else {
            $subjects = \App\Models\Subject::where('is_active', true)->get();
        }
        if (auth()->user()->hasRole('guru') && auth()->user()->teacher) {
            $classes = auth()->user()->teacher->classes()->with('major')->get();
            $students = \App\Models\Student::whereIn('school_class_id', $classes->pluck('id'))->with('user', 'schoolClass')->get();
        } else {
            $classes = \App\Models\SchoolClass::with('major')->get();
            $students = \App\Models\Student::with('user', 'schoolClass')->get();
        }
        
        $banksQuery = \App\Models\QuestionBank::with(['questions.currentVersion.options', 'subject']);
        if (auth()->user()->hasRole('guru')) {
            $banksQuery->where('teacher_id', auth()->user()->teacher?->id);
        }
        $banks = $banksQuery->get();

        $exam->load(['participants', 'examQuestions.questionVersion']);

        return view('exams.builder', compact('exam', 'subjects', 'classes', 'students', 'banks'));
    }

    public function update(Request $request, Exam $exam)
    {
        $this->authorize('update', $exam);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'required|string|unique:exams,code,' . $exam->id,
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
            'exam_type' => 'nullable|string',
            'grade' => 'nullable|in:X,XI,XII',
            'duration' => 'required|integer|min:1',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after_or_equal:start_at',
            'random_question' => 'boolean',
            'random_option' => 'boolean',
            'review_summary' => 'boolean',
        ]);

        $exam->update($validated);

        AuditLog::record('exam.update', $exam, null, $exam->toArray(), 'success', 'Informasi dasar ujian diperbarui');

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Ujian berhasil diperbarui.',
                'exam' => $exam
            ]);
        }

        return redirect()->route('exams.show', $exam)->with('success', 'Ujian berhasil diperbarui.');
    }

    public function updateParticipants(Request $request, Exam $exam)
    {
        $this->authorize('update', $exam);

        if (!in_array($exam->status, ['DRAFT', 'PAUSED'])) {
            abort(403, 'Peserta tidak dapat diubah karena ujian sudah berjalan atau dipublikasikan. (Jeda ujian terlebih dahulu)');
        }

        $validated = $request->validate([
            'school_class_ids' => 'nullable|array',
            'school_class_ids.*' => 'exists:school_classes,id',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        DB::transaction(function () use ($validated, $exam) {
            // Delete existing
            $exam->participants()->delete();

            if (!empty($validated['school_class_ids'])) {
                foreach ($validated['school_class_ids'] as $classId) {
                    ExamParticipant::create([
                        'exam_id' => $exam->id,
                        'school_class_id' => $classId,
                    ]);
                }
            }

            if (!empty($validated['student_ids'])) {
                foreach ($validated['student_ids'] as $studentId) {
                    ExamParticipant::create([
                        'exam_id' => $exam->id,
                        'student_id' => $studentId,
                    ]);
                }
            }
        });

        AuditLog::record('exam.participants_updated', $exam, null, $validated, 'success', 'Peserta ujian diperbarui');

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Peserta berhasil diperbarui.']);
        }

        return back()->with('success', 'Peserta berhasil diperbarui.');
    }

    public function updateQuestions(Request $request, Exam $exam)
    {
        $this->authorize('update', $exam);

        if (!in_array($exam->status, ['DRAFT', 'PAUSED'])) {
            abort(403, 'Soal tidak dapat diubah karena ujian sudah berjalan atau dipublikasikan. (Jeda ujian terlebih dahulu)');
        }

        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.question_version_id' => 'required|exists:question_versions,id',
            'questions.*.order' => 'required|integer',
            'questions.*.weight' => 'required|numeric',
        ]);

        DB::transaction(function () use ($validated, $exam) {
            $exam->examQuestions()->delete();

            foreach ($validated['questions'] as $q) {
                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'question_version_id' => $q['question_version_id'],
                    'order' => $q['order'],
                    'weight' => $q['weight'],
                ]);
            }
        });

        AuditLog::record('exam.questions_updated', $exam, null, $validated, 'success', 'Soal ujian diperbarui');

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Soal berhasil diperbarui.']);
        }

        return back()->with('success', 'Soal berhasil diperbarui.');
    }

    public function publish(Request $request, Exam $exam)
    {
        $this->authorize('publish', $exam);

        if ($exam->status !== 'DRAFT') {
            abort(403, 'Ujian ini sudah dipublikasi atau tidak dalam status draft.');
        }

        // Validate that exam has questions and participants
        if ($exam->examQuestions()->count() === 0) {
            return back()->with('error', 'Tidak dapat mempublikasi ujian: Belum ada soal yang ditambahkan.');
        }

        // If you want to strictly require participants before publish:
        // if ($exam->participants()->count() === 0) {
        //     return back()->with('error', 'Tidak dapat mempublikasi ujian: Belum ada peserta yang diatur.');
        // }

        $exam->update([
            'status' => 'PUBLISHED'
        ]);

        AuditLog::record('exam.published', $exam, null, null, 'success', 'Ujian dipublikasikan');

        return redirect()->route('exams.index')->with('success', 'Ujian berhasil dipublikasikan dan sekarang dapat diakses oleh peserta pada jadwal yang ditentukan.');
    }

    public function pause(Request $request, Exam $exam)
    {
        $this->authorize('pause', $exam);

        if ($exam->status !== 'PUBLISHED') {
            abort(403, 'Hanya ujian yang sudah dipublikasikan yang dapat dijeda.');
        }

        $exam->update(['status' => 'PAUSED']);

        AuditLog::record('exam.paused', $exam, null, null, 'success', 'Ujian dijeda oleh panitia');

        return back()->with('success', 'Ujian berhasil dijeda. Siswa tidak dapat mengirim jawaban saat ini. Anda dapat mengedit soal/peserta sekarang.');
    }

    public function unpause(Request $request, Exam $exam)
    {
        $this->authorize('pause', $exam);

        if ($exam->status !== 'PAUSED') {
            abort(403, 'Hanya ujian yang sedang dijeda yang dapat dilanjutkan.');
        }

        $exam->update(['status' => 'PUBLISHED']);

        AuditLog::record('exam.unpaused', $exam, null, null, 'success', 'Ujian dilanjutkan oleh panitia');

        return back()->with('success', 'Ujian berhasil dilanjutkan.');
    }

    public function updateSusulan(Request $request, Exam $exam)
    {
        $this->authorize('update', $exam);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'susulan_start_at' => 'nullable|date',
            'susulan_end_at' => 'required|date|after_or_equal:susulan_start_at',
        ]);

        // Delete any existing susulan for this student
        ExamParticipant::where('exam_id', $exam->id)
            ->where('student_id', $validated['student_id'])
            ->where('is_susulan', true)
            ->delete();

        // Create the susulan participant entry
        ExamParticipant::create([
            'exam_id' => $exam->id,
            'student_id' => $validated['student_id'],
            'is_susulan' => true,
            'susulan_start_at' => $validated['susulan_start_at'],
            'susulan_end_at' => $validated['susulan_end_at'],
        ]);

        AuditLog::record('exam.susulan_assigned', $exam, null, $validated, 'success', 'Peserta ditambahkan untuk ujian susulan');

        return back()->with('success', 'Jadwal susulan berhasil diatur untuk siswa tersebut.');
    }

    public function addEkstra(Request $request, Exam $exam)
    {
        $this->authorize('update', $exam);

        if (!in_array($exam->status, ['DRAFT', 'PAUSED'])) {
            abort(403, 'Peserta tidak dapat ditambahkan karena ujian sudah berjalan atau dipublikasikan. (Jeda ujian terlebih dahulu)');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        // Check if already exists
        $exists = ExamParticipant::where('exam_id', $exam->id)
            ->where('student_id', $validated['student_id'])
            ->where('is_susulan', false)
            ->exists();

        if ($exists) {
            return back()->with('error', 'Siswa tersebut sudah terdaftar sebagai peserta ekstra.');
        }

        ExamParticipant::create([
            'exam_id' => $exam->id,
            'student_id' => $validated['student_id'],
            'is_susulan' => false,
        ]);

        AuditLog::record('exam.ekstra_added', $exam, null, $validated, 'success', 'Siswa ekstra ditambahkan');

        return back()->with('success', 'Siswa ekstra berhasil ditambahkan.');
    }

    public function removeParticipant(Request $request, Exam $exam, ExamParticipant $participant)
    {
        \Illuminate\Support\Facades\Log::info("removeParticipant hit", ['exam_id' => $exam->id, 'participant_id' => $participant->id]);
        
        $this->authorize('update', $exam);

        if ($participant->exam_id !== $exam->id) {
            \Illuminate\Support\Facades\Log::warning("removeParticipant 404", ['participant_exam_id' => $participant->exam_id]);
            abort(404);
        }

        // Only allow removing if exam is DRAFT/PAUSED, or if it's a susulan (susulan can be managed anytime)
        if (!$participant->is_susulan && !in_array($exam->status, ['DRAFT', 'PAUSED'])) {
            \Illuminate\Support\Facades\Log::warning("removeParticipant 403", ['is_susulan' => $participant->is_susulan, 'status' => $exam->status]);
            abort(403, 'Peserta normal/ekstra hanya dapat dihapus saat ujian berstatus DRAFT atau PAUSED.');
        }

        $res = $participant->delete();
        \Illuminate\Support\Facades\Log::info("removeParticipant deleted", ['result' => $res]);

        AuditLog::record('exam.participant_removed', $exam, null, ['participant_id' => $participant->id], 'success', 'Peserta dihapus dari ujian');

        return back()->with('success', 'Peserta berhasil dihapus.');
    }
}
