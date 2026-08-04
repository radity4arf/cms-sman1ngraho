<?php

// [THECHNOLOGY-CRE-DSE] : UsersTable — tabel daftar user dengan kolom permission

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
