<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $classes = \App\Models\SchoolClass::where('is_active', true)->get();
        return view('auth.login', ['forceRegister' => true, 'classes' => $classes]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:siswa,guru,proktor'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'nisn' => ['nullable', 'string', 'max:20', 'unique:students,nisn'],
            'nip' => ['nullable', 'string', 'max:30', 'unique:teachers,nip'],
        ]);

        $user = \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'active',
            ]);

            $user->assignRole($request->role);

            if ($request->role === 'siswa') {
                \App\Models\Student::create([
                    'user_id' => $user->id,
                    'school_class_id' => $request->school_class_id,
                    'nisn' => $request->nisn,
                ]);
            } elseif ($request->role === 'guru') {
                \App\Models\Teacher::create([
                    'user_id' => $user->id,
                    'nip' => $request->nip,
                ]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
