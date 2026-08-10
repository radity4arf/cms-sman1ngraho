<?php

/**
 * CreateHeroSlide — Halaman buat Hero Slide baru
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah file header
 */

// [THECHNOLOGY-CRE] : CreateHeroSlide page
// [THECHNOLOGY-MOD] : Tambah file header & format ulang

namespace App\Filament\Resources\HeroSlides\Pages;

use App\Filament\Resources\HeroSlides\HeroSlideResource;
use Filament\Resources\Pages\CreateRecord;

class CreateHeroSlide extends CreateRecord
{
    protected static string $resource = HeroSlideResource::class;
}
