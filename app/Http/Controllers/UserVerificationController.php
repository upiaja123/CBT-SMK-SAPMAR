<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserVerificationController extends Controller
{
    public function index()
    {
        // Only Super Admin or users with manage permissions should access this
        $this->authorize('users.manage');

        // Get all inactive users who registered via Google
        $pendingUsers = User::where('status', 'inactive')
            ->whereNotNull('google_id')
            ->latest()
            ->paginate(15);

        // Filter roles based on current user's permission
        if (auth()->user()->hasRole('super_admin')) {
            $roles = Role::all();
        } else {
            // Kurikulum/Staff only can verify as siswa, guru, proktor, etc (excluding super_admin and kurikulum)
            $roles = Role::whereNotIn('name', ['super_admin', 'kurikulum'])->get();
        }

        $classes = SchoolClass::where('is_active', true)->get();

        return view('users.verifications.index', compact('pendingUsers', 'roles', 'classes'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('users.manage');

        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
            'school_class_id' => 'required_if:role,siswa',
            'nis' => 'nullable|string|max:20',
            'nip' => 'nullable|string|max:30',
        ]);

        // Security Check: Only super_admin can assign super_admin or kurikulum roles
        if (in_array($validated['role'], ['super_admin', 'kurikulum']) && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Anda tidak memiliki hak akses untuk memberikan Role level tinggi ini.');
        }

        DB::transaction(function () use ($user, $validated) {
            $role = $validated['role'];

            // Remove existing student record if role is NOT siswa
            if ($role !== 'siswa') {
                $user->student()->delete();
            }

            // Remove existing teacher record if role is NOT guru
            if ($role !== 'guru') {
                $user->teacher()->delete();
            }

            if ($role === 'siswa') {
                Student::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'school_class_id' => $validated['school_class_id'],
                        'nis' => $validated['nis'],
                        'status' => 'active',
                    ]
                );
            } elseif ($role === 'guru') {
                Teacher::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nip' => $validated['nip'],
                        'status' => 'active',
                    ]
                );
            }

            // Sync role
            $user->syncRoles([$role]);

            // Activate user
            $user->update(['status' => 'active']);
        });

        return redirect()->route('users.verifications.index')
            ->with('success', "Akun {$user->name} berhasil diverifikasi dan diaktifkan sebagai " . ucfirst($validated['role']) . ".");
    }

    public function destroy(User $user)
    {
        $this->authorize('users.manage');

        DB::transaction(function () use ($user) {
            $user->student()->delete();
            $user->teacher()->delete();
            $user->delete();
        });

        return redirect()->route('users.verifications.index')
            ->with('success', 'Pendaftaran akun berhasil ditolak dan dihapus.');
    }
}
