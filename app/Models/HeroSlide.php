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

use App\Enums\ContentStatus;
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

    // [THECHNOLOGY-FIX] : Model-level guard — record is_default=true tidak bisa delete/draft/nonaktif
    // Guard ini berlaku di SEMUA level (UI, Tinker, API, job) — bukan cuma UI hiding.
    protected static function booted(): void
    {
        // Cegah soft-delete record default
        static::deleting(function (self $slide) {
            if ($slide->is_default) {
                throw new \RuntimeException('Slide default tidak dapat dihapus.');
            }
        });

        // Cegah force-delete record default
        static::forceDeleting(function (self $slide) {
            if ($slide->is_default) {
                throw new \RuntimeException('Slide default tidak dapat dihapus permanen.');
            }
        });

        // Cegah update status ke draft atau nonaktifkan record default (Edge Case #4)
        static::updating(function (self $slide) {
            if (! $slide->is_default) {
                return;
            }
            if ($slide->isDirty('status') && $slide->status === ContentStatus::Draft) {
                throw new \RuntimeException('Slide default tidak dapat diubah menjadi draft.');
            }
            if ($slide->isDirty('is_active') && $slide->is_active === false) {
                throw new \RuntimeException('Slide default tidak dapat dinonaktifkan.');
            }
        });
    }
}
