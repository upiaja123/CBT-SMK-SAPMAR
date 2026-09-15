<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\SubjectRequest;
use App\Models\AuditLog;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $this->authorize('subjects.manage');
        $subjects = Subject::latest()->paginate(10);

        return view('master.subjects.index', compact('subjects'));
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        $subject = Subject::create($request->validated());

        AuditLog::record('subject.created', $subject, null, $subject->toArray());

        return redirect()->route('master.subjects.index')
            ->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }

    public function update(SubjectRequest $request, Subject $subject): RedirectResponse
    {
        $old = $subject->toArray();
        $subject->update($request->validated());

        AuditLog::record('subject.updated', $subject, $old, $subject->toArray());

        return redirect()->route('master.subjects.index')
            ->with('success', 'Mata Pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $this->authorize('subjects.manage');
        $old = $subject->toArray();
        $subject->delete();

        AuditLog::record('subject.deleted', null, $old, null);

        return redirect()->route('master.subjects.index')
            ->with('success', 'Mata Pelajaran berhasil dihapus.');
    }
}
