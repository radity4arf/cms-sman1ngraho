<?php

/**
 * HeroSlidePolicy — Otorisasi granular untuk model HeroSlide (RT-15)
 *
 * Spesial: record is_default=true tidak bisa di-delete/draft/nonaktif.
 * Guard model (booted) + Policy (delete) = double layer perlindungan.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : HeroSlidePolicy — otorisasi CRUD Hero Slide + guard is_default

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
     * [THECHNOLOGY-MOD] : Double guard — selain permission delete_hero_slides,
     * record is_default=true tidak boleh dihapus (RT-15 edge case #4).
     */
    public function delete(User $user, HeroSlide $heroSlide): bool
    {
        if (! $user->can('delete_hero_slides')) {
            return false;
        }

        if ($heroSlide->is_default) {
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
