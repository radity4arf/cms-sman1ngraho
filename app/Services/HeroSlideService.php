<?php

/**
 * HeroSlideService — Layanan bisnis untuk manajemen HeroSlide
 *
 * Satu-satunya entry point resmi untuk operasi swap is_default.
 * Semua perubahan is_default HARUS melalui service ini; perubahan langsung
 * via property assignment (model->is_default = false; model->save()) DITOLAK
 * di level model guard, kecuali dalam konteks swap internal.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-11
 * @updated  2026-08-12 — CLX fix: token-guard beginSwap/endSwap + try/finally
 */

// [THECHNOLOGY-CRE] : HeroSlideService — promoteAsDefault(), satu-satunya jalur resmi swap default
// [THECHNOLOGY-MOD] : Token-guard beginSwap/endSwap + try/finally — flag selalu di-reset

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\HeroSlide;
use Illuminate\Support\Facades\DB;

class HeroSlideService
{
    /**
     * Promosikan slide menjadi default.
     *
     * Operasi atomik dalam satu DB transaction:
     * 1. Validasi slide: harus published + aktif (draft/inactive → exception)
     * 2. Lock semua row is_default=true (serialisasi concurrent)
     * 3. Unset semua default existing
     * 4. Set slide baru sebagai default
     *
     * @param  HeroSlide  $slide  Slide yang akan dipromosikan sebagai default baru
     * @throws \RuntimeException  Jika slide status=draft atau is_active=false
     */
    public static function promoteAsDefault(HeroSlide $slide): void
    {
        // [THECHNOLOGY-FIX] : Guard — slide yang akan jadi default wajib published + aktif
        if ($slide->status !== ContentStatus::Published) {
            throw new \RuntimeException(
                'Slide dengan status draft tidak dapat dijadikan default. Publish terlebih dahulu.'
            );
        }

        if (! $slide->is_active) {
            throw new \RuntimeException(
                'Slide yang tidak aktif tidak dapat dijadikan default. Aktifkan terlebih dahulu.'
            );
        }

        DB::transaction(function () use ($slide) {
            // Lock semua baris dengan is_default=true — serialisasi akses concurrent
            $existingDefaults = HeroSlide::where('is_default', true)
                ->when($slide->exists, fn ($q) => $q->where('id', '!=', $slide->id))
                ->lockForUpdate()
                ->get();

            // [THECHNOLOGY-MOD] : Set internal flag via token-guarded beginSwap().
            // try/finally memastikan flag selalu di-reset meskipun exception terjadi.
            HeroSlide::beginSwap(HeroSlide::SWAP_TOKEN);
            try {
                // Unset semua default existing
                foreach ($existingDefaults as $default) {
                    $default->is_default = false;
                    $default->save();
                }

                // Set slide baru sebagai default
                if (! $slide->exists) {
                    $slide->is_default = true;
                    $slide->save();
                } elseif (! $slide->is_default) {
                    $slide->is_default = true;
                    $slide->save();
                }
                // Kalau slide->is_default sudah true dan sudah exists → sudah default, tidak perlu apa-apa
            } finally {
                HeroSlide::endSwap(HeroSlide::SWAP_TOKEN);
            }
        });
    }
}
