<?php

/**
 * CreateDownload — Halaman buat Unduhan baru
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : CreateDownload page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\Downloads\Pages;

use App\Filament\Resources\Downloads\DownloadResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDownload extends CreateRecord
{
    protected static string $resource = DownloadResource::class;
}
