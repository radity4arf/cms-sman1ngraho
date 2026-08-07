<?php

/**
 * Post Model (RT-01 — Berita)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : Post model — Berita

namespace App\Models;

use App\Traits\HasAudit;
use App\Traits\HasPublishWorkflow;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'title', 'slug', 'excerpt', 'body',
    'status', 'is_active', 'published_at',
])]
class Post extends Model implements HasMedia
{
    use SoftDeletes, HasAudit, HasPublishWorkflow;
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(480)
            ->height(320)
            ->performOnCollections('featured_image');

        $this->addMediaConversion('medium')
            ->width(1200)
            ->height(800)
            ->performOnCollections('featured_image');
    }

    // Slug mutation saat soft-delete (Arsitektur §9)
    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (empty($post->slug) && ! empty($post->title)) {
                $post->slug = static::generateUniqueSlug($post->title, $post->id);
            }
        });

        static::softDeleted(function (Post $post) {
            $post->slug = $post->slug . '-archived-' . $post->id;
            $post->saveQuietly();
        });
    }

    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $counter = 1;

        while (static::withTrashed()->where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
