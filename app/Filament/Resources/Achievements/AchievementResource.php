<?php

/**
 * AchievementResource — Filament CRUD untuk Prestasi (RT-02)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : AchievementResource — CRUD Prestasi

namespace App\Filament\Resources\Achievements;

use App\Filament\Resources\Achievements\Pages;
use App\Models\Achievement;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): string { return 'Konten Beranda'; }
    public static function getNavigationIcon(): \BackedEnum|string|null { return Heroicon::OutlinedTrophy; }
    public static function getModelLabel(): string { return 'Prestasi'; }
    public static function getPluralModelLabel(): string { return 'Prestasi'; }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && Gate::allows('view_any_achievements');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Prestasi')->schema([
                TextInput::make('title')->label('Judul / Kejuaraan')->required()->maxLength(255),
                TextInput::make('name')->label('Nama Siswa / Tim')->nullable()->maxLength(255),
                TextInput::make('year')->label('Tahun')->numeric()->minValue(1900)->maxValue(2099)->nullable(),
                Textarea::make('description')->label('Deskripsi')->nullable()->rows(3),
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
            SpatieMediaLibraryImageColumn::make('photo')->label('Foto')->circular()->collection('photo')->conversion('thumb'),
            TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('year')->label('Tahun')->sortable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
        ])->filters([TrashedFilter::make()])->recordActions([\Filament\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAchievements::route('/'),
            'create' => Pages\CreateAchievement::route('/create'),
            'edit'   => Pages\EditAchievement::route('/{record}/edit'),
        ];
    }
}
