<?php

/**
 * HeroSlide Model (RT-15 — Hero Slider)
 *
 * Status "default" sekarang ditentukan oleh hero_slide_config.default_hero_slide_id
 * (single source of truth), BUKAN oleh kolom boolean is_default.
 *
 * Guard bisnis (tetap relevan):
 * - Slide yang sedang default tidak bisa dihapus/draft/nonaktif.
 * - Promosi default melalui HeroSlideService::promoteAsDefault().
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 * @updated  2026-08-12 — Restrukturisasi: isDefault() via HeroSlideConfig, hapus swap mechanism
 */

// [THECHNOLOGY-CRE] : HeroSlide model — Hero Slider
// [THECHNOLOGY-MOD] : Restrukturisasi — isDefault() via HeroSlideConfig, hapus semua swap/guard lama

namespace App\Models;

use App\Enums\ContentStatus;
use App\Traits\HasAudit;
use App\Traits\HasOrdering;
use App\Traits\HasPublishWorkflow;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'title', 'caption', 'cta_label', 'cta_url',
    'sort_order', 'status', 'is_active', 'published_at',
])]
class HeroSlide extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasAudit, HasPublishWorkflow, HasOrdering;
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'sort_order'   => 'integer',
            'is_active'    => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    // ──────────────────────────────────────────────────
    // Default Status
    // ──────────────────────────────────────────────────

    /**
     * Apakah slide ini sedang menjadi default?
     * Cek ke hero_slide_config (single source of truth).
     */
    public function isDefault(): bool
    {
        return $this->id !== null
            && $this->id === HeroSlideConfig::defaultSlideId();
    }

    // ──────────────────────────────────────────────────
    // Media
    // ──────────────────────────────────────────────────

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

    // ──────────────────────────────────────────────────
    // Guard: Bisnis rules untuk slide default
    // ──────────────────────────────────────────────────

    protected static function booted(): void
    {
        // Guard: cegah soft-delete slide yang sedang default
        static::deleting(function (self $slide) {
            if ($slide->isDefault()) {
                throw new \RuntimeException('Slide default tidak dapat dihapus.');
            }
        });

        // Guard: cegah force-delete slide yang sedang default
        static::forceDeleting(function (self $slide) {
            if ($slide->isDefault()) {
                throw new \RuntimeException('Slide default tidak dapat dihapus permanen.');
            }
        });

        // Guard saving: tolak draft/nonaktif untuk slide yang sedang default
        static::saving(function (self $slide) {
            if (! $slide->isDefault()) {
                return;
            }

            // Cegah draft
            if ($slide->isDirty('status') && $slide->status === ContentStatus::Draft) {
                throw new \RuntimeException(
                    'Slide default tidak dapat berstatus draft.'
                );
            }

            // Cegah nonaktifkan
            if ($slide->isDirty('is_active') && $slide->is_active === false) {
                throw new \RuntimeException(
                    'Slide default tidak dapat dinonaktifkan.'
                );
            }
        });
    }
}
