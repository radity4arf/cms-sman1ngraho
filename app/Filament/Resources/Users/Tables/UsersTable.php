<?php

// [THECHNOLOGY-CRE-DSE] : UsersTable — tabel daftar user dengan kolom permission

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
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
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // [THECHNOLOGY-MOD-DSE] : guard self-lockout via bulk delete —
                    // cegah hapus diri sendiri, super-admin, atau satu-satunya pemegang manage_users
                    DeleteBulkAction::make()
                        ->before(function (Collection $records, DeleteBulkAction $action): void {
                            $currentUserId = auth()->id();
                            $issues = [];

                            foreach ($records as $record) {
                                if ($record->id === $currentUserId) {
                                    $issues[] = "Tidak bisa menghapus akun Anda sendiri.";
                                }
                                if ($record->is_super_admin) {
                                    $issues[] = "Tidak bisa menghapus akun super-admin.";
                                }
                            }

                            // Cek: apakah user yang akan dihapus termasuk pemegang manage_users terakhir?
                            if (empty($issues)) {
                                $deletingIds = $records->pluck('id')->toArray();
                                $deletingHasManageUsers = User::whereIn('id', $deletingIds)
                                    ->whereHas('permissions', fn ($q) => $q->where('name', 'manage_users'))
                                    ->exists();

                                if ($deletingHasManageUsers) {
                                    $otherWithAccess = User::whereNotIn('id', $deletingIds)
                                        ->where(function ($q) {
                                            $q->where('is_super_admin', true)
                                              ->orWhereHas('permissions', fn ($sq) => $sq->where('name', 'manage_users'));
                                        })
                                        ->count();

                                    if ($otherWithAccess === 0) {
                                        $issues[] = "Tidak bisa menghapus satu-satunya pengguna dengan akses manage_users. Sistem akan terkunci total.";
                                    }
                                }
                            }

                            if (!empty($issues)) {
                                Notification::make()
                                    ->title('Aksi Ditolak')
                                    ->body(implode(' ', $issues))
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
