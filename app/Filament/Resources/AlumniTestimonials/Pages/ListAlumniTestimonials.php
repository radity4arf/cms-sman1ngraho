<?php

/**
 * ListAlumniTestimonials — Halaman daftar Alumni Menginspirasi
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : ListAlumniTestimonials page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\AlumniTestimonials\Pages;

use App\Filament\Resources\AlumniTestimonials\AlumniTestimonialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlumniTestimonials extends ListRecords
{
    protected static string $resource = AlumniTestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
