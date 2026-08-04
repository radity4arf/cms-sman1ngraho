<?php

/**
 * User Model
 *
 * Model autentikasi utama — mengimplementasikan FilamentUser untuk akses panel
 * dan Spatie HasRoles untuk permission granular per user (bukan role tetap).
 * Super-admin dikenali lewat flag boolean is_super_admin (lihat Gate::before).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-07-28
 * @updated  2026-08-04
 */

// [THECHNOLOGY-CRE-DSE] : User model — implementasi FilamentUser + Spatie permission granular per fitur (bukan role tetap)

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // [THECHNOLOGY-CRE-DSE] : trait HasRoles dari spatie/laravel-permission — digunakan untuk permission granular per user, BUKAN role tetap
    use HasRoles;

    /**
     * Tentukan apakah user bisa mengakses Filament panel.
     * Hanya user yang punya minimal 1 permission ATAU adalah super-admin yang diizinkan masuk.
     * Ini mencegah user tanpa permission apapun bisa login ke dashboard kosong tanpa menu.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // [THECHNOLOGY-CRE-DSE] : izinkan masuk hanya jika user punya minimal 1 permission
        // atau memiliki flag is_super_admin — ini menjaga granularitas akses panel itu sendiri
        return $this->is_super_admin || $this->getAllPermissions()->isNotEmpty();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
        ];
    }
}
