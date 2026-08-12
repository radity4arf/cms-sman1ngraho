<?php

/**
 * FacilityPolicy — Otorisasi granular untuk model Facility (RT-09)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : FacilityPolicy — otorisasi CRUD Fasilitas

namespace App\Policies;

use App\Models\Facility;
use App\Models\User;

class FacilityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_facilities');
    }

    public function view(User $user, Facility $facility): bool
    {
        return $user->can('view_any_facilities');
    }

    public function create(User $user): bool
    {
        return $user->can('create_facilities');
    }

    public function update(User $user, Facility $facility): bool
    {
        return $user->can('update_facilities');
    }

    public function delete(User $user, Facility $facility): bool
    {
        return $user->can('delete_facilities');
    }

    public function restore(User $user, Facility $facility): bool
    {
        return $user->can('update_facilities');
    }

    public function forceDelete(User $user, Facility $facility): bool
    {
        return $user->can('delete_facilities');
    }
}
