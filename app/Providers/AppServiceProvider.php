<?php

// [THECHNOLOGY-CRE-DSE] : AppServiceProvider — Gate::before untuk super-admin + integrasi Spatie permission

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // [THECHNOLOGY-CRE-DSE] : Gate::before — jika user adalah super-admin (punya SEMUA permission),
        // otomatis diizinkan mengakses semua fitur tanpa perlu assign permission satu per satu.
        // User biasa tetap melalui pengecekan permission normal (Gate/Policies).
        Gate::before(function ($user) {
            // Super-admin diidentifikasi dengan memiliki SEMUA permission yang terdaftar
            $allPermissions = \Spatie\Permission\Models\Permission::pluck('name')->toArray();

            if (!empty($allPermissions) && $user->hasAllPermissions($allPermissions)) {
                return true;
            }

            return null; // lanjut ke pengecekan normal
        });
    }
}
