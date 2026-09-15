<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('students.manage');
        
        $query = Student::with(['user', 'schoolClass.major']);

        if ($request->filled('school_class_id')) {
            $query->where('school_class_id', $request->school_class_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('username', 'like', "%{$search}%");
                })->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(15);
        $classes = SchoolClass::where('is_active', true)->get();

        return view('users.students.index', compact('students', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('students.manage');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:students,nisn'],
            'nis' => ['nullable', 'string', 'max:20', 'unique:students,nis'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($validated) {
            $email = $validated['email'] ?? ($validated['username'] . '@cbt-sapmar.sch.id');

            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $email,
                'password' => Hash::make($validated['password']),
                'status' => 'active',
            ]);
            $user->assignRole('siswa');

            $student = Student::create([
                'user_id' => $user->id,
                'school_class_id' => $validated['school_class_id'],
                'nisn' => $validated['nisn'] ?? null,
                'nis' => $validated['nis'] ?? null,
                'status' => 'active',
            ]);

            AuditLog::record('student.created', $student, null, [
                'user' => $user->toArray(),
                'student' => $student->toArray(),
            ]);
        });

        return redirect()->route('users.students.index')
            ->with('success', 'Data Siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorize('students.manage');
        $user = $student->user;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:students,nisn,' . $student->id],
            'nis' => ['nullable', 'string', 'max:20', 'unique:students,nis,' . $student->id],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        DB::transaction(function () use ($validated, $user, $student) {
            $oldStudent = $student->toArray();
            $oldUser = $user->toArray();

            $email = $validated['email'] ?? ($validated['username'] . '@cbt-sapmar.sch.id');

            $userUpdateData = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $email,
            ];

            if (!empty($validated['password'])) {
                $userUpdateData['password'] = Hash::make($validated['password']);
            }

            $user->update($userUpdateData);

            $student->update([
                'school_class_id' => $validated['school_class_id'],
                'nisn' => $validated['nisn'] ?? null,
                'nis' => $validated['nis'] ?? null,
            ]);

            AuditLog::record('student.updated', $student, [
                'user' => $oldUser,
                'student' => $oldStudent,
            ], [
                'user' => $user->toArray(),
                'student' => $student->toArray(),
            ]);
        });

        return redirect()->route('users.students.index')
            ->with('success', 'Data Siswa berhasil diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('students.manage');

        DB::transaction(function () use ($student) {
            $user = $student->user;
            $oldStudent = $student->toArray();
            $student->delete();
            if ($user) {
                $user->delete();
            }

            AuditLog::record('student.deleted', null, $oldStudent, null);
        });

        return redirect()->route('users.students.index')
            ->with('success', 'Data Siswa berhasil dihapus.');
    }
}
