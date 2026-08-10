<?php

/**
 * DownloadCategoryPolicy — Otorisasi granular untuk model DownloadCategory (RT-10)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : DownloadCategoryPolicy — otorisasi CRUD Kategori Unduhan

namespace App\Policies;

use App\Models\DownloadCategory;
use App\Models\User;

class DownloadCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_download_categories');
    }

    public function view(User $user, DownloadCategory $downloadCategory): bool
    {
        return $user->can('view_any_download_categories');
    }

    public function create(User $user): bool
    {
        return $user->can('create_download_categories');
    }

    public function update(User $user, DownloadCategory $downloadCategory): bool
    {
        return $user->can('update_download_categories');
    }

    public function delete(User $user, DownloadCategory $downloadCategory): bool
    {
        return $user->can('delete_download_categories');
    }

    public function restore(User $user, DownloadCategory $downloadCategory): bool
    {
        return $user->can('update_download_categories');
    }

    public function forceDelete(User $user, DownloadCategory $downloadCategory): bool
    {
        return $user->can('delete_download_categories');
    }
}
