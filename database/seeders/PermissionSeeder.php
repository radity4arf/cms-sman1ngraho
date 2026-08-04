<?php

// [THECHNOLOGY-CRE-DSE] : PermissionSeeder — seed permission generik fondasi (bukan role tetap)
// ASUNSI: Daftar permission ini bersifat GENERIK dan akan diperluas di Fase 3+ seiring penambahan modul CMS konkret.
// Permission saat ini: manage_users, manage_content, manage_downloads
// Tidak ada role — permission di-assign langsung per user (granular).

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Daftar permission generik fondasi — diperluas bertahap di Fase 3+.
     */
    public static function getDefaultPermissions(): array
    {
        return [
            'manage_users'      => 'Kelola Pengguna',
            'manage_content'    => 'Kelola Konten',
            'manage_downloads'  => 'Kelola Unduhan',
        ];
    }

    public function run(): void
    {
        foreach (static::getDefaultPermissions() as $name => $label) {
            // [THECHNOLOGY-CRE-DSE] : firstOrCreate + update label — idempoten, aman dijalankan berkali-kali
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
            );

            // Set label secara eksplisit (kolom tambahan di tabel permissions)
            $permission->label = $label;
            $permission->save();
        }

        // Clear cache permission agar langsung terdeteksi
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
