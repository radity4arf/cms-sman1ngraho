<?php

/**
 * UserPolicy — Otorisasi untuk model User (Fase 2 legacy)
 *
 * User memakai permission Fase 2 `manage_users` untuk semua aksi —
 * tidak mengikuti konvensi granular Fase 3 (view_any_users, dll)
 * karena manajemen user adalah fitur fondasi yang tidak dipecah.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : UserPolicy — otorisasi manajemen user (Fase 2 legacy)

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage_users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('manage_users');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('manage_users');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('manage_users');
    }
}
