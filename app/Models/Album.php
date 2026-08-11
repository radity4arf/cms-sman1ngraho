<?php

/**
 * Album Model (RT-06 — Galeri: Album)
 *
 * Cover = foto pertama dari relasi photos().
 * Scope publik: published + punya minimal 1 foto published+active.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : Album model — Galeri

namespace App\Models;

use App\Enums\ContentStatus;
use App\Traits\HasAudit;
use App\Traits\HasOrdering;
use App\Traits\HasPublishWorkflow;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'description',
    'sort_order', 'status', 'is_active', 'published_at',
])]
class Album extends Model
{
    use HasFactory, SoftDeletes, HasAudit, HasPublishWorkflow, HasOrdering;

    protected function casts(): array
    {
        return [
            'sort_order'   => 'integer',
            'is_active'    => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    // [THECHNOLOGY-FIX] : Terapkan scope ordered() dari HasOrdering ke relasi
    // supaya urutan sort_order + tiebreak id ASC (RT-17) benar-benar jalan,
    // bukan cuma tersedia tapi tidak dipakai (CGX Fase 3 Minor #4).
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->ordered();
    }

    /**
     * Scope publik: published + punya ≥1 foto published+active.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ContentStatus::Published->value)
            ->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', Carbon::now());
            })
            ->whereHas('photos', function (Builder $q) {
                $q->where('status', ContentStatus::Published->value)
                  ->where('is_active', true);
            });
    }

    // Slug mutation saat soft-delete
    protected static function booted(): void
    {
        static::saving(function (Album $album) {
            if (empty($album->slug) && ! empty($album->name)) {
                $album->slug = static::generateUniqueSlug($album->name, $album->id);
            }
        });

        static::softDeleted(function (Album $album) {
            $album->slug = $album->slug . '-archived-' . $album->id;
            $album->saveQuietly();
            // Soft-delete semua foto
            $album->photos()->delete();
        });

        static::restored(function (Album $album) {
            $album->photos()->withTrashed()->restore();
        });
    }

    public static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $counter = 1;

        while (static::withTrashed()->where('slug', $slug)->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
