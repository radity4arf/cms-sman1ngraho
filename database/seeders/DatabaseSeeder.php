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
        // [THECHNOLOGY-CRE-DSE] : urutan penting — PermissionSeeder dulu, baru AdminUserSeeder
        $this->call([
            PermissionSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
