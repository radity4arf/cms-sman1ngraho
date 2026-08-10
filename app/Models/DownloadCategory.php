<?php

/**
 * DownloadCategory Model (RT-10 — Kategori Unduhan)
 *
 * Tabel struktural — baseline tanpa status/published_at.
 * Di-seed: Formulir, Kalender, Brosur, Surat.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : DownloadCategory model

namespace App\Models;

use App\Traits\HasAudit;
use App\Traits\HasOrdering;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'name', 'slug', 'sort_order', 'is_active',
])]
class DownloadCategory extends Model
{
    use HasFactory, SoftDeletes, HasAudit, HasOrdering;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    public function downloads(): HasMany
    {
        return $this->hasMany(Download::class);
    }

    // Slug mutation saat soft-delete
    protected static function booted(): void
    {
        static::saving(function (DownloadCategory $category) {
            if (empty($category->slug) && ! empty($category->name)) {
                $category->slug = static::generateUniqueSlug($category->name, $category->id);
            }
        });

        static::softDeleted(function (DownloadCategory $category) {
            $category->slug = $category->slug . '-archived-' . $category->id;
            $category->saveQuietly();
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
