<?php

/**
 * CreateExtracurricular — Halaman buat Ekstrakurikuler baru
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : CreateExtracurricular page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\Extracurriculars\Pages;

use App\Filament\Resources\Extracurriculars\ExtracurricularResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExtracurricular extends CreateRecord
{
    protected static string $resource = ExtracurricularResource::class;
}
