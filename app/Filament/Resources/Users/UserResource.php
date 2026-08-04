<?php

// [THECHNOLOGY-CRE-DSE] : UserResource — CRUD user + assignment permission granular per fitur (bukan role)

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Gate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    // [THECHNOLOGY-CRE-DSE] : override method getNavigationGroup() untuk set grup navigasi "Sistem"
    public static function getNavigationGroup(): \UnitEnum|string|null
    {
        return 'Sistem';
    }

    // [THECHNOLOGY-CRE-DSE] : override method getNavigationIcon() untuk set icon Users
    public static function getNavigationIcon(): \BackedEnum|string|null
    {
        return \Filament\Support\Icons\Heroicon::OutlinedUsers;
    }

    // [THECHNOLOGY-CRE-DSE] : override getModelLabel() untuk label bahasa Indonesia
    public static function getModelLabel(): string
    {
        return 'Pengguna';
    }

    // [THECHNOLOGY-CRE-DSE] : override getPluralModelLabel() untuk label jamak
    public static function getPluralModelLabel(): string
    {
        return 'Pengguna';
    }

    /**
     * [THECHNOLOGY-CRE-DSE] : kontrol akses resource — hanya user dengan permission 'manage_users'
     * yang bisa mengakses resource ini. Super-admin otomatis diizinkan via Gate::before.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return Gate::allows('manage_users');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }
}
