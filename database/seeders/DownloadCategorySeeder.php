<?php

/**
 * DownloadCategorySeeder — Seed 4 kategori unduhan default (RT-10)
 *
 * Kategori: Formulir, Kalender, Brosur, Surat.
 * Idempoten — firstOrCreate berdasarkan slug.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : DownloadCategorySeeder — seed kategori unduhan default

namespace Database\Seeders;

use App\Models\DownloadCategory;
use Illuminate\Database\Seeder;

class DownloadCategorySeeder extends Seeder
{
    public static function getDefaultCategories(): array
    {
        return [
            ['name' => 'Formulir',  'slug' => 'formulir',  'sort_order' => 1],
            ['name' => 'Kalender',  'slug' => 'kalender',  'sort_order' => 2],
            ['name' => 'Brosur',    'slug' => 'brosur',    'sort_order' => 3],
            ['name' => 'Surat',     'slug' => 'surat',     'sort_order' => 4],
        ];
    }

    public function run(): void
    {
        foreach (static::getDefaultCategories() as $data) {
            DownloadCategory::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'name'       => $data['name'],
                    'sort_order' => $data['sort_order'],
                    'is_active'  => true,
                ]
            );
        }
    }
}
