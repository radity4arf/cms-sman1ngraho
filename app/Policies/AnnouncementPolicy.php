<?php

/**
 * AnnouncementPolicy — Otorisasi granular untuk model Announcement (RT-05)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : AnnouncementPolicy — otorisasi CRUD Pengumuman

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_announcements');
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->can('view_any_announcements');
    }

    public function create(User $user): bool
    {
        return $user->can('create_announcements');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->can('update_announcements');
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->can('delete_announcements');
    }

    public function restore(User $user, Announcement $announcement): bool
    {
        return $user->can('update_announcements');
    }

    public function forceDelete(User $user, Announcement $announcement): bool
    {
        return $user->can('delete_announcements');
    }
}
