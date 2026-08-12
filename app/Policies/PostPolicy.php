<?php

/**
 * PostPolicy — Otorisasi granular untuk model Post (RT-01 — Berita)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : PostPolicy — otorisasi CRUD Berita

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_posts');
    }

    public function view(User $user, Post $post): bool
    {
        return $user->can('view_any_posts');
    }

    public function create(User $user): bool
    {
        return $user->can('create_posts');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->can('update_posts');
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->can('delete_posts');
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->can('update_posts');
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $user->can('delete_posts');
    }
}
