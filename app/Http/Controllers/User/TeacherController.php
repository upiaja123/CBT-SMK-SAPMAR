<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        $this->authorize('teachers.manage');
        $teachers = Teacher::with(['user', 'subjects', 'classes'])->latest()->paginate(10);
        $subjects = \App\Models\Subject::where('is_active', true)->orderBy('name')->get();
        $classes = \App\Models\SchoolClass::orderBy('grade')->orderBy('name')->get();

        return view('users.teachers.index', compact('teachers', 'subjects', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('teachers.manage');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'nip' => ['nullable', 'string', 'max:30', 'unique:teachers,nip'],
            'password' => ['required', 'string', 'min:8'],
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['exists:subjects,id'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:school_classes,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => 'active',
            ]);
            $user->assignRole('guru');

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'nip' => $validated['nip'] ?? null,
                'status' => 'active',
            ]);

            if (isset($validated['subjects'])) {
                $teacher->subjects()->sync($validated['subjects']);
            }
            if (isset($validated['classes'])) {
                $teacher->classes()->sync($validated['classes']);
            }

            AuditLog::record('teacher.created', $teacher, null, [
                'user' => $user->toArray(),
                'teacher' => $teacher->toArray(),
            ]);
        });

        return redirect()->route('users.teachers.index')
            ->with('success', 'Data Guru berhasil ditambahkan.');
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $this->authorize('teachers.manage');
        $user = $teacher->user;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'nip' => ['nullable', 'string', 'max:30', 'unique:teachers,nip,' . $teacher->id],
            'password' => ['nullable', 'string', 'min:8'],
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['exists:subjects,id'],
            'classes' => ['nullable', 'array'],
            'classes.*' => ['exists:school_classes,id'],
        ]);

        DB::transaction(function () use ($validated, $user, $teacher) {
            $oldTeacher = $teacher->toArray();
            $oldUser = $user->toArray();

            $userUpdateData = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $userUpdateData['password'] = Hash::make($validated['password']);
            }

            $user->update($userUpdateData);

            $teacher->update([
                'nip' => $validated['nip'] ?? null,
            ]);

            $teacher->subjects()->sync($validated['subjects'] ?? []);
            $teacher->classes()->sync($validated['classes'] ?? []);

            AuditLog::record('teacher.updated', $teacher, [
                'user' => $oldUser,
                'teacher' => $oldTeacher,
            ], [
                'user' => $user->toArray(),
                'teacher' => $teacher->toArray(),
            ]);
        });

        return redirect()->route('users.teachers.index')
            ->with('success', 'Data Guru berhasil diperbarui.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        $this->authorize('teachers.manage');

        DB::transaction(function () use ($teacher) {
            $user = $teacher->user;
            $oldTeacher = $teacher->toArray();
            $teacher->delete();
            if ($user) {
                $user->delete();
            }

            AuditLog::record('teacher.deleted', null, $oldTeacher, null);
        });

        return redirect()->route('users.teachers.index')
            ->with('success', 'Data Guru berhasil dihapus.');
    }
}
