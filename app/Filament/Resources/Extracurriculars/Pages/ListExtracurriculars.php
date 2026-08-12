<?php

/**
 * ListExtracurriculars — Halaman daftar Ekstrakurikuler
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : ListExtracurriculars page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\Extracurriculars\Pages;

use App\Filament\Resources\Extracurriculars\ExtracurricularResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExtracurriculars extends ListRecords
{
    protected static string $resource = ExtracurricularResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
