<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $this->authorize('users.manage');
        
        $query = User::whereDoesntHave('roles', function ($q) {
            $q->whereIn('name', ['guru', 'siswa']);
        });

        // Jika bukan super_admin, tidak boleh melihat akun super_admin
        if (!auth()->user()->hasRole('super_admin')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'super_admin');
            });
        }

        $staff = $query->with('roles')->latest()->paginate(10);

        return view('users.staff.index', compact('staff'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('users.manage');
        
        // Kurikulum tidak boleh membuat super_admin
        if ($request->role === 'super_admin' && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Akses ditolak. Anda tidak berhak membuat Super Admin.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:super_admin,kurikulum,proktor'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => 'active',
            ]);
            $user->assignRole($validated['role']);

            AuditLog::record('staff.created', null, null, $user->toArray());
        });

        return redirect()->route('users.staff.index')
            ->with('success', 'Data Staf berhasil ditambahkan.');
    }

    public function update(Request $request, User $staff): RedirectResponse
    {
        $this->authorize('users.manage');
        
        // Prevent editing guru/siswa from here just in case
        if ($staff->hasRole(['guru', 'siswa'])) {
            abort(403, 'Akses ditolak.');
        }

        // Kurikulum tidak boleh mengedit akun super_admin
        if ($staff->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Akses ditolak. Anda tidak berhak mengedit Super Admin.');
        }

        // Kurikulum tidak boleh mengubah role seseorang menjadi super_admin
        if ($request->role === 'super_admin' && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Akses ditolak. Anda tidak berhak menjadikan akun sebagai Super Admin.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $staff->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $staff->id],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:super_admin,kurikulum,proktor'],
        ]);

        DB::transaction(function () use ($validated, $staff) {
            $oldUser = $staff->toArray();

            $updateData = [
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $staff->update($updateData);
            
            // Sync roles (removes old, adds new)
            $staff->syncRoles([$validated['role']]);

            AuditLog::record('staff.updated', null, $oldUser, $staff->toArray());
        });

        return redirect()->route('users.staff.index')
            ->with('success', 'Data Staf berhasil diperbarui.');
    }

    public function destroy(User $staff): RedirectResponse
    {
        $this->authorize('users.manage');

        if ($staff->hasRole(['guru', 'siswa'])) {
            abort(403, 'Akses ditolak.');
        }
        
        // Kurikulum tidak boleh menghapus akun super_admin
        if ($staff->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            abort(403, 'Akses ditolak. Anda tidak berhak menghapus Super Admin.');
        }
        
        // Prevent deleting yourself
        if ($staff->id === auth()->id()) {
            return redirect()->route('users.staff.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        DB::transaction(function () use ($staff) {
            $oldUser = $staff->toArray();
            $staff->delete();

            AuditLog::record('staff.deleted', null, $oldUser, null);
        });

        return redirect()->route('users.staff.index')
            ->with('success', 'Data Staf berhasil dihapus.');
    }
}
