<?php

// [THECHNOLOGY-CRE-DSE] : DatabaseSeeder — orchestrator untuk semua seeder project

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // [THECHNOLOGY-CRE] : urutan penting —
        // 1. PermissionSeeder (harus ada sebelum AdminUserSeeder sync permissions)
        // 2. AdminUserSeeder (assign permission ke admin)
        // 3. DownloadCategorySeeder (kategori harus ada sebelum Download)
        // 4. HeroSlideSeeder (fallback default slide)
        $this->call([
            PermissionSeeder::class,
            AdminUserSeeder::class,
            DownloadCategorySeeder::class,
            HeroSlideSeeder::class,
        ]);
    }
}
