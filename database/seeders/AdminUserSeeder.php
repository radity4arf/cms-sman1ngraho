<?php

// [THECHNOLOGY-CRE-DSE] : AdminUserSeeder — buat akun admin awal (superuser) dengan semua permission
// Kredensial default: admin@sman1ngraho.sch.id / password (WAJIB diganti di production!)
// Admin akan memiliki SEMUA permission yang ada — dikenali sebagai super-admin via Gate::before di AppServiceProvider

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

        // [THECHNOLOGY-CRE-DSE] : berikan SEMUA permission ke admin — ini menjadikannya super-admin
        // via Gate::before di AppServiceProvider (cek hasAllPermissions)
        $allPermissions = Permission::pluck('name')->toArray();

        if (!empty($allPermissions)) {
            $admin->syncPermissions($allPermissions);
        } else {
            // Fallback: kalau permission belum di-seed (misal dijalankan duluan sebelum PermissionSeeder)
            // Tidak error, admin tetap bisa login — permission akan di-assign saat PermissionSeeder dijalankan
        }
    }
}
