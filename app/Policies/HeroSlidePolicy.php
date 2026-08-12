<?php

/**
 * HeroSlidePolicy — Otorisasi granular untuk model HeroSlide (RT-15)
 *
 * Spesial: slide yang sedang default tidak bisa di-delete.
 * Cek status default via HeroSlide::isDefault() (→ hero_slide_config).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 * @updated  2026-08-12 — Restrukturisasi: isDefault() via config, bukan kolom is_default
 */

// [THECHNOLOGY-CRE] : HeroSlidePolicy — otorisasi CRUD Hero Slide + guard default
// [THECHNOLOGY-MOD] : isDefault() via HeroSlideConfig, bukan boolean is_default

namespace App\Policies;

use App\Models\HeroSlide;
use App\Models\User;

class HeroSlidePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_hero_slides');
    }

    public function view(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('view_any_hero_slides');
    }

    public function create(User $user): bool
    {
        return $user->can('create_hero_slides');
    }

    public function update(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('update_hero_slides');
    }

    /**
     * Double guard: selain permission delete_hero_slides,
     * slide yang sedang default tidak boleh dihapus.
     */
    public function delete(User $user, HeroSlide $heroSlide): bool
    {
        if (! $user->can('delete_hero_slides')) {
            return false;
        }

        if ($heroSlide->isDefault()) {
            return false;
        }

        return true;
    }

    public function restore(User $user, HeroSlide $heroSlide): bool
    {
        return $user->can('update_hero_slides');
    }

    public function forceDelete(User $user, HeroSlide $heroSlide): bool
    {
        return $this->delete($user, $heroSlide);
    }
}
