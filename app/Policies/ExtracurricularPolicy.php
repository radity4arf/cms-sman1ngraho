<?php

/**
 * ExtracurricularPolicy — Otorisasi granular untuk model Extracurricular (RT-08)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : ExtracurricularPolicy — otorisasi CRUD Ekskul

namespace App\Policies;

use App\Models\Extracurricular;
use App\Models\User;

class ExtracurricularPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_extracurriculars');
    }

    public function view(User $user, Extracurricular $extracurricular): bool
    {
        return $user->can('view_any_extracurriculars');
    }

    public function create(User $user): bool
    {
        return $user->can('create_extracurriculars');
    }

    public function update(User $user, Extracurricular $extracurricular): bool
    {
        return $user->can('update_extracurriculars');
    }

    public function delete(User $user, Extracurricular $extracurricular): bool
    {
        return $user->can('delete_extracurriculars');
    }

    public function restore(User $user, Extracurricular $extracurricular): bool
    {
        return $user->can('update_extracurriculars');
    }

    public function forceDelete(User $user, Extracurricular $extracurricular): bool
    {
        return $user->can('delete_extracurriculars');
    }
}
