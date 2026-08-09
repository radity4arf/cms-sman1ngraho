<?php

// [THECHNOLOGY-CRE] : PHP TipTap extension — node YouTube untuk server-side render/parse
// Validasi whitelist domain youtube.com / youtu.be, responsive wrapper aspect-ratio 16:9

namespace App\Filament\Extensions\TipTap;

use Tiptap\Core\Node;

class YoutubeExtension extends Node
{
    /** @var string */
    public static $name = 'youtube';

    /**
     * @return array<array<string, mixed>>
     */
    public function parseHTML(): array
    {
        return [
            ['tag' => 'div[data-youtube-video]'],
            ['tag' => 'iframe[src*="youtube.com"]'],
            ['tag' => 'iframe[src*="youtu.be"]'],
        ];
    }

    /**
     * @param  object  $node
     * @param  array<string, mixed>  $HTMLAttributes
     * @return array<mixed>
     */
    public function renderHTML($node, $HTMLAttributes = []): array
    {
        $src = $HTMLAttributes['data-youtube-src'] ?? ($node->attrs->src ?? '');

        $videoId = $this->extractYoutubeId($src);

        if ($videoId === null) {
            return ['div', [], '[Invalid YouTube URL]'];
        }

        $start = (int) ($HTMLAttributes['data-youtube-start'] ?? ($node->attrs->start ?? 0));
        $embedUrl = "https://www.youtube.com/embed/{$videoId}" . ($start > 0 ? "?start={$start}" : '');

        return [
            'div',
            [
                'data-youtube-video' => '',
                'style' => 'position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%;',
            ],
            [
                'iframe',
                [
                    'src' => $embedUrl,
                    'style' => 'position: absolute; top: 0; left: 0; width: 100%; height: 100%;',
                    'frameborder' => '0',
                    'allowfullscreen' => 'true',
                    'allow' => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture',
                    'referrerpolicy' => 'strict-origin-when-cross-origin',
                ],
            ],
        ];
    }

    /**
     * Ekstrak YouTube video ID dari berbagai format URL.
     * Hanya whitelist domain youtube.com dan youtu.be.
     */
    public function extractYoutubeId(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $patterns = [
            '#(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/|youtube\.com/live/)([a-zA-Z0-9_-]{11})#',
            '#youtube\.com/watch\?.*[?&]v=([a-zA-Z0-9_-]{11})#',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Validasi apakah URL adalah YouTube yang valid.
     */
    public static function isValidYoutubeUrl(?string $url): bool
    {
        if (blank($url)) {
            return false;
        }

        $instance = app(static::class);
        $id = $instance->extractYoutubeId($url);

        return $id !== null && strlen($id) === 11;
    }
}
