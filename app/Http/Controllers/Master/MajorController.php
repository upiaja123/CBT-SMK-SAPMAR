<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Major;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MajorController extends Controller
{
    public function index(): View
    {
        $this->authorize('majors.manage');
        $majors = Major::latest()->paginate(10);

        return view('master.majors.index', compact('majors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('majors.manage');
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:majors,code'],
            'name' => ['required', 'string', 'max:100'],
            'abbreviation' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $major = Major::create($validated);
        AuditLog::record('major.created', $major, null, $major->toArray());

        return redirect()->route('master.majors.index')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, Major $major): RedirectResponse
    {
        $this->authorize('majors.manage');
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:majors,code,'.$major->id],
            'name' => ['required', 'string', 'max:100'],
            'abbreviation' => ['nullable', 'string', 'max:20'],
            'is_active' => ['boolean'],
        ]);

        $old = $major->toArray();
        $major->update($validated);
        AuditLog::record('major.updated', $major, $old, $major->toArray());

        return redirect()->route('master.majors.index')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Major $major): RedirectResponse
    {
        $this->authorize('majors.manage');
        $old = $major->toArray();
        $major->delete();

        AuditLog::record('major.deleted', null, $old, null);

        return redirect()->route('master.majors.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}
