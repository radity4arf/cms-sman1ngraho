<?php

// [THECHNOLOGY-CRE] : RichContentPlugin — register YouTube extension ke RichEditor
// Menjembatani PHP extension + JS extension + toolbar tool + action modal

namespace App\Filament\Plugins;

use App\Filament\Actions\InsertYoutubeAction;
use App\Filament\Extensions\TipTap\YoutubeExtension;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\Plugins\Contracts\RichContentPlugin;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Support\Icons\Heroicon;
use Tiptap\Core\Extension;

class YoutubePlugin implements RichContentPlugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * @return array<Extension>
     */
    public function getTipTapPhpExtensions(): array
    {
        return [
            app(YoutubeExtension::class),
        ];
    }

    /**
     * @return array<string>
     */
    public function getTipTapJsExtensions(): array
    {
        return [
            '/js/rich-editor-youtube-extension.js',
        ];
    }

    /**
     * @return array<RichEditorTool>
     */
    public function getEditorTools(): array
    {
        return [
            RichEditorTool::make('youtube')
                ->label('Sisip Video YouTube')
                ->icon(Heroicon::VideoCamera)
                ->iconAlias('heroicon-o-video-camera')
                ->action('insertYoutube'),
        ];
    }

    /**
     * @return array<Action>
     */
    public function getEditorActions(): array
    {
        return [
            InsertYoutubeAction::make(),
        ];
    }
}
