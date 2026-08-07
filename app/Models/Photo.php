<?php

/**
 * Photo Model (RT-06 — Galeri: Foto)
 *
 * FK album_id → albums. Media koleksi: image.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : Photo model — Galeri (foto)

namespace App\Models;

use App\Traits\HasAudit;
use App\Traits\HasOrdering;
use App\Traits\HasPublishWorkflow;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'album_id', 'caption', 'alt_text',
    'sort_order', 'status', 'is_active', 'published_at',
])]
class Photo extends Model implements HasMedia
{
    use SoftDeletes, HasAudit, HasPublishWorkflow, HasOrdering;
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'sort_order'   => 'integer',
            'is_active'    => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(480)->height(320)
            ->performOnCollections('image');

        $this->addMediaConversion('medium')
            ->width(1200)->height(800)
            ->performOnCollections('image');
    }
}
