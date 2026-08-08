<?php

/**
 * FacilityResource — Filament CRUD untuk Fasilitas (RT-09)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : FacilityResource

namespace App\Filament\Resources\Facilities;

use App\Filament\Resources\Facilities\Pages;
use App\Models\Facility;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
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

class FacilityResource extends Resource
{
    protected static ?string $model = Facility::class;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string { return 'Profil'; }
    public static function getNavigationIcon(): \BackedEnum|string|null { return Heroicon::OutlinedBuildingOffice2; }
    public static function getModelLabel(): string { return 'Fasilitas'; }
    public static function getPluralModelLabel(): string { return 'Fasilitas'; }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && Gate::allows('view_any_facilities');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi')->schema([
                TextInput::make('name')->label('Nama')->required()->maxLength(255),
                Textarea::make('description')->label('Deskripsi')->nullable()->maxLength(500)->rows(3),
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
            SpatieMediaLibraryImageColumn::make('photo')->label('Foto')->collection('photo')->conversion('thumb'),
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])->filters([TrashedFilter::make()])->recordActions([\Filament\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListFacilities::route('/'),
            'create' => Pages\CreateFacility::route('/create'),
            'edit'   => Pages\EditFacility::route('/{record}/edit'),
        ];
    }
}
