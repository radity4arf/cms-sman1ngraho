<?php

/**
 * AppServiceProvider
 *
 * Service provider utama — mendaftarkan Gate::before untuk super-admin
 * (flag is_super_admin) sehingga user super-admin otomatis lolos semua
 * pengecekan permission tanpa perlu assign satu per satu.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-07-28
 * @updated  2026-08-04
 */

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
        // [THECHNOLOGY-CRE-DSE] : Gate::before — jika user memiliki flag is_super_admin,
        // otomatis diizinkan mengakses semua fitur tanpa perlu assign permission satu per satu.
        // Flag eksplisit ini tidak bergantung pada jumlah/daftar permission yang bisa berubah di Fase 3+.
        // User biasa tetap melalui pengecekan permission normal (Gate/Policies).
        Gate::before(function ($user) {
            // [THECHNOLOGY-CRE-DSE] : cek flag boolean eksplisit, bukan daftar permission dinamis
            if ($user->is_super_admin) {
                return true;
            }

            return null; // lanjut ke pengecekan normal
        });
    }
}
