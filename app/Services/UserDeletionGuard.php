<?php

/**
 * UserDeletionGuard
 *
 * Shared guard service untuk pengecekan proteksi penghapusan user.
 * Mencakup 3 skenario sesuai requirement iterasi 2:
 *   1. Self-delete — tidak bisa menghapus akun sendiri.
 *   2. Super-admin — tidak bisa menghapus akun dengan flag is_super_admin.
 *   3. Last manage_users — tidak bisa menghapus satu-satunya pemegang
 *      permission manage_users (selain super-admin).
 *
 * Digunakan secara konsisten di EditUser.php (single delete) dan
 * UsersTable.php (bulk delete) untuk mencegah divergensi logic.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-04
 * @updated  2026-08-04
 */

// [THECHNOLOGY-CRE-DSE] : UserDeletionGuard — shared service untuk guard delete user;
// mencakup 3 skenario proteksi (self, super-admin, last manage_users) agar logic
// tidak terduplikasi antara single delete (EditUser) dan bulk delete (UsersTable)

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class UserDeletionGuard
{
    /**
     * Cek apakah sebuah record user dilindungi dari penghapusan.
     *
     * Mencakup ketiga skenario: self-delete, super-admin, dan last manage_users holder.
     * Digunakan untuk single-record delete (EditUser::hidden).
     *
     * @param  User  $record
     * @return bool  true jika record dilindungi (tidak boleh dihapus)
     */
    public static function isProtected(User $record): bool
    {
        // 1. Self-delete — tidak bisa menghapus akun sendiri
        if ($record->id === auth()->id()) {
            return true;
        }

        // 2. Super-admin — tidak bisa menghapus akun super-admin
        if ($record->is_super_admin) {
            return true;
        }

        // 3. Last manage_users holder — tidak bisa menghapus pemegang terakhir
        return self::wouldRemoveLastManageUsersHolder(collect([$record]));
    }

    /**
     * Cek apakah record adalah user yang sedang login atau super-admin.
     *
     * Hanya mencakup 2 skenario pertama (self + super-admin), tanpa query
     * tambahan untuk last manage_users. Berguna untuk iterasi per-record
     * di bulk delete — last-manage-users dicek terpisah di level batch.
     *
     * @param  User  $record
     * @return bool
     */
    public static function isSelfOrSuperAdmin(User $record): bool
    {
        return $record->id === auth()->id() || $record->is_super_admin;
    }

    /**
     * Cek apakah menghapus user-user dalam koleksi akan menghilangkan
     * pemegang terakhir permission manage_users.
     *
     * Super-admin tidak dihitung sebagai "pemegang permission manage_users"
     * karena akses mereka via Gate::before, bukan via permission list.
     *
     * @param  Collection  $records  Koleksi User yang akan dihapus
     * @return bool  true jika tidak ada pemegang manage_users lain di luar batch
     */
    public static function wouldRemoveLastManageUsersHolder(Collection $records): bool
    {
        $deletingIds = $records->pluck('id')->toArray();

        // Apakah ada user dalam batch yang memegang permission manage_users?
        $deletingHasManageUsers = User::whereIn('id', $deletingIds)
            ->whereHas('permissions', fn ($q) => $q->where('name', 'manage_users'))
            ->exists();

        if (!$deletingHasManageUsers) {
            return false;
        }

        // Hitung user lain (di luar batch) yang punya akses manage_users
        // — baik lewat permission langsung maupun flag is_super_admin
        $otherWithAccess = User::whereNotIn('id', $deletingIds)
            ->where(function ($q) {
                $q->where('is_super_admin', true)
                  ->orWhereHas('permissions', fn ($sq) => $sq->where('name', 'manage_users'));
            })
            ->count();

        return $otherWithAccess === 0;
    }
}
