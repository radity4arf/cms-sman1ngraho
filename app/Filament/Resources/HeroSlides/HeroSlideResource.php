<?php

/**
 * HeroSlideResource — Filament CRUD untuk Hero Slider (RT-15)
 *
 * Status default via HeroSlideConfig (single source of truth).
 * Tidak bisa dimutasi langsung dari form — gunakan tombol "Jadikan Default".
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-12 — Restrukturisasi: isDefault() via config
 */

// [THECHNOLOGY-CRE] : HeroSlideResource
// [THECHNOLOGY-MOD] : isDefault() via HeroSlideConfig — ganti boolean is_default

namespace App\Filament\Resources\HeroSlides;

use App\Filament\Resources\HeroSlides\Pages;
use App\Models\HeroSlide;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): string { return 'Konten Beranda'; }
    public static function getNavigationIcon(): \BackedEnum|string|null { return Heroicon::OutlinedPhoto; }
    public static function getModelLabel(): string { return 'Hero Slide'; }
    public static function getPluralModelLabel(): string { return 'Hero Slides'; }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && Gate::allows('view_any_hero_slides');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Slide')->schema([
                TextInput::make('title')->label('Judul')->required()->maxLength(255),
                Textarea::make('caption')->label('Caption')->maxLength(300)->rows(2),
                TextInput::make('cta_label')->label('Label Tombol')->maxLength(50)->nullable(),
                TextInput::make('cta_url')->label('URL Tombol')->maxLength(500)->nullable()->url(),
                \Filament\Forms\Components\Placeholder::make('is_default_status')
                    ->label('Status Default')
                    ->content(fn (?\App\Models\HeroSlide $record): string =>
                        $record?->isDefault()
                            ? 'Ya — slide ini adalah default (tidak dapat diubah langsung)'
                            : 'Bukan — gunakan tombol "Jadikan Default" di halaman edit untuk mempromosikan'
                    )
                    ->columnSpanFull(),
                TextInput::make('sort_order')->label('Urutan')->numeric()->default(0),
            ])->columns(2)->columnSpanFull(),
            Section::make('Media')->schema([
                SpatieMediaLibraryFileUpload::make('image')->label('Gambar')->collection('image')
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
            SpatieMediaLibraryImageColumn::make('image')->label('Gambar')->collection('image')->conversion('thumb'),
            TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            TextColumn::make('default_status')
                ->label('Default')
                ->badge()
                ->color(fn (HeroSlide $record): string => $record->isDefault() ? 'success' : 'gray')
                ->formatStateUsing(fn (HeroSlide $record): string => $record->isDefault() ? 'Ya' : 'Tidak')
                ->sortable(false),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
            TextColumn::make('sort_order')->label('Urutan')->sortable(),
        ])
            ->emptyStateHeading('Belum ada Hero Slide')
            ->emptyStateDescription('Klik tombol "Buat Hero Slide" untuk menambahkan slide baru.')
            ->filters([TrashedFilter::make()])->recordActions([\Filament\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListHeroSlides::route('/'),
            'create' => Pages\CreateHeroSlide::route('/create'),
            'edit'   => Pages\EditHeroSlide::route('/{record}/edit'),
        ];
    }
}
