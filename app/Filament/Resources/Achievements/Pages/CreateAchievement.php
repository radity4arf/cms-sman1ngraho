<?php

/**
 * CreateAchievement — Halaman buat Prestasi baru
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : CreateAchievement page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\Achievements\Pages;

use App\Filament\Resources\Achievements\AchievementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAchievement extends CreateRecord
{
    protected static string $resource = AchievementResource::class;
}
