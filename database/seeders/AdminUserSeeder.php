<?php

// [THECHNOLOGY-CRE-DSE] : AdminUserSeeder — buat akun admin awal (superuser) dengan flag is_super_admin
// Kredensial default: admin@sman1ngraho.sch.id / password (WAJIB diganti di production!)
// Flag is_super_admin = true memberi akses penuh via Gate::before tanpa bergantung permission list.
// Permission list tetap di-assign untuk backward compatibility (cek hasPermissionTo langsung di model).

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // [THECHNOLOGY-CRE-DSE] : firstOrCreate — idempoten, hanya buat admin kalau belum ada
        $admin = User::firstOrCreate(
            ['email' => 'admin@sman1ngraho.sch.id'],
            [
                'name'     => 'Administrator',
                'password' => bcrypt('password'), // WAJIB diganti di production!
            ]
        );

        // [THECHNOLOGY-CRE-DSE] : set flag eksplisit super-admin — ini yang dipakai Gate::before,
        // tidak bergantung pada daftar permission yang bisa berubah di Fase 3+
        // Gunakan property assignment langsung (bukan update()) karena is_super_admin tidak ada di Fillable
        $admin->is_super_admin = true;
        $admin->save();

        // [THECHNOLOGY-CRE-DSE] : tetap assign semua permission untuk backward compatibility
        // (misal kalau ada pengecekan manual $admin->hasPermissionTo() di luar Gate)
        $allPermissions = Permission::pluck('name')->toArray();

        if (!empty($allPermissions)) {
            $admin->syncPermissions($allPermissions);
        }
    }
}
