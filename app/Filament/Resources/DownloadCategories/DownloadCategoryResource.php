<?php

/**
 * DownloadCategoryResource — Filament CRUD untuk Kategori Unduhan (RT-10)
 *
 * Tabel struktural — tanpa status/published_at.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : DownloadCategoryResource

namespace App\Filament\Resources\DownloadCategories;

use App\Filament\Resources\DownloadCategories\Pages;
use App\Models\DownloadCategory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class DownloadCategoryResource extends Resource
{
    protected static ?string $model = DownloadCategory::class;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string { return 'Unduhan'; }
    public static function getNavigationIcon(): \BackedEnum|string|null { return Heroicon::OutlinedFolder; }
    public static function getModelLabel(): string { return 'Kategori Unduhan'; }
    public static function getPluralModelLabel(): string { return 'Kategori Unduhan'; }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && Gate::allows('view_any_download_categories');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi')->schema([
                TextInput::make('name')->label('Nama')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, $state) => $set('slug', DownloadCategory::generateUniqueSlug($state))),
                TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
                Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('downloads_count')->label('File')->counts('downloads')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])->filters([TrashedFilter::make()])->recordActions([\Filament\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDownloadCategories::route('/'),
            'create' => Pages\CreateDownloadCategory::route('/create'),
            'edit'   => Pages\EditDownloadCategory::route('/{record}/edit'),
        ];
    }
}
