<?php

/**
 * ExtracurricularResource — Filament CRUD untuk Ekstrakurikuler (RT-08)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : ExtracurricularResource

namespace App\Filament\Resources\Extracurriculars;

use App\Filament\Resources\Extracurriculars\Pages;
use App\Enums\ExtracurricularCategory;
use App\Models\Extracurricular;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class ExtracurricularResource extends Resource
{
    protected static ?string $model = Extracurricular::class;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string { return 'Profil'; }
    public static function getNavigationIcon(): \BackedEnum|string|null { return Heroicon::OutlinedStar; }
    public static function getModelLabel(): string { return 'Ekstrakurikuler'; }
    public static function getPluralModelLabel(): string { return 'Ekstrakurikuler'; }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && Gate::allows('view_any_extracurriculars');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi')->schema([
                TextInput::make('name')->label('Nama')->required()->maxLength(255),
                Select::make('category')->label('Kategori')->options([
                    null => '—',
                    ExtracurricularCategory::Olahraga->value => ExtracurricularCategory::Olahraga->label(),
                    ExtracurricularCategory::Seni->value => ExtracurricularCategory::Seni->label(),
                    ExtracurricularCategory::Akademik->value => ExtracurricularCategory::Akademik->label(),
                    ExtracurricularCategory::Keagamaan->value => ExtracurricularCategory::Keagamaan->label(),
                ])->nullable(),
                TextInput::make('coach')->label('Pembina')->nullable()->maxLength(255),
                TextInput::make('schedule')->label('Jadwal')->nullable()->maxLength(100),
                Textarea::make('description')->label('Deskripsi')->nullable()->rows(3),
                TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            ])->columns(2)->columnSpanFull(),
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
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('category')->label('Kategori')->badge()->sortable(),
            TextColumn::make('coach')->label('Pembina')->searchable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])
            ->emptyStateHeading('Belum ada Ekstrakurikuler')
            ->emptyStateDescription('Klik tombol "Buat Ekstrakurikuler" untuk menambahkan data ekskul.')
            ->filters([
            TrashedFilter::make(),
            SelectFilter::make('category')->options([
                'olahraga' => 'Olahraga', 'seni' => 'Seni', 'akademik' => 'Akademik', 'keagamaan' => 'Keagamaan',
            ]),
        ])->recordActions([\Filament\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListExtracurriculars::route('/'),
            'create' => Pages\CreateExtracurricular::route('/create'),
            'edit'   => Pages\EditExtracurricular::route('/{record}/edit'),
        ];
    }
}
