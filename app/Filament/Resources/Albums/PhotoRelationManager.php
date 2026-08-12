<?php

/**
 * PhotoRelationManager — Relation Manager untuk foto dalam Album (RT-06)
 *
 * Dikelola via AlbumResource. CRUD foto per album.
 * Otorisasi: PhotoPolicy (view_any/create/update/delete_photos).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-10 — tambah CreateAction header + Edit/Delete record actions
 */

// [THECHNOLOGY-CRE] : PhotoRelationManager — kelola foto dalam album
// [THECHNOLOGY-MOD] : Tambah header CreateAction + record Edit/Delete actions

namespace App\Filament\Resources\Albums;

use App\Models\Photo;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\ToggleColumn;

class PhotoRelationManager extends RelationManager
{
    protected static string $relationship = 'photos';
    protected static ?string $recordTitleAttribute = 'caption';

    public static function getModelLabel(): string { return 'Foto'; }
    public static function getPluralModelLabel(): string { return 'Foto'; }

    /**
     * [THECHNOLOGY-MOD] : Header action — tombol Tambah Foto.
     * Otorisasi otomatis via PhotoPolicy::create() → create_photos.
     */
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Foto'),
        ];
    }

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

    /**
     * [THECHNOLOGY-MOD] : Record actions — Edit + Delete.
     * Otorisasi otomatis via PhotoPolicy (update → update_photos, delete → delete_photos).
     * ToggleColumn is_active juga ter-gate via Policy update().
     */
    public function table(Table $table): Table
    {
        return $table->columns([
            SpatieMediaLibraryImageColumn::make('image')->label('Gambar')->collection('image')->conversion('thumb'),
            TextColumn::make('caption')->label('Caption')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])->recordActions([
            \Filament\Actions\EditAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ])->defaultSort('sort_order', 'asc');
    }
}
