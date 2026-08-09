<?php

/**
 * EditHeroSlide — Halaman edit Hero Slide
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : EditHeroSlide page
// [THECHNOLOGY-FIX] : DeleteAction di-hidden untuk record is_default=true
// [THECHNOLOGY-FIX] : beforeDelete() + beforeSave() tangkap aksi terlarang SEBELUM model — tampilkan Notification Filament, bukan error 500

namespace App\Filament\Resources\HeroSlides\Pages;

use App\Filament\Resources\HeroSlides\HeroSlideResource;
use App\Models\HeroSlide;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
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

    // [THECHNOLOGY-FIX] : Tangkap delete sebelum model — tampilkan notifikasi rapi, bukan error 500 mentah
    protected function beforeDelete(): void
    {
        if ($this->getRecord()->is_default) {
            Notification::make()
                ->title('Aksi Ditolak')
                ->body('Slide default tidak dapat dihapus.')
                ->danger()
                ->send();

            $this->halt();
        }
    }

    // [THECHNOLOGY-FIX] : Tangkap perubahan status/is_active sebelum model — notifikasi rapi untuk Edge Case #4
    protected function beforeSave(): void
    {
        $record = $this->getRecord();

        if (! $record->is_default) {
            return;
        }

        $data = $this->form->getState();

        // Cegah draft
        if (($data['status'] ?? null) === 'draft') {
            Notification::make()
                ->title('Aksi Ditolak')
                ->body('Slide default tidak dapat diubah menjadi draft.')
                ->danger()
                ->send();

            $this->halt();
        }

        // Cegah nonaktifkan
        if (($data['is_active'] ?? null) === false) {
            Notification::make()
                ->title('Aksi Ditolak')
                ->body('Slide default tidak dapat dinonaktifkan.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
