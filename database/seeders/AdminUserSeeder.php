<?php

// [THECHNOLOGY-MOD] : AdminUserSeeder — buat akun admin awal (superuser) dengan flag is_super_admin
// Password diambil dari env('ADMIN_DEFAULT_PASSWORD'). Jika kosong, generate random & log ke output.
// Flag is_super_admin = true memberi akses penuh via Gate::before tanpa bergantung permission list.
// [THECHNOLOGY-FIX] : HAPUS auto-assignment permission Fase 3 ke admin (CGX Fase 3 Minor #3).
// Seeder hanya DAFTARKAN permission (via RoleAndPermissionSeeder), TIDAK assign otomatis.
// Assignment permission tetap manual via UI sesuai spec asli.

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // firstOrCreate — idempoten, hanya buat admin kalau belum ada.
        // Password dari env('ADMIN_DEFAULT_PASSWORD'), fallback ke random string jika kosong.
        $password = env('ADMIN_DEFAULT_PASSWORD') ?: \Illuminate\Support\Str::random(16);

        $admin = User::firstOrCreate(
            ['email' => 'admin@sman1ngraho.sch.id'],
            [
                'name'     => 'Administrator',
                'password' => bcrypt($password),
            ]
        );

        // log password ke output seeder jika bukan dari env (random-generated)
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

        // set flag eksplisit super-admin — ini yang dipakai Gate::before,
        // tidak bergantung pada daftar permission yang bisa berubah di Fase 3+
        // Gunakan property assignment langsung (bukan update()) karena is_super_admin tidak ada di Fillable
        $admin->is_super_admin = true;
        $admin->save();
    }
}
