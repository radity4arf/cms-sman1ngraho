<?php

/**
 * HeroSlideSeeder — Seed 1 slide default (RT-15)
 *
 * Membuat 1 record is_default=true, status=published, is_active=true.
 * Ini adalah fallback hero slider — Beranda tidak boleh kosong total.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : HeroSlideSeeder — seed 1 default hero slide

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya buat jika belum ada slide default sama sekali
        if (HeroSlide::where('is_default', true)->exists()) {
            return;
        }

        HeroSlide::create([
            'title'      => 'SMAN 1 Ngraho',
            'caption'    => 'Selamat datang di portal resmi SMAN 1 Ngraho. Unggul dalam prestasi, berakhlak mulia, dan berwawasan global.',
            'is_default' => true,
            'sort_order' => 0,
            'status'     => 'published',
            'is_active'  => true,
        ]);
    }
}
