<?php

/**
 * PhotoRelationManager — Relation Manager untuk foto dalam Album (RT-06)
 *
 * Dikelola via AlbumResource. CRUD foto per album.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : PhotoRelationManager — kelola foto dalam album

namespace App\Filament\Resources\Albums;

use App\Models\Photo;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;

class PhotoRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';
    protected static ?string $recordTitleAttribute = 'caption';

    public static function getModelLabel(): string { return 'Foto'; }
    public static function getPluralModelLabel(): string { return 'Foto'; }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Foto')->schema([
                SpatieMediaLibraryFileUpload::make('image')
                    ->label('Gambar')->collection('image')
                    ->image()->imageEditor()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(10240)
                    ->required(),
                TextInput::make('caption')->label('Caption')->nullable()->maxLength(255),
                TextInput::make('alt_text')->label('Alt Text')->nullable()->maxLength(255),
                TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            ])->columns(2),
            Section::make('Status')->schema([
                Select::make('status')->label('Status')->options(['draft' => 'Draft', 'published' => 'Publish'])->default('draft')->required(),
                Toggle::make('is_active')->label('Aktif')->default(true),
            ])->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image')->label('Gambar')->getStateUsing(fn ($r) => $r->getFirstMediaUrl('image', 'thumb')),
            TextColumn::make('caption')->label('Caption')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])->defaultSort('sort_order', 'asc');
    }
}
