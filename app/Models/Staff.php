<?php

/**
 * Staff Model (RT-07 — Guru & Tenaga Kependidikan)
 *
 * category: StaffCategory enum (guru, tenaga_kependidikan) — wajib.
 * TANPA kolom NIP (keputusan AMB-01).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : Staff model — Guru & Tenaga Kependidikan

namespace App\Models;

use App\Enums\StaffCategory;
use App\Traits\HasAudit;
use App\Traits\HasOrdering;
use App\Traits\HasPublishWorkflow;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable([
    'name', 'category', 'position', 'subject',
    'sort_order', 'status', 'is_active', 'published_at',
])]
class Staff extends Model implements HasMedia
{
    use SoftDeletes, HasAudit, HasPublishWorkflow, HasOrdering;
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'category'     => StaffCategory::class,
            'sort_order'   => 'integer',
            'is_active'    => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\Conversions\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(480)->height(320)
            ->performOnCollections('photo');

        $this->addMediaConversion('medium')
            ->width(1200)->height(800)
            ->performOnCollections('photo');
    }
}
