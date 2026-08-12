<?php

/**
 * StaffPolicy — Otorisasi granular untuk model Staff (RT-07)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : StaffPolicy — otorisasi CRUD Staff

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;

class StaffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_staff');
    }

    public function view(User $user, Staff $staff): bool
    {
        return $user->can('view_any_staff');
    }

    public function create(User $user): bool
    {
        return $user->can('create_staff');
    }

    public function update(User $user, Staff $staff): bool
    {
        return $user->can('update_staff');
    }

    public function delete(User $user, Staff $staff): bool
    {
        return $user->can('delete_staff');
    }

    public function restore(User $user, Staff $staff): bool
    {
        return $user->can('update_staff');
    }

    public function forceDelete(User $user, Staff $staff): bool
    {
        return $user->can('delete_staff');
    }
}
