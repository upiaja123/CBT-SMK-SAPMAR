<?php

namespace App\Http\Controllers;

use App\Models\QuestionBank;
use App\Models\Subject;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class QuestionBankController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', QuestionBank::class);

        $query = QuestionBank::with(['subject', 'teacher.user'])->withCount('questions');

        // Guru only sees their own question banks
        if (auth()->user()->hasRole('guru')) {
            $query->where('teacher_id', auth()->user()->teacher?->id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $banks = $query->latest()->paginate(15);
        
        if (auth()->user()->hasRole('guru') && auth()->user()->teacher) {
            $subjects = auth()->user()->teacher->subjects()->where('is_active', true)->get();
        } else {
            $subjects = Subject::where('is_active', true)->get();
        }

        return view('question_banks.index', compact('banks', 'subjects'));
    }

    public function create(): View
    {
        $this->authorize('create', QuestionBank::class);
        
        if (auth()->user()->hasRole('guru') && auth()->user()->teacher) {
            $subjects = auth()->user()->teacher->subjects()->where('is_active', true)->get();
        } else {
            $subjects = Subject::where('is_active', true)->get();
        }
        
        $teachers = collect();
        if (!auth()->user()->teacher) {
            $teachers = \App\Models\Teacher::with('user')->get();
        }

        return view('question_banks.create', compact('subjects', 'teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', QuestionBank::class);

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade' => 'nullable|in:X,XI,XII',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Default to the logged-in teacher if they are a guru, else must be specified or default
        $teacherId = auth()->user()->teacher?->id;
        if (!$teacherId && $request->filled('teacher_id') && auth()->user()->hasPermissionTo('users.update')) {
            $teacherId = $request->teacher_id;
        }

        abort_if(!$teacherId, 403, 'Anda tidak terdaftar sebagai guru/pengajar.');

        $validated['teacher_id'] = $teacherId;

        $bank = QuestionBank::create($validated);

        AuditLog::record(
            'question_bank.create',
            $bank,
            null,
            $bank->toArray(),
            'success',
            'Bank soal berhasil dibuat'
        );

        return redirect()->route('question_banks.show', $bank)->with('success', 'Bank soal berhasil dibuat.');
    }

    public function show(QuestionBank $questionBank): View
    {
        $this->authorize('view', $questionBank);
        $questionBank->load(['subject', 'teacher.user', 'questions.currentVersion']);
        return view('question_banks.show', compact('questionBank'));
    }

    public function edit(QuestionBank $questionBank): View
    {
        $this->authorize('update', $questionBank);
        if (auth()->user()->hasRole('guru') && auth()->user()->teacher) {
            $subjects = auth()->user()->teacher->subjects()->where('is_active', true)->get();
        } else {
            $subjects = Subject::where('is_active', true)->get();
        }
        
        $teachers = collect();
        if (!auth()->user()->teacher) {
            $teachers = \App\Models\Teacher::with('user')->get();
        }

        return view('question_banks.edit', compact('questionBank', 'subjects', 'teachers'));
    }

    public function update(Request $request, QuestionBank $questionBank): RedirectResponse
    {
        $this->authorize('update', $questionBank);

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'grade' => 'nullable|in:X,XI,XII',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $oldData = $questionBank->toArray();
        $questionBank->update($validated);

        AuditLog::record(
            'question_bank.update',
            $questionBank,
            $oldData,
            $questionBank->fresh()->toArray(),
            'success',
            'Informasi bank soal diperbarui'
        );

        return redirect()->route('question_banks.show', $questionBank)->with('success', 'Bank soal berhasil diperbarui.');
    }

    public function destroy(QuestionBank $questionBank): RedirectResponse
    {
        $this->authorize('delete', $questionBank);
        
        $oldData = $questionBank->toArray();
        $questionBank->delete(); // Soft delete

        AuditLog::record(
            'question_bank.archive',
            $questionBank,
            $oldData,
            null,
            'success',
            'Bank soal diarsipkan'
        );

        return redirect()->route('question_banks.index')->with('success', 'Bank soal berhasil diarsipkan.');
    }
}
