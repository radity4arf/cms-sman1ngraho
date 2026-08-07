<?php

/**
 * DownloadResource — Filament CRUD untuk Unduhan (RT-10)
 *
 * File via spatie/laravel-medialibrary (koleksi: file).
 * Validasi: pdf,doc,docx,xls,xlsx,jpg,png,webp max 10MB.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : DownloadResource

namespace App\Filament\Resources\Downloads;

use App\Filament\Resources\Downloads\Pages;
use App\Models\Download;
use App\Models\DownloadCategory;
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
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class DownloadResource extends Resource
{
    protected static ?string $model = Download::class;
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): string { return 'Unduhan'; }
    public static function getNavigationIcon(): string { return Heroicon::OutlinedArrowDownTray; }
    public static function getModelLabel(): string { return 'Unduhan'; }
    public static function getPluralModelLabel(): string { return 'Unduhan'; }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && Gate::allows('view_any_downloads');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi')->schema([
                TextInput::make('title')->label('Judul')->required()->maxLength(255),
                Select::make('download_category_id')->label('Kategori')->relationship('category', 'name')->required(),
            ])->columns(2),
            Section::make('File')->schema([
                SpatieMediaLibraryFileUpload::make('file')->label('File Unduhan')->collection('file')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'image/jpeg', 'image/png', 'image/webp',
                    ])
                    ->maxSize(10240)
                    ->required(),
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
            TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            TextColumn::make('category.name')->label('Kategori')->sortable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
            TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->sortable(),
        ])->filters([
            TrashedFilter::make(),
            SelectFilter::make('download_category_id')->label('Kategori')->relationship('category', 'name'),
        ])->recordActions([\Filament\Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDownloads::route('/'),
            'create' => Pages\CreateDownload::route('/create'),
            'edit'   => Pages\EditDownload::route('/{record}/edit'),
        ];
    }
}
