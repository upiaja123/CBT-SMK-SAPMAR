<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\SchoolClassRequest;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Major;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        $this->authorize('classes.manage');
        $classes = SchoolClass::with(['major', 'academicYear'])->latest()->paginate(10);
        $majors = Major::where('is_active', true)->get();
        $academicYears = AcademicYear::where('is_active', true)->get();

        return view('master.classes.index', compact('classes', 'majors', 'academicYears'));
    }

    public function store(SchoolClassRequest $request): RedirectResponse
    {
        $schoolClass = SchoolClass::create($request->validated());

        AuditLog::record('school_class.created', $schoolClass, null, $schoolClass->toArray());

        return redirect()->route('master.classes.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(SchoolClassRequest $request, SchoolClass $class): RedirectResponse
    {
        $old = $class->toArray();
        $class->update($request->validated());

        AuditLog::record('school_class.updated', $class, $old, $class->toArray());

        return redirect()->route('master.classes.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        $this->authorize('classes.manage');
        $old = $class->toArray();
        $class->delete();

        AuditLog::record('school_class.deleted', null, $old, null);

        return redirect()->route('master.classes.index')
            ->with('success', 'Kelas berhasil dihapus.');
    }
}
