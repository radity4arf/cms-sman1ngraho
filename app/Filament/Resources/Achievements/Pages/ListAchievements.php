<?php

/**
 * ListAchievements — Halaman daftar Prestasi
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : ListAchievements page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\Achievements\Pages;

use App\Filament\Resources\Achievements\AchievementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAchievements extends ListRecords
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
