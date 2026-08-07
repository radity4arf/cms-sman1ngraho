<?php

/**
 * Announcement Model (RT-05 — Pengumuman)
 *
 * Urut publik: created_at DESC. Scope published() menambah expired_at NULL OR >= today.
 * Tanpa sort_order.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : Announcement model

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
    'title', 'body', 'expired_at',
    'status', 'is_active', 'published_at',
])]
class Announcement extends Model
{
    use SoftDeletes, HasAudit, HasPublishWorkflow;

    protected function casts(): array
    {
        return [
            'expired_at'   => 'date',
            'is_active'    => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Scope publik: published + expired_at NULL or >= today.
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
            ->where(function (Builder $q) {
                $q->whereNull('expired_at')
                  ->orWhere('expired_at', '>=', Carbon::today());
            });
    }
}
