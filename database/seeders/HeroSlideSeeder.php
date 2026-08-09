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
        // [THECHNOLOGY-FIX] : firstOrCreate agar idempoten — tidak duplikat jika sudah ada
        HeroSlide::firstOrCreate(
            ['is_default' => true],
            [
                'title'      => 'SMAN 1 Ngraho',
                'caption'    => 'Selamat datang di portal resmi SMAN 1 Ngraho. Unggul dalam prestasi, berakhlak mulia, dan berwawasan global.',
                'sort_order' => 0,
                'status'     => 'published',
                'is_active'  => true,
            ]
        );
    }
}
