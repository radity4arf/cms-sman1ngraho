<?php

/**
 * CreateAlumniTestimonial — Halaman buat Alumni baru
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : CreateAlumniTestimonial page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\AlumniTestimonials\Pages;

use App\Filament\Resources\AlumniTestimonials\AlumniTestimonialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAlumniTestimonial extends CreateRecord
{
    protected static string $resource = AlumniTestimonialResource::class;
}
