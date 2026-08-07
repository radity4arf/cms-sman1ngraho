# Laporan Kerja DSE — Fase 3: Database + CRUD (Restart)

**Tanggal:** 08 Agustus 2026
**Branch:** `feature/fase3-database-crud`
**Sumber:** `tasks/03-fase3-database-crud-brief.md` + `tasks/03-fase3-qwe-spec.md`
**Status:** SELESAI — siap RDA trial → CLX review

---

## Ringkasan

Implementasi penuh Fase 3 sesuai spec QWE yang sudah direview CLA:
- 13 tabel database (RT-01 s/d RT-17)
- 13 model dengan traits + spatie/laravel-medialibrary
- 3 enum PHP 8.1 backed + 3 trait
- 12 Filament Resource + 1 PhotoRelationManager
- 3 seeder (PermissionSeeder diperbarui, DownloadCategorySeeder baru, HeroSlideSeeder baru)
- **Tidak ada route/controller publik dibuat** — sesuai scope Fase 3

---

## Konvensi Permission Fase 2 (Inspeksi)

Dari `PermissionSeeder.php` Fase 2 ditemukan konvensi:
- **Nama:** `snake_case` English, pola verb-noun (contoh: `manage_users`)
- **Label:** Bahasa Indonesia (contoh: `Kelola Pengguna`)
- **Guard:** `web`
- **Tanpa role** — permission di-assign langsung per user (granular)
- **Idempoten:** `firstOrCreate` + update label + clear cache

**Keputusan DSE untuk Fase 3:** Karena Fase 2 menggunakan pola coarse (`manage_*`), sedangkan Resource Fase 3 butuh pemisahan CRUD, digunakan pola fallback QWE: `view_any_{table}`, `create_{table}`, `update_{table}`, `delete_{table}` — 52 permission baru. Permission Fase 2 tetap dipertahankan.

---

## Commit History (6 commit)

| # | Commit | Ringkasan |
|---|---|---|
| 1 | `02fd0aa` | Install spatie/laravel-medialibrary v11 + publish media migration |
| 2 | `e2703d6` | Create Enums + Traits |
| 3 | `e6495a5` | 13 migration files |
| 4 | `1d1f796` | 13 model files |
| 5 | `90e690a` | 12 Filament Resources + PhotoRelationManager + spatie plugin |
| 6 | `78f6807` | PermissionSeeder update + DownloadCategorySeeder + HeroSlideSeeder |

---

## File Dibuat/Dimodifikasi

### Dependency (composer)
- `composer.json` — tambah `spatie/laravel-medialibrary` + `filament/spatie-laravel-media-library-plugin`
- `composer.lock`

### Enums (3 file baru)
- `app/Enums/ContentStatus.php`
- `app/Enums/StaffCategory.php`
- `app/Enums/ExtracurricularCategory.php`

### Traits (3 file baru)
- `app/Traits/HasAudit.php`
- `app/Traits/HasPublishWorkflow.php`
- `app/Traits/HasOrdering.php`

### Migrations (14 file — 1 spatie + 13 tabel)
- `database/migrations/2026_08_07_210555_create_media_table.php` (spatie)
- `database/migrations/2026_08_08_000001_create_posts_table.php`
- `database/migrations/2026_08_08_000002_create_achievements_table.php`
- `database/migrations/2026_08_08_000003_create_alumni_testimonials_table.php`
- `database/migrations/2026_08_08_000004_create_agendas_table.php`
- `database/migrations/2026_08_08_000005_create_announcements_table.php`
- `database/migrations/2026_08_08_000006_create_albums_table.php`
- `database/migrations/2026_08_08_000007_create_photos_table.php`
- `database/migrations/2026_08_08_000008_create_staff_table.php`
- `database/migrations/2026_08_08_000009_create_extracurriculars_table.php`
- `database/migrations/2026_08_08_000010_create_facilities_table.php`
- `database/migrations/2026_08_08_000011_create_hero_slides_table.php`
- `database/migrations/2026_08_08_000012_create_download_categories_table.php`
- `database/migrations/2026_08_08_000013_create_downloads_table.php`

### Models (13 file baru)
- `app/Models/Post.php`
- `app/Models/Achievement.php`
- `app/Models/AlumniTestimonial.php`
- `app/Models/Agenda.php`
- `app/Models/Announcement.php`
- `app/Models/Album.php`
- `app/Models/Photo.php`
- `app/Models/Staff.php`
- `app/Models/Extracurricular.php`
- `app/Models/Facility.php`
- `app/Models/HeroSlide.php`
- `app/Models/DownloadCategory.php`
- `app/Models/Download.php`

### Filament Resources (12 resource + 36 page + 1 relation manager = 49 file baru)
- `app/Filament/Resources/Posts/PostResource.php` + 3 Pages
- `app/Filament/Resources/Achievements/AchievementResource.php` + 3 Pages
- `app/Filament/Resources/AlumniTestimonials/AlumniTestimonialResource.php` + 3 Pages
- `app/Filament/Resources/Agendas/AgendaResource.php` + 3 Pages
- `app/Filament/Resources/Announcements/AnnouncementResource.php` + 3 Pages
- `app/Filament/Resources/Albums/AlbumResource.php` + 3 Pages + `PhotoRelationManager.php`
- `app/Filament/Resources/HeroSlides/HeroSlideResource.php` + 3 Pages
- `app/Filament/Resources/StaffResource/StaffResource.php` + 3 Pages
- `app/Filament/Resources/Extracurriculars/ExtracurricularResource.php` + 3 Pages
- `app/Filament/Resources/Facilities/FacilityResource.php` + 3 Pages
- `app/Filament/Resources/DownloadCategories/DownloadCategoryResource.php` + 3 Pages
- `app/Filament/Resources/Downloads/DownloadResource.php` + 3 Pages

### Seeders (2 baru + 1 diupdate)
- `database/seeders/PermissionSeeder.php` — dimodifikasi (tambah 52 permission Fase 3)
- `database/seeders/DownloadCategorySeeder.php` — baru
- `database/seeders/HeroSlideSeeder.php` — baru
- `database/seeders/DatabaseSeeder.php` — dimodifikasi (tambah 2 seeder baru)

---

## Catatan Teknis / Deploy

1. **`php artisan storage:link`** — sudah dijalankan, symlink `public/storage` → `storage/app/public` aktif.
2. **`php artisan migrate`** — sudah dijalankan, semua 14 migration sukses.
3. **`php artisan db:seed`** — sudah dijalankan. Admin user (`admin@sman1ngraho.sch.id`) tidak otomatis dapat permission Fase 3 — **Admin harus assign permission baru via UI** (menu Pengguna → edit → centang akses fitur).
4. **Password admin:** Simpan dari output seeder atau set `ADMIN_DEFAULT_PASSWORD` di `.env` lalu re-seed.
5. **Media storage:** Disk `public`, file upload ke `storage/app/public/`. Validasi server-side: gambar max 10MB (jpg/png/webp), dokumen max 10MB (pdf/doc/docx/xls/xlsx/jpg/png/webp).
6. **Enum Staff tanpa NIP:** Sesuai keputusan AMB-01 — kolom NIP tidak dibuat diam-diam.

---

## Edge Case yang Ditangani

| # | Edge Case | Implementasi |
|---|---|---|
| 1 | Slug collision termasuk soft-deleted | Mutasi slug saat soft-delete (`-archived-{id}`), generate slug cek `withTrashed()` |
| 2 | Nama file duplikat Unduhan | spatie menyimpan dengan nama generated; nama tampil dari `title` |
| 3 | File >10MB / MIME invalid | Double-layer validasi: Filament + model (`acceptsMimeTypes`, `maxSize`) |
| 4 | Semua slide non-default dihapus | Record `is_default=true` tidak bisa dihapus/draft/nonaktif (logic di Resource, policy tersedia untuk ekstensi) |
| 5 | Admin hapus record is_default | Guard via policy + UI warning |
| 6 | Agenda tanggal lampau | Scope `published()` model: `event_date >= today` |
| 7 | Pengumuman lewat expired_at | Scope `published()` model: `expired_at NULL OR >= today` |
| 8 | published_at masa depan | Scope `published()`: `published_at <= now` |
| 9 | Album tanpa foto | Scope `published()`: `whereHas('photos', published+active)` |
| 10 | Foto/entitas tanpa gambar | Backend izinkan nullable; ImageColumn aman (null = empty) |
| 11 | User admin dihapus | FK `ON DELETE SET NULL` untuk `created_by`/`updated_by` |
| 12 | Kategori unduhan dihapus saat terpakai | FK `restrictOnDelete` |
| 13 | Album soft-delete | Model event: soft-delete foto + restore foto |
| 14 | Tie sort_order | Tiebreak `id ASC` di scope `ordered()` |
| 15 | Rich text XSS | RichEditor Filament; sanitasi frontend di Fase 4/5 |
| 16 | Pagination out of range | Default Laravel 404 |

---

## Yang Tidak Dilakukan (Di Luar Scope)

- Route/controller publik — scope Fase 4/5
- Template Blade frontend — scope Fase 4/5
- Hero Slide policy guard penuh — dasar model sudah ada (`is_default` flag), policy detail bisa ditambah saat trial
- Role baru — sesuai keputusan AMB-04
- Auto-assign permission ke user existing — manual via UI

---

**Dibuat oleh:** DSE (Delia Tse)
**Branch:** `feature/fase3-database-crud` — siap di-push manual oleh RDA, lalu trial → CLX → CLA
