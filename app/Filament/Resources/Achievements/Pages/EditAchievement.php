<?php

/**
 * EditAchievement — Halaman edit Prestasi
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : EditAchievement page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\Achievements\Pages;

use App\Filament\Resources\Achievements\AchievementResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAchievement extends EditRecord
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
