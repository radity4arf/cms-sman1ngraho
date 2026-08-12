<?php

/**
 * AchievementPolicy — Otorisasi granular untuk model Achievement (RT-02 — Prestasi)
 *
 * Memetakan permission Fase 3: view_any_achievements, create_achievements,
 * update_achievements, delete_achievements — sesuai konvensi PermissionSeeder.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : AchievementPolicy — otorisasi CRUD Prestasi

namespace App\Policies;

use App\Models\Achievement;
use App\Models\User;

class AchievementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_achievements');
    }

    public function view(User $user, Achievement $achievement): bool
    {
        return $user->can('view_any_achievements');
    }

    public function create(User $user): bool
    {
        return $user->can('create_achievements');
    }

    public function update(User $user, Achievement $achievement): bool
    {
        return $user->can('update_achievements');
    }

    public function delete(User $user, Achievement $achievement): bool
    {
        return $user->can('delete_achievements');
    }

    public function restore(User $user, Achievement $achievement): bool
    {
        return $user->can('update_achievements');
    }

    public function forceDelete(User $user, Achievement $achievement): bool
    {
        return $user->can('delete_achievements');
    }
}
