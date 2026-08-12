<?php

/**
 * HeroSlideSeeder — Seed 1 slide + 1 config row sebagai default (RT-15)
 *
 * Membuat 1 HeroSlide dan mencatatnya di HeroSlideConfig sebagai default.
 * HeroSlideConfig dijamin tepat 1 row via firstOrCreate.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-12 — Restrukturisasi: HeroSlideConfig sebagai single source of truth
 */

// [THECHNOLOGY-CRE] : HeroSlideSeeder — seed 1 default hero slide
// [THECHNOLOGY-MOD] : HeroSlideConfig — ganti is_default=true

namespace Database\Seeders;

use App\Models\HeroSlide;
use App\Models\HeroSlideConfig;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        // Idempoten — firstOrCreate slide
        $slide = HeroSlide::firstOrCreate(
            ['title' => 'SMAN 1 Ngraho'],
            [
                'caption'    => 'Selamat datang di portal resmi SMAN 1 Ngraho. Unggul dalam prestasi, berakhlak mulia, dan berwawasan global.',
                'sort_order' => 0,
                'status'     => 'published',
                'is_active'  => true,
            ]
        );

        // Idempoten — pastikan config row ada dan tunjuk slide ini
        $config = HeroSlideConfig::firstOrCreate([], [
            'default_hero_slide_id' => $slide->id,
        ]);

        // Kalau config row sudah ada tapi default_hero_slide_id null, isi
        if ($config->default_hero_slide_id === null) {
            $config->update(['default_hero_slide_id' => $slide->id]);
        }
    }
}
