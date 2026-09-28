<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Arahkan user ke halaman login Google.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Tangani callback dari Google setelah user login.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Login dengan Google gagal. Silakan coba lagi.');
        }

        // Cari user berdasarkan google_id atau email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Cek status akun
            if (!$user->isActive()) {
                return redirect()->route('login')
                    ->with('error', 'Akun Anda belum aktif. Hubungi administrator.');
            }

            // Cek Maintenance Mode
            if (app()->isDownForMaintenance() && !$user->hasRole('super_admin')) {
                return redirect()->route('login')
                    ->with('error', 'Sistem sedang dalam mode perbaikan (Maintenance). Hanya administrator yang dapat masuk saat ini.');
            }

            // Simpan google_id jika user mendaftar via email sebelumnya
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            Auth::login($user, true);
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            return redirect()->intended(route('dashboard'));
        }

        // Cek Maintenance Mode untuk pendaftar baru
        if (app()->isDownForMaintenance()) {
            return redirect()->route('login')
                ->with('error', 'Sistem sedang dalam mode perbaikan. Pendaftaran akun baru ditutup sementara.');
        }

        // Buat akun baru dari data Google (default: siswa, status: inactive)
        $googleEmail = $googleUser->getEmail();
        $googleName  = $googleUser->getName();

        $newUser = User::create([
            'name'      => $googleName,
            'email'     => $googleEmail,
            'username'  => Str::slug($googleName) . '_' . Str::random(5),
            'google_id' => $googleUser->getId(),
            'avatar'    => $googleUser->getAvatar(),
            'password'  => \Illuminate\Support\Facades\Hash::make(Str::random(24)),
            'status'    => 'inactive',
        ]);
        $newUser->assignRole('siswa');

        \App\Models\Student::create([
            'user_id' => $newUser->id,
            'school_class_id' => null,
            'status' => 'inactive',
        ]);

        return redirect()->route('login')->with('status',
            "Akun Google Anda ({$googleEmail}) berhasil terdaftar! Silakan tunggu aktivasi dari administrator."
        );
    }
}
