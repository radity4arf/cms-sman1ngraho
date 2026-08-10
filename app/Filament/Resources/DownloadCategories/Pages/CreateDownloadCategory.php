<?php

/**
 * CreateDownloadCategory — Halaman buat Kategori Unduhan baru
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : CreateDownloadCategory page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\DownloadCategories\Pages;

use App\Filament\Resources\DownloadCategories\DownloadCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDownloadCategory extends CreateRecord
{
    protected static string $resource = DownloadCategoryResource::class;
}
