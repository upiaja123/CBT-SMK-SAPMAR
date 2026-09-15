<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Services\StudentImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentImportController extends Controller
{
    public function show(): View
    {
        $this->authorize('students.manage');
        $classes = SchoolClass::where('is_active', true)->get();

        return view('users.students.import', compact('classes'));
    }

    public function store(Request $request, StudentImportService $importService): RedirectResponse
    {
        $this->authorize('students.manage');

        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:2048'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
        ]);

        $file = $request->file('file');
        $rows = [];

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ',');
            if ($header) {
                // Normalize headers
                $header = array_map(fn($h) => strtolower(trim($h)), $header);
                
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (count($header) === count($data)) {
                        $rows[] = array_combine($header, $data);
                    }
                }
            }
            fclose($handle);
        }

        $result = $importService->import($rows, (int) $request->school_class_id);

        if (!empty($result['errors'])) {
            return redirect()->route('users.students.index')
                ->with('warning', "{$result['imported_count']} Siswa berhasil diimpor dengan beberapa error: " . implode(' | ', array_slice($result['errors'], 0, 5)));
        }

        return redirect()->route('users.students.index')
            ->with('success', "Berhasil mengimpor {$result['imported_count']} Siswa.");
    }
}
