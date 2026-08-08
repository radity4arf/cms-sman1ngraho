<?php

/**
 * PostResource — Filament CRUD untuk Berita (RT-01)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : PostResource — CRUD Berita

namespace App\Filament\Resources\Posts;

use App\Filament\Resources\Posts\Pages;
use App\Models\Post;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
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

class PostResource extends Resource
{
    protected static ?string $model = Post::class;
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): string { return 'Konten Beranda'; }
    public static function getNavigationIcon(): \BackedEnum|string|null { return Heroicon::OutlinedNewspaper; }
    public static function getModelLabel(): string { return 'Berita'; }
    public static function getPluralModelLabel(): string { return 'Berita'; }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && Gate::allows('view_any_posts');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Konten')->schema([
                TextInput::make('title')->label('Judul')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn ($set, $state) => $set('slug', Post::generateUniqueSlug($state))),
                TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                Textarea::make('excerpt')->label('Ringkasan')->maxLength(300)->rows(3),
                RichEditor::make('body')->label('Isi Berita')->required()->columnSpanFull(),
            ])->columns(2),
            Section::make('Media')->schema([
                SpatieMediaLibraryFileUpload::make('featured_image')
                    ->label('Gambar Utama')->collection('featured_image')
                    ->image()->imageEditor()->acceptedFileTypes(['image/jpeg','image/png','image/webp'])
                    ->maxSize(10240),
            ]),
            Section::make('Status')->schema([
                Select::make('status')->label('Status')->options([
                    'draft' => 'Draft', 'published' => 'Publish',
                ])->default('draft')->required(),
                Toggle::make('is_active')->label('Aktif')->default(true),
                DateTimePicker::make('published_at')->label('Tanggal Terbit')->nullable(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            SpatieMediaLibraryImageColumn::make('featured_image')->label('Gambar')->circular()->collection('featured_image')->conversion('thumb'),
            TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
            TextColumn::make('published_at')->label('Terbit')->dateTime('d M Y H:i')->sortable(),
            TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y')->sortable(),
        ])->filters([
            TrashedFilter::make(),
        ])->recordActions([
            \Filament\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit'   => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
