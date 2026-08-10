<?php

/**
 * AgendaPolicy — Otorisasi granular untuk model Agenda (RT-04)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : AgendaPolicy — otorisasi CRUD Agenda

namespace App\Policies;

use App\Models\Agenda;
use App\Models\User;

class AgendaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_agendas');
    }

    public function view(User $user, Agenda $agenda): bool
    {
        return $user->can('view_any_agendas');
    }

    public function create(User $user): bool
    {
        return $user->can('create_agendas');
    }

    public function update(User $user, Agenda $agenda): bool
    {
        return $user->can('update_agendas');
    }

    public function delete(User $user, Agenda $agenda): bool
    {
        return $user->can('delete_agendas');
    }

    public function restore(User $user, Agenda $agenda): bool
    {
        return $user->can('update_agendas');
    }

    public function forceDelete(User $user, Agenda $agenda): bool
    {
        return $user->can('delete_agendas');
    }
}
