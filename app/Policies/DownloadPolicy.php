<?php

/**
 * DownloadPolicy — Otorisasi granular untuk model Download (RT-10)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : DownloadPolicy — otorisasi CRUD Unduhan

namespace App\Policies;

use App\Models\Download;
use App\Models\User;

class DownloadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_downloads');
    }

    public function view(User $user, Download $download): bool
    {
        return $user->can('view_any_downloads');
    }

    public function create(User $user): bool
    {
        return $user->can('create_downloads');
    }

    public function update(User $user, Download $download): bool
    {
        return $user->can('update_downloads');
    }

    public function delete(User $user, Download $download): bool
    {
        return $user->can('delete_downloads');
    }

    public function restore(User $user, Download $download): bool
    {
        return $user->can('update_downloads');
    }

    public function forceDelete(User $user, Download $download): bool
    {
        return $user->can('delete_downloads');
    }
}
