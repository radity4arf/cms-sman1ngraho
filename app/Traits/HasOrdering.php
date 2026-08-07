<?php

/**
 * HasOrdering Trait
 *
 * Trait untuk ordering konten — scope ordered() = sort_order ASC, id ASC.
 * Tiebreak deterministik via id ASC.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : HasOrdering trait — scope ordered() untuk urutan tampil

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasOrdering
{
    /**
     * Boot trait: pastikan default sort_order = 0.
     */
    public function initializeHasOrdering(): void
    {
        if (! isset($this->attributes['sort_order'])) {
            $this->attributes['sort_order'] = 0;
        }
    }

    /**
     * Scope: urut berdasarkan sort_order ASC, tiebreak id ASC.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
    }
}
