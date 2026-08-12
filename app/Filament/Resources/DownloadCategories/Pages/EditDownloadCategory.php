<?php

/**
 * EditDownloadCategory — Halaman edit Kategori Unduhan
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : EditDownloadCategory page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\DownloadCategories\Pages;

use App\Filament\Resources\DownloadCategories\DownloadCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDownloadCategory extends EditRecord
{
    protected static string $resource = DownloadCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
