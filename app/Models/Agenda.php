<?php

/**
 * Agenda Model (RT-04 — Agenda)
 *
 * Urut publik: event_date ASC. Scope published() menambah event_date >= today.
 * Tanpa sort_order — urut tanggal.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : Agenda model

namespace App\Models;

use App\Enums\ContentStatus;
use App\Traits\HasAudit;
use App\Traits\HasPublishWorkflow;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable([
    'title', 'event_date', 'location', 'description',
    'status', 'is_active', 'published_at',
])]
class Agenda extends Model
{
    use SoftDeletes, HasAudit, HasPublishWorkflow;

    protected function casts(): array
    {
        return [
            'event_date'   => 'date',
            'is_active'    => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Scope publik: published + event_date >= today (atau null untuk event mendatang via published_at).
     * Override HasPublishWorkflow::published() untuk menambah filter event_date.
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
            ->where('event_date', '>=', Carbon::today());
    }
}
