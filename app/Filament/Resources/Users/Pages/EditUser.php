<?php

/**
 * EditUser Page
 *
 * Halaman edit pengguna — menangani sinkronisasi permission setelah update
 * dan pengaman self-lockout (tidak bisa mencabut manage_users dari diri sendiri,
 * tidak bisa menghapus super-admin atau diri sendiri via DeleteAction).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-07-28
 * @updated  2026-08-04
 */

// [THECHNOLOGY-CRE-DSE] : EditUser page — handle sync permissions setelah user diupdate + populate data awal
// + pengaman self-lockout: admin tidak bisa mencabut manage_users dari akun dirinya sendiri

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    // [THECHNOLOGY-MOD-DSE] : sembunyikan tombol Delete untuk diri sendiri & super-admin — cegah self-lockout
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn ($record) => $record->id === auth()->id() || $record->is_super_admin),
        ];
    }

    // [THECHNOLOGY-CRE-DSE] : populate CheckboxList 'permissions' dengan data existing user saat edit
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['permissions'] = $this->record->permissions()->pluck('name')->toArray();

        return $data;
    }

    // [THECHNOLOGY-CRE-DSE] : pengaman self-lockout — cegah admin mencabut manage_users dari dirinya sendiri
    // Super-admin (is_super_admin = true) exempt dari pengaman ini karena aksesnya tidak bergantung permission list
    protected function beforeSave(): void
    {
        $record = $this->record;
        $submittedPermissions = $this->data['permissions'] ?? [];

        // Hanya cek jika user yang diedit adalah user yang sedang login
        if ($record->id === auth()->id()) {
            // Super-admin exempt — mereka tidak bergantung pada permission list (diizinkan via Gate::before)
            if (!($record->is_super_admin ?? false)) {
                // Tolak jika manage_users TIDAK ada di daftar permission yang disubmit
                if (!in_array('manage_users', $submittedPermissions)) {
                    Notification::make()
                        ->title('Aksi Ditolak')
                        ->body('Anda tidak bisa mencabut akses manage_users dari akun Anda sendiri. Tindakan ini akan mengunci Anda keluar dari menu Pengguna.')
                        ->danger()
                        ->send();

                    $this->halt();
                }
            }
        }
    }

    // [THECHNOLOGY-CRE-DSE] : setelah record diupdate, sync ulang permissions (assign & cabut)
    protected function afterSave(): void
    {
        $permissions = $this->data['permissions'] ?? [];
        $this->record->syncPermissions($permissions);
    }
}
