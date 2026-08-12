<?php

/**
 * AppServiceProvider
 *
 * Service provider utama — mendaftarkan Gate::before untuk super-admin
 * (flag is_super_admin) sehingga user super-admin otomatis lolos semua
 * pengecekan permission tanpa perlu assign satu per satu.
 *
 * [THECHNOLOGY-FIX] : Tambah model-level file size validation (10MB)
 * via Spatie Media::saving — berlaku untuk SEMUA model dengan media,
 * bukan cuma Filament form. Mencegah upload file besar via Tinker/API/CLI.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-07-28
 * @updated  2026-08-11 — model-level media size validation (CGX Fase 3)
 */

// [THECHNOLOGY-CRE-DSE] : AppServiceProvider — Gate::before untuk super-admin + integrasi Spatie permission
// [THECHNOLOGY-FIX] : Model-level media file size validation (10MB) — semua model, semua jalur

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

        // [THECHNOLOGY-FIX] : Model-level file size validation (10MB) — CGX Fase 3 Critical #2.
        // Validasi ukuran file di level Spatie Media model, BUKAN cuma Filament form.
        // Mencakup SEMUA model (Post, Achievement, AlumniTestimonial, Staff, Extracurricular,
        // Facility, HeroSlide, Photo, Download) dan SEMUA jalur upload (UI, Tinker, API, CLI, job).
        //
        // Batas 10MB = 10 * 1024 * 1024 bytes = 10485760 bytes.
        Media::saving(function (Media $media) {
            $maxSize = 10 * 1024 * 1024; // 10MB dalam bytes

            if ((int) $media->size > $maxSize) {
                throw new \RuntimeException(
                    'Ukuran file tidak boleh melebihi 10MB.'
                );
            }
        });
    }
}
