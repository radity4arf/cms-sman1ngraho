<?php

/**
 * EditHeroSlide — Halaman edit Hero Slide
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-12 — CLX fix: tambah PromoteAsDefaultAction via HeroSlideService
 */

// [THECHNOLOGY-CRE] : EditHeroSlide page
// [THECHNOLOGY-FIX] : DeleteAction di-hidden untuk record is_default=true
// [THECHNOLOGY-FIX] : beforeDelete() + beforeSave() tangkap aksi terlarang SEBELUM model — tampilkan Notification Filament, bukan error 500
// [THECHNOLOGY-MOD] : Promosikan via HeroSlideService::promoteAsDefault() — satu-satunya jalur UI resmi swap is_default

namespace App\Filament\Resources\HeroSlides\Pages;

use App\Enums\ContentStatus;
use App\Filament\Resources\HeroSlides\HeroSlideResource;
use App\Models\HeroSlide;
use App\Services\HeroSlideService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditHeroSlide extends EditRecord
{
    protected static string $resource = HeroSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // [THECHNOLOGY-MOD] : Aksi promosi default — panggil HeroSlideService, bukan mutasi langsung
            Action::make('promote_default')
                ->label('Jadikan Default')
                ->icon(Heroicon::OutlinedStar)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Promosikan sebagai Slide Default')
                ->modalDescription('Slide ini akan menjadi slide default menggantikan slide default yang ada saat ini. Lanjutkan?')
                ->modalSubmitActionLabel('Ya, jadikan default')
                ->visible(fn (HeroSlide $record): bool =>
                    ! $record->is_default
                    && $record->status === ContentStatus::Published
                    && $record->is_active
                )
                ->action(function (HeroSlide $record): void {
                    try {
                        HeroSlideService::promoteAsDefault($record);
                        Notification::make()
                            ->title('Slide berhasil dijadikan default')
                            ->success()
                            ->send();
                    } catch (\RuntimeException $e) {
                        Notification::make()
                            ->title('Gagal mempromosikan slide')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
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
