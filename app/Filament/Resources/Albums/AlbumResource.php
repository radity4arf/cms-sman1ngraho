<?php

/**
 * AlbumResource — Filament CRUD untuk Album Galeri (RT-06)
 *
 * Foto dikelola via PhotoRelationManager.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : AlbumResource

namespace App\Filament\Resources\Albums;

use App\Filament\Resources\Albums\Pages;
use App\Models\Album;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class AlbumResource extends Resource
{
    protected static ?string $model = Album::class;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string { return 'Galeri'; }
    public static function getNavigationIcon(): \BackedEnum|string|null { return Heroicon::OutlinedCamera; }
    public static function getModelLabel(): string { return 'Album'; }
    public static function getPluralModelLabel(): string { return 'Album'; }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && Gate::allows('view_any_albums');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Album')->schema([
                TextInput::make('name')->label('Nama Album')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, $state) => $set('slug', Album::generateUniqueSlug($state))),
                TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                Textarea::make('description')->label('Deskripsi')->nullable()->rows(3),
                TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            ])->columns(2),
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
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('photos_count')->label('Foto')->counts('photos')->sortable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])->filters([TrashedFilter::make()])->recordActions([\Filament\Actions\EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            PhotoRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAlbums::route('/'),
            'create' => Pages\CreateAlbum::route('/create'),
            'edit'   => Pages\EditAlbum::route('/{record}/edit'),
        ];
    }
}
