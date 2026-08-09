<?php

// [THECHNOLOGY-CRE] : Action modal — sisip video YouTube ke RichEditor
// Validasi whitelist domain (youtube.com / youtu.be), tolak URL non-YouTube

namespace App\Filament\Actions;

use App\Filament\Extensions\TipTap\YoutubeExtension;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Livewire\Component;

class InsertYoutubeAction
{
    public static function make(): Action
    {
        return Action::make('insertYoutube')
            ->label('Sisip Video YouTube')
            ->modalHeading('Sisip Video YouTube')
            ->modalDescription('Masukkan URL video YouTube (youtube.com atau youtu.be). Video akan ditampilkan sebagai embed responsif di posisi kursor saat ini.')
            ->modalWidth(Width::Large)
            ->schema([
                TextInput::make('url')
                    ->label('URL Video YouTube')
                    ->placeholder('https://www.youtube.com/watch?v=... atau https://youtu.be/...')
                    ->url()
                    ->required()
                    ->maxLength(2048)
                    ->autofocus()
                    ->helperText('Hanya URL dari youtube.com atau youtu.be yang diterima. URL dari platform lain (Vimeo, Dailymotion, dll.) akan ditolak.'),
            ])
            ->action(function (array $arguments, array $data, RichEditor $component, Component $livewire): void {
                $url = trim($data['url'] ?? '');

                if (blank($url)) {
                    Notification::make()
                        ->title('URL tidak boleh kosong')
                        ->danger()
                        ->send();

                    return;
                }

                // Validasi whitelist domain
                if (! YoutubeExtension::isValidYoutubeUrl($url)) {
                    Notification::make()
                        ->title('URL tidak valid')
                        ->body('Hanya URL video YouTube yang diterima (youtube.com/watch, youtu.be, youtube.com/embed, youtube.com/shorts). URL dari platform lain tidak didukung.')
                        ->danger()
                        ->send();

                    return;
                }

                // Insert youtube node ke editor
                $component->runCommands(
                    [
                        EditorCommand::make('insertContent', arguments: [[
                            'type' => 'youtube',
                            'attrs' => [
                                'src' => $url,
                            ],
                        ]]),
                    ],
                    editorSelection: $arguments['editorSelection'],
                );

                Notification::make()
                    ->title('Video YouTube berhasil disisipkan')
                    ->success()
                    ->send();
            });
    }
}
