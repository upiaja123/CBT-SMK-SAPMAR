<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\AcademicYearRequest;
use App\Models\AcademicYear;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $this->authorize('academic_years.manage');
        $academicYears = AcademicYear::latest()->paginate(10);

        return view('master.academic_years.index', compact('academicYears'));
    }

    public function store(AcademicYearRequest $request): RedirectResponse
    {
        $academicYear = AcademicYear::create($request->validated());

        AuditLog::record('academic_year.created', $academicYear, null, $academicYear->toArray());

        return redirect()->route('master.academic-years.index')
            ->with('success', 'Tahun Ajaran berhasil ditambahkan.');
    }

    public function update(AcademicYearRequest $request, AcademicYear $academicYear): RedirectResponse
    {
        $old = $academicYear->toArray();
        $academicYear->update($request->validated());

        AuditLog::record('academic_year.updated', $academicYear, $old, $academicYear->toArray());

        return redirect()->route('master.academic-years.index')
            ->with('success', 'Tahun Ajaran berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        $this->authorize('academic_years.manage');
        $old = $academicYear->toArray();
        $academicYear->delete();

        AuditLog::record('academic_year.deleted', null, $old, null);

        return redirect()->route('master.academic-years.index')
            ->with('success', 'Tahun Ajaran berhasil dihapus.');
    }
}
