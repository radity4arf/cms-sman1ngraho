<?php

/**
 * UsersTable
 *
 * Konfigurasi tabel daftar pengguna — menampilkan kolom Nama, Email,
 * Akses Fitur (permission label), dan Tanggal Dibuat. Menyertakan
 * guard bulk-delete untuk mencegah self-lockout (3 skenario proteksi).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-07-28
 * @updated  2026-08-04
 */

// [THECHNOLOGY-CRE-DSE] : UsersTable — tabel daftar user dengan kolom permission

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use App\Services\UserDeletionGuard;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                // [THECHNOLOGY-CRE-DSE] : tampilkan daftar permission yang dimiliki user
                TextColumn::make('permissions_label')
                    ->label('Akses Fitur')
                    ->getStateUsing(function ($record) {
                        $perms = $record->permissions()->pluck('label')->filter()->toArray();
                        return !empty($perms) ? implode(', ', $perms) : '—';
                    })
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->emptyStateHeading('Belum ada Pengguna')
            ->emptyStateDescription('Klik tombol "Buat Pengguna" untuk menambahkan akun baru.')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // [THECHNOLOGY-MOD-DSE] : guard self-lockout via bulk delete —
                    // cegah hapus diri sendiri, super-admin, atau satu-satunya pemegang manage_users.
                    // Logic proteksi didelegasikan ke UserDeletionGuard (shared service) agar simetris
                    // dengan single-delete di EditUser.php.
                    DeleteBulkAction::make()
                        ->before(function (Collection $records, DeleteBulkAction $action): void {
                            $issues = [];

                            // Per-record check: self-delete & super-admin (via shared guard)
                            foreach ($records as $record) {
                                if (UserDeletionGuard::isSelfOrSuperAdmin($record)) {
                                    if ($record->id === auth()->id()) {
                                        $issues[] = "Tidak bisa menghapus akun Anda sendiri.";
                                    }
                                    if ($record->is_super_admin) {
                                        $issues[] = "Tidak bisa menghapus akun super-admin.";
                                    }
                                }
                            }

                            // Batch-level check: last manage_users holder (via shared guard)
                            if (empty($issues)) {
                                if (UserDeletionGuard::wouldRemoveLastManageUsersHolder($records)) {
                                    $issues[] = "Tidak bisa menghapus satu-satunya pengguna dengan akses manage_users. Sistem akan terkunci total.";
                                }
                            }

                            if (!empty($issues)) {
                                // [THECHNOLOGY-MOD-DSE] : array_unique mencegah duplikasi pesan
                                // identik dari beberapa record dalam batch yang sama
                                $uniqueIssues = array_unique($issues);

                                Notification::make()
                                    ->title('Aksi Ditolak')
                                    ->body(implode(' ', $uniqueIssues))
                                    ->danger()
                                    ->send();

                                // [THECHNOLOGY-MOD-DSE] : halt() mencegah eksekusi delete pada bulk action
                                $action->halt();
                            }
                        }),
                ]),
            ]);
    }
}
