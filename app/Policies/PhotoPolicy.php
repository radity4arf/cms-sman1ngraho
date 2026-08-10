<?php

/**
 * PhotoPolicy — Otorisasi granular untuk model Photo (RT-06 — Galeri: Foto)
 *
 * Photo diakses via Album Relation Manager; Policy tetap standalone
 * supaya bisa dipakai di RelationManager (Filament mendukung Policy
 * di RelationManager secara otomatis).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : PhotoPolicy — otorisasi CRUD Foto

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;

class PhotoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_photos');
    }

    public function view(User $user, Photo $photo): bool
    {
        return $user->can('view_any_photos');
    }

    public function create(User $user): bool
    {
        return $user->can('create_photos');
    }

    public function update(User $user, Photo $photo): bool
    {
        return $user->can('update_photos');
    }

    public function delete(User $user, Photo $photo): bool
    {
        return $user->can('delete_photos');
    }

    public function restore(User $user, Photo $photo): bool
    {
        return $user->can('update_photos');
    }

    public function forceDelete(User $user, Photo $photo): bool
    {
        return $user->can('delete_photos');
    }
}
