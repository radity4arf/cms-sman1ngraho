<?php

/**
 * EditDownload — Halaman edit Unduhan
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : EditDownload page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\Downloads\Pages;

use App\Filament\Resources\Downloads\DownloadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDownload extends EditRecord
{
    protected static string $resource = DownloadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
