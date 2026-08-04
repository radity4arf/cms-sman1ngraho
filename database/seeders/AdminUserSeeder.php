<?php

// [THECHNOLOGY-MOD-DSE] : AdminUserSeeder — buat akun admin awal (superuser) dengan flag is_super_admin
// Password diambil dari env('ADMIN_DEFAULT_PASSWORD'). Jika kosong, generate random & log ke output.
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
        // [THECHNOLOGY-MOD-DSE] : firstOrCreate — idempoten, hanya buat admin kalau belum ada.
        // Password dari env('ADMIN_DEFAULT_PASSWORD'), fallback ke random string jika kosong.
        $password = env('ADMIN_DEFAULT_PASSWORD') ?: \Illuminate\Support\Str::random(16);

        $admin = User::firstOrCreate(
            ['email' => 'admin@sman1ngraho.sch.id'],
            [
                'name'     => 'Administrator',
                'password' => bcrypt($password),
            ]
        );

        // [THECHNOLOGY-MOD-DSE] : log password ke output seeder jika bukan dari env (random-generated)
        // supaya developer tahu password yang harus dipakai untuk login pertama.
        if (!env('ADMIN_DEFAULT_PASSWORD')) {
            $this->command->warn('=================================================');
            $this->command->warn('  PENTING: Password admin di-generate otomatis!');
            $this->command->warn("  Email    : admin@sman1ngraho.sch.id");
            $this->command->warn("  Password : {$password}");
            $this->command->warn('  Simpan password ini. Tidak akan ditampilkan lagi.');
            $this->command->warn('  Atau set ADMIN_DEFAULT_PASSWORD di .env lalu re-seed.');
            $this->command->warn('=================================================');
        }

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
