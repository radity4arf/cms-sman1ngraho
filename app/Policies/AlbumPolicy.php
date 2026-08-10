<?php

/**
 * AlbumPolicy — Otorisasi granular untuk model Album (RT-06 — Galeri)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : AlbumPolicy — otorisasi CRUD Album

namespace App\Policies;

use App\Models\Album;
use App\Models\User;

class AlbumPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_albums');
    }

    public function view(User $user, Album $album): bool
    {
        return $user->can('view_any_albums');
    }

    public function create(User $user): bool
    {
        return $user->can('create_albums');
    }

    public function update(User $user, Album $album): bool
    {
        return $user->can('update_albums');
    }

    public function delete(User $user, Album $album): bool
    {
        return $user->can('delete_albums');
    }

    public function restore(User $user, Album $album): bool
    {
        return $user->can('update_albums');
    }

    public function forceDelete(User $user, Album $album): bool
    {
        return $user->can('delete_albums');
    }
}
