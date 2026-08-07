<?php

/**
 * Facility Model (RT-09 — Fasilitas)
 *
 * CRUD penuh — field: name, description (500), photo (media nullable), sort_order.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : Facility model

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
    'name', 'description',
    'sort_order', 'status', 'is_active', 'published_at',
])]
class Facility extends Model implements HasMedia
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
