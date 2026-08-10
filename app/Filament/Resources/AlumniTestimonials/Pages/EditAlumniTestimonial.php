<?php

/**
 * EditAlumniTestimonial — Halaman edit Alumni
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : EditAlumniTestimonial page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\AlumniTestimonials\Pages;

use App\Filament\Resources\AlumniTestimonials\AlumniTestimonialResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlumniTestimonial extends EditRecord
{
    protected static string $resource = AlumniTestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
