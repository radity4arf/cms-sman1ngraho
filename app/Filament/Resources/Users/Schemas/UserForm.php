<?php

// [THECHNOLOGY-CRE-DSE] : UserForm schema — form untuk create/edit user dengan assignment permission granular

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->rule(Password::defaults())
                            ->required(fn(string $operation): bool => $operation === 'create')
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->revealable(),
                    ])
                    ->columns(2),

                // [THECHNOLOGY-CRE-DSE] : CheckboxList untuk assign permission granular per user (BUKAN role)
                Section::make('Akses Fitur (Permission Granular)')
                    ->description('Centang fitur yang boleh diakses user ini. Tidak menggunakan role — setiap user di-assign akses per fitur secara spesifik.')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('Fitur yang dapat diakses')
                            ->options(function () {
                                // Ambil semua permission dengan label untuk ditampilkan
                                return Permission::query()
                                    ->orderBy('name')
                                    ->pluck('label', 'name')
                                    ->toArray();
                            })
                            ->columns(2)
                            ->bulkToggleable()
                            ->searchable(),
                    ]),
            ]);
    }
}
