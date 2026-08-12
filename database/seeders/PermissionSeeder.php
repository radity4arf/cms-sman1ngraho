<?php

// [THECHNOLOGY-CRE] : PermissionSeeder — seed permission Fase 2 (foundation) + Fase 3 (granular per resource)
// Konvensi penamaan: snake_case English untuk name, Bahasa Indonesia untuk label.
// Tidak ada role — permission di-assign langsung per user (granular).
// Fase 3 menambah 52 permission granular (13 resource × 4 CRUD) mengikuti pola view_any_{table}, create_{table}, update_{table}, delete_{table}.

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Daftar permission generik fondasi Fase 2 — tetap dipertahankan.
     */
    public static function getDefaultPermissions(): array
    {
        return [
            'manage_users'      => 'Kelola Pengguna',
            'manage_content'    => 'Kelola Konten',
            'manage_downloads'  => 'Kelola Unduhan',
        ];
    }

    /**
     * [THECHNOLOGY-CRE] : Daftar permission granular Fase 3 — 13 resource × 4 CRUD.
     * Konvensi: view_any_{table}, create_{table}, update_{table}, delete_{table}.
     * Tidak auto-assign ke user mana pun — assignment manual via UI.
     */
    public static function getFase3Permissions(): array
    {
        return [
            // Posts (Berita)
            'view_any_posts'   => 'Lihat Berita',
            'create_posts'     => 'Buat Berita',
            'update_posts'     => 'Edit Berita',
            'delete_posts'     => 'Hapus Berita',

            // Achievements (Prestasi)
            'view_any_achievements'   => 'Lihat Prestasi',
            'create_achievements'     => 'Buat Prestasi',
            'update_achievements'     => 'Edit Prestasi',
            'delete_achievements'     => 'Hapus Prestasi',

            // Alumni Testimonials
            'view_any_alumni_testimonials'   => 'Lihat Alumni',
            'create_alumni_testimonials'     => 'Buat Alumni',
            'update_alumni_testimonials'     => 'Edit Alumni',
            'delete_alumni_testimonials'     => 'Hapus Alumni',

            // Agendas
            'view_any_agendas'   => 'Lihat Agenda',
            'create_agendas'     => 'Buat Agenda',
            'update_agendas'     => 'Edit Agenda',
            'delete_agendas'     => 'Hapus Agenda',

            // Announcements
            'view_any_announcements'   => 'Lihat Pengumuman',
            'create_announcements'     => 'Buat Pengumuman',
            'update_announcements'     => 'Edit Pengumuman',
            'delete_announcements'     => 'Hapus Pengumuman',

            // Albums
            'view_any_albums'   => 'Lihat Album',
            'create_albums'     => 'Buat Album',
            'update_albums'     => 'Edit Album',
            'delete_albums'     => 'Hapus Album',

            // Photos
            'view_any_photos'   => 'Lihat Foto',
            'create_photos'     => 'Buat Foto',
            'update_photos'     => 'Edit Foto',
            'delete_photos'     => 'Hapus Foto',

            // Staff
            'view_any_staff'   => 'Lihat Staff',
            'create_staff'     => 'Buat Staff',
            'update_staff'     => 'Edit Staff',
            'delete_staff'     => 'Hapus Staff',

            // Extracurriculars
            'view_any_extracurriculars'   => 'Lihat Ekskul',
            'create_extracurriculars'     => 'Buat Ekskul',
            'update_extracurriculars'     => 'Edit Ekskul',
            'delete_extracurriculars'     => 'Hapus Ekskul',

            // Facilities
            'view_any_facilities'   => 'Lihat Fasilitas',
            'create_facilities'     => 'Buat Fasilitas',
            'update_facilities'     => 'Edit Fasilitas',
            'delete_facilities'     => 'Hapus Fasilitas',

            // Hero Slides
            'view_any_hero_slides'   => 'Lihat Hero Slide',
            'create_hero_slides'     => 'Buat Hero Slide',
            'update_hero_slides'     => 'Edit Hero Slide',
            'delete_hero_slides'     => 'Hapus Hero Slide',

            // Download Categories
            'view_any_download_categories'   => 'Lihat Kategori Unduhan',
            'create_download_categories'     => 'Buat Kategori Unduhan',
            'update_download_categories'     => 'Edit Kategori Unduhan',
            'delete_download_categories'     => 'Hapus Kategori Unduhan',

            // Downloads
            'view_any_downloads'   => 'Lihat Unduhan',
            'create_downloads'     => 'Buat Unduhan',
            'update_downloads'     => 'Edit Unduhan',
            'delete_downloads'     => 'Hapus Unduhan',
        ];
    }

    /**
     * Seed semua permission (Fase 2 + Fase 3).
     * Idempoten — firstOrCreate, aman dijalankan berkali-kali.
     */
    public function run(): void
    {
        $allPermissions = array_merge(
            static::getDefaultPermissions(),
            static::getFase3Permissions(),
        );

        foreach ($allPermissions as $name => $label) {
            // [THECHNOLOGY-CRE] : firstOrCreate + update label — idempoten
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
