<?php

/**
 * HasPublishWorkflow Trait
 *
 * Trait untuk workflow Draft → Publish.
 * Menyediakan:
 *   - Cast enum ContentStatus pada kolom 'status'
 *   - Scope published() — filter record yang siap tampil publik
 *   - Helper isPublic(), isDraft(), publish(), unpublish()
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : HasPublishWorkflow trait — scope published() + cast ContentStatus

namespace App\Traits;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait HasPublishWorkflow
{
    /**
     * Boot trait: pastikan model punya cast untuk kolom status.
     */
    public function initializeHasPublishWorkflow(): void
    {
        if (! isset($this->casts['status'])) {
            $this->casts['status'] = ContentStatus::class;
        }
    }

    /**
     * Scope: hanya record yang published DAN aktif DAN published_at ≤ sekarang.
     * Dipakai oleh query publik (Fase 4/5).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ContentStatus::Published->value)
            ->where('is_active', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                  ->orWhere('published_at', '<=', Carbon::now());
            });
    }

    /**
     * Scope: hanya record draft.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Draft->value);
    }

    /**
     * Apakah record ini siap tampil di publik?
     */
    public function isPublic(): bool
    {
        return $this->status === ContentStatus::Published
            && $this->is_active
            && ($this->published_at === null || $this->published_at <= Carbon::now());
    }

    /**
     * Apakah record ini draft?
     */
    public function isDraft(): bool
    {
        return $this->status === ContentStatus::Draft;
    }

    /**
     * Publish record (set status + published_at).
     */
    public function publish(): void
    {
        $this->status = ContentStatus::Published;
        if (is_null($this->published_at)) {
            $this->published_at = Carbon::now();
        }
        $this->save();
    }

    /**
     * Kembalikan ke draft.
     */
    public function unpublish(): void
    {
        $this->status = ContentStatus::Draft;
        $this->save();
    }
}
