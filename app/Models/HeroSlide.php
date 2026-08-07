<?php

/**
 * HeroSlide Model (RT-15 — Hero Slider)
 *
 * is_default=true: guarded dari delete/draft/nonaktif via policy.
 * Seeder wajib buat 1 record is_default=true published+active.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : HeroSlide model — Hero Slider

namespace App\Models;

use App\Traits\HasAudit;
use App\Traits\HasOrdering;
use App\Traits\HasPublishWorkflow;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'title', 'caption', 'cta_label', 'cta_url',
    'is_default', 'sort_order', 'status', 'is_active', 'published_at',
])]
class HeroSlide extends Model implements HasMedia
{
    use SoftDeletes, HasAudit, HasPublishWorkflow, HasOrdering;
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'is_default'   => 'boolean',
            'sort_order'   => 'integer',
            'is_active'    => 'boolean',
            'published_at' => 'datetime',
        ];
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
            ->width(480)->height(270)
            ->performOnCollections('image');

        $this->addMediaConversion('medium')
            ->width(1920)->height(1080)
            ->performOnCollections('image');
    }
}
