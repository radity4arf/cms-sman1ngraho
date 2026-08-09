<?php

/**
 * AgendaResource — Filament CRUD untuk Agenda (RT-04)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : AgendaResource

namespace App\Filament\Resources\Agendas;

use App\Filament\Resources\Agendas\Pages;
use App\Models\Agenda;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

class AgendaResource extends Resource
{
    protected static ?string $model = Agenda::class;
    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): string { return 'Konten Beranda'; }
    public static function getNavigationIcon(): \BackedEnum|string|null { return Heroicon::OutlinedCalendarDays; }
    public static function getModelLabel(): string { return 'Agenda'; }
    public static function getPluralModelLabel(): string { return 'Agenda'; }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && Gate::allows('view_any_agendas');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            // [THECHNOLOGY-MOD] : field vertikal penuh — hapus columns(2), semua field columnSpanFull
            Section::make('Informasi Agenda')->schema([
                TextInput::make('title')->label('Judul')->required()->maxLength(255)->columnSpanFull(),
                DatePicker::make('event_date')->label('Tanggal')->required()->columnSpanFull(),
                TextInput::make('location')->label('Lokasi')->nullable()->maxLength(255)->columnSpanFull(),
                Textarea::make('description')->label('Deskripsi')->nullable()->rows(3)->columnSpanFull(),
            ])->columnSpanFull(),
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
            TextColumn::make('event_date')->label('Tanggal')->date('d M Y')->sortable(),
            TextColumn::make('location')->label('Lokasi')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn ($s) => $s === 'published' ? 'success' : 'warning')->sortable(),
            ToggleColumn::make('is_active')->label('Aktif')->sortable(),
        ])->filters([TrashedFilter::make()])->recordActions([\Filament\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAgendas::route('/'),
            'create' => Pages\CreateAgenda::route('/create'),
            'edit'   => Pages\EditAgenda::route('/{record}/edit'),
        ];
    }
}
