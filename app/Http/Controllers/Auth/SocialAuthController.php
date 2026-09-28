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
            // Simpan google_id jika user mendaftar via email sebelumnya
            if (!$user->google_id) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            // Cek status akun
            if (!$user->isActive()) {
                return redirect()->route('login')
                    ->with('error', 'Akun Anda belum aktif. Hubungi administrator.');
            }

            Auth::login($user, true);
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => request()->ip(),
            ]);

            return redirect()->intended(route('dashboard'));
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

        return redirect()->route('login')->with('status',
            "Akun Google Anda ({$googleEmail}) berhasil terdaftar! Silakan tunggu aktivasi dari administrator."
        );
    }
}
