<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'avatar',
        'password',
        'status',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isKurikulum(): bool
    {
        return $this->hasRole('kurikulum');
    }

    public function isGuru(): bool
    {
        return $this->hasRole('guru');
    }

    public function isProktor(): bool
    {
        return $this->hasRole('proktor');
    }

    public function isSiswa(): bool
    {
        return $this->hasRole('siswa');
    }

    /**
     * Get primary role label for display.
     */
    public function getPrimaryRoleLabelAttribute(): string
    {
        return match ($this->roles->first()?->name) {
            'super_admin' => 'Super Admin',
            'kurikulum' => 'Kurikulum',
            'guru' => 'Guru',
            'proktor' => 'Proktor',
            'siswa' => 'Siswa',
            default => 'Pengguna',
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=ffffff&background=1e3a5f';
    }

    public function questions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    public function questionVersions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(QuestionVersion::class, 'created_by');
    }

    public function exams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Exam::class, 'created_by');
    }
}
