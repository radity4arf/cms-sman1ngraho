<?php

/**
 * AlumniTestimonialResource — Filament CRUD untuk Alumni Menginspirasi (RT-03)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : AlumniTestimonialResource

namespace App\Filament\Resources\AlumniTestimonials;

use App\Filament\Resources\AlumniTestimonials\Pages;
use App\Models\AlumniTestimonial;
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

class AlumniTestimonialResource extends Resource
{
    protected static ?string $model = AlumniTestimonial::class;
    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string { return 'Konten Beranda'; }
    public static function getNavigationIcon(): \BackedEnum|string|null { return Heroicon::OutlinedAcademicCap; }
    public static function getModelLabel(): string { return 'Alumni Menginspirasi'; }
    public static function getPluralModelLabel(): string { return 'Alumni Menginspirasi'; }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && Gate::allows('view_any_alumni_testimonials');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Alumni')->schema([
                TextInput::make('name')->label('Nama')->required()->maxLength(255),
                TextInput::make('graduation_year')->label('Tahun Lulus')->numeric()->required()->minValue(1900)->maxValue(2099),
                TextInput::make('profession')->label('Profesi')->required()->maxLength(255),
                Textarea::make('quote')->label('Kutipan / Testimoni')->required()->rows(4),
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
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('graduation_year')->label('Thn Lulus')->sortable(),
            TextColumn::make('profession')->label('Profesi')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
        ])->filters([TrashedFilter::make()])->recordActions([\Filament\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAlumniTestimonials::route('/'),
            'create' => Pages\CreateAlumniTestimonial::route('/create'),
            'edit'   => Pages\EditAlumniTestimonial::route('/{record}/edit'),
        ];
    }
}
