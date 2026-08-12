<?php

/**
 * AlumniTestimonialPolicy — Otorisasi granular untuk model AlumniTestimonial (RT-03)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : AlumniTestimonialPolicy — otorisasi CRUD Alumni

namespace App\Policies;

use App\Models\AlumniTestimonial;
use App\Models\User;

class AlumniTestimonialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_alumni_testimonials');
    }

    public function view(User $user, AlumniTestimonial $alumniTestimonial): bool
    {
        return $user->can('view_any_alumni_testimonials');
    }

    public function create(User $user): bool
    {
        return $user->can('create_alumni_testimonials');
    }

    public function update(User $user, AlumniTestimonial $alumniTestimonial): bool
    {
        return $user->can('update_alumni_testimonials');
    }

    public function delete(User $user, AlumniTestimonial $alumniTestimonial): bool
    {
        return $user->can('delete_alumni_testimonials');
    }

    public function restore(User $user, AlumniTestimonial $alumniTestimonial): bool
    {
        return $user->can('update_alumni_testimonials');
    }

    public function forceDelete(User $user, AlumniTestimonial $alumniTestimonial): bool
    {
        return $user->can('delete_alumni_testimonials');
    }
}
