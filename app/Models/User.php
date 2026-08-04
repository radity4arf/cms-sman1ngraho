<?php

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
     * Semua user yang bisa login ke admin panel harus punya akses.
     * Permission granular dicek via Gate/Policies di level fitur, bukan di sini.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // [THECHNOLOGY-CRE-DSE] : semua user yang sudah login bisa akses admin panel
        // Permission spesifik per fitur dicek via Gate::allows() di resource/widget masing-masing
        return true;
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
        ];
    }
}
