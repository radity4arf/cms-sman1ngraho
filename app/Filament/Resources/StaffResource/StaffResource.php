<?php

/**
 * StaffResource — Filament CRUD untuk Guru & Tenaga Kependidikan (RT-07)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : StaffResource

namespace App\Filament\Resources\StaffResource;

use App\Filament\Resources\StaffResource\Pages;
use App\Enums\StaffCategory;
use App\Models\Staff;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string { return 'Profil'; }
    public static function getNavigationIcon(): string { return Heroicon::OutlinedUserGroup; }
    public static function getModelLabel(): string { return 'Staff'; }
    public static function getPluralModelLabel(): string { return 'Guru & Tenaga Kependidikan'; }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && Gate::allows('view_any_staff');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi')->schema([
                TextInput::make('name')->label('Nama')->required()->maxLength(255),
                Select::make('category')->label('Kategori')->options([
                    StaffCategory::Guru->value => StaffCategory::Guru->label(),
                    StaffCategory::TenagaKependidikan->value => StaffCategory::TenagaKependidikan->label(),
                ])->required(),
                TextInput::make('position')->label('Jabatan')->nullable()->maxLength(255),
                TextInput::make('subject')->label('Mata Pelajaran')->nullable()->maxLength(255),
                TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            ])->columns(2),
            Section::make('Media')->schema([
                SpatieMediaLibraryFileUpload::make('photo')->label('Foto')->collection('photo')
                    ->image()->imageEditor()->acceptedFileTypes(['image/jpeg','image/png','image/webp'])->maxSize(10240),
            ]),
            Section::make('Status')->schema([
                Select::make('status')->label('Status')->options(['draft' => 'Draft', 'published' => 'Publish'])->default('draft')->required(),
                Toggle::make('is_active')->label('Aktif')->default(true),
                DateTimePicker::make('published_at')->label('Tanggal Terbit')->nullable(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('photo')->label('Foto')->circular()->getStateUsing(fn ($r) => $r->getFirstMediaUrl('photo', 'thumb')),
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('category')->label('Kategori')->badge()->color(fn ($s) => $s === 'guru' ? 'primary' : 'info')->sortable(),
            TextColumn::make('position')->label('Jabatan')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])->filters([
            TrashedFilter::make(),
            SelectFilter::make('category')->options([
                'guru' => 'Guru', 'tenaga_kependidikan' => 'Tenaga Kependidikan',
            ]),
        ])->recordActions([\Filament\Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit'   => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}
