<?php

/**
 * HeroSlideService — Layanan bisnis untuk manajemen HeroSlide
 *
 * Satu-satunya entry point resmi untuk mengganti slide default.
 * Operasi atomic native DB: hanya UPDATE 1 row di hero_slide_config —
 * tidak perlu lock, swap flag, token, trigger, atau mekanisme kompleks
 * warisan desain boolean is_default.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-11
 * @updated  2026-08-12 — Restrukturisasi: hapus swap mechanism, 1 UPDATE atomic native
 */

// [THECHNOLOGY-CRE] : HeroSlideService — promoteAsDefault()
// [THECHNOLOGY-MOD] : Restrukturisasi — 1 UPDATE atomic, tanpa lock/flag/token/trigger

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\HeroSlide;
use App\Models\HeroSlideConfig;

class HeroSlideService
{
    /**
     * Promosikan slide menjadi default.
     *
     * Operasi: UPDATE hero_slide_config SET default_hero_slide_id = $slide->id.
     * Hanya 1 row — atomicity native DB.
     *
     * @param  HeroSlide  $slide  Slide yang akan dipromosikan
     * @throws \RuntimeException  jika slide draft atau nonaktif
     */
    public static function promoteAsDefault(HeroSlide $slide): void
    {
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

        HeroSlideConfig::current()->update([
            'default_hero_slide_id' => $slide->id,
        ]);
    }
}
