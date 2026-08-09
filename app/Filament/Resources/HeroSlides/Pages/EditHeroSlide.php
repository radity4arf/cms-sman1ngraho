<?php

/**
 * EditHeroSlide — Halaman edit Hero Slide
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : EditHeroSlide page
// [THECHNOLOGY-FIX] : DeleteAction di-hidden untuk record is_default=true (model-level guard sebagai backend safety net)

namespace App\Filament\Resources\HeroSlides\Pages;

use App\Filament\Resources\HeroSlides\HeroSlideResource;
use App\Models\HeroSlide;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHeroSlide extends EditRecord
{
    protected static string $resource = HeroSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                // [THECHNOLOGY-FIX] : Sembunyikan tombol delete untuk slide default
                ->hidden(fn (HeroSlide $record): bool => $record->is_default),
        ];
    }
}
