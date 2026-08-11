# DSE Work Report — CGX Fase 3 Review Fix

**Tanggal:** 2026-08-11  
**Branch:** `feature/fase3-database-crud`  
**Status:** SELESAI — Semua 5 issue CGX Fase 3 diperbaiki

---

## Ringkasan Pekerjaan

Perbaikan menyeluruh terhadap 5 issue hasil review CGX (3 CRITICAL + 2 MINOR) pada Fase 3.

---

## Issue #1 [CRITICAL] — HeroSlide `is_default` Guard

### Perubahan:

#### a. `app/Services/HeroSlideService.php` (NEW)
- Service `HeroSlideService::promoteAsDefault($slide)` — satu-satunya jalur resmi untuk swap `is_default`
- Operasi atomik dalam DB transaction + `lockForUpdate()`
- Validasi: slide yang dipromosikan WAJIB published + aktif
- Proteksi: tolak slide draft atau nonaktif untuk jadi default

#### b. Guard `updating()` di `app/Models/HeroSlide.php`
- **TOLAK SEMUA** unset `is_default` (true→false) tanpa melalui mekanisme swap resmi
- Mekanisme swap resmi: hanya `HeroSlideService::promoteAsDefault()` atau internal `saving()` event
- Flag internal `$swappingDefault` + public method `beginSwap()`/`endSwap()` untuk koordinasi
- Bahkan jika ada kandidat pengganti, unset langsung tetap DITOLAK

#### c. Guard `creating()`/`saving()` di `app/Models/HeroSlide.php`
- `creating()`: tolak `is_default=true` dengan status=draft ATAU `is_active=false`
- `saving()`: tolak `is_default=true` (existing) dengan status=draft atau nonaktif — mencakup update kolom lain

#### d. DB-level proteksi — `database/migrations/2026_08_10_040108_...php`
- Tambah SQLite partial unique index: `CREATE UNIQUE INDEX ... WHERE is_default = 1`
- SQLite 3.8.0+ mendukung partial index via WHERE clause
- MySQL tetap menggunakan functional index via CASE expression
- Driver-aware — otomatis pilih syntax yang tepat

#### e. Test coverage — `tests/Feature/HeroSlideGuardTest.php`
- **15 test cases** (sebelumnya 8), semuanya PASS
- **Dihapus:** `test_allow_unset_default_with_replacement` (mengesahkan bug — unset langsung tanpa service)
- **Diganti:** `test_reject_direct_unset_default_even_with_replacement` (unset langsung DITOLAK meski ada kandidat)
- **Ditambah:**
  - `test_promote_as_default_service_swaps_correctly` — swap via service berhasil
  - `test_promote_as_default_rejects_draft_slide` — service tolak draft
  - `test_promote_as_default_rejects_inactive_slide` — service tolak nonaktif
  - `test_reject_create_draft_default` — create draft+default DITOLAK
  - `test_reject_create_inactive_default` — create inactive+default DITOLAK
  - `test_reject_save_existing_default_as_draft` — update ke draft DITOLAK
  - `test_reject_save_existing_default_as_inactive` — update ke nonaktif DITOLAK

**Bukti test:** `php artisan test --filter=HeroSlideGuardTest` → 15/15 PASSED

---

## Issue #2 [CRITICAL] — Validasi Ukuran File 10MB di Level Model

### Perubahan:

#### `app/Providers/AppServiceProvider.php`
- Tambah listener `Media::saving()` di `boot()` method
- Validasi: jika `$media->size > 10 * 1024 * 1024` → throw `\RuntimeException`
- Mencakup **SEMUA** model dengan media (Post, Achievement, AlumniTestimonial, Staff, Extracurricular, Facility, HeroSlide, Photo, Download)
- Mencakup **SEMUA** jalur upload (UI, Tinker, API, CLI, job)

#### `tests/Feature/UploadValidationTest.php`
- **Ditambah 4 test size:**
  - `test_model_rejects_file_larger_than_10mb` — Photo model tolak >10MB via Media::saving
  - `test_download_rejects_file_larger_than_10mb` — Download model tolak >10MB via Media::saving
  - `test_hero_slide_rejects_file_larger_than_10mb` — HeroSlide model tolak >10MB via Media::saving
  - `test_post_model_accepts_valid_file` — Post model terima file dalam batas normal
- Strategi test: upload file kecil valid dulu → modifikasi `size` di Media model → re-save → verifikasi listener menolak
- Ini mensimulasikan skenario bypass form (Tinker/CLI) sesuai permintaan CGX

**Bukti test:** `php artisan test --filter=UploadValidationTest` → 12/12 PASSED

---

## Issue #3 [MINOR] — AdminUserSeeder Hapus Auto-Assignment Permission

### Perubahan:

#### `database/seeders/AdminUserSeeder.php`
- **Dihapus:** blok kode `$allPermissions = Permission::pluck('name')...` dan `$admin->syncPermissions(...)`
- **Dihapus:** import `Spatie\Permission\Models\Permission`
- Seeder sekarang hanya: buat user admin + set flag `is_super_admin` + log password
- Permission hanya didaftarkan (via `RoleAndPermissionSeeder`), assignment manual via UI

---

## Issue #4 [MINOR] — Album/Photo Ordering

### Perubahan:

#### `app/Models/Album.php`
- Relasi `photos()` sekarang: `return $this->hasMany(Photo::class)->ordered()`
- `ordered()` scope dari trait `HasOrdering`: `ORDER BY sort_order ASC, id ASC`
- Mencakup semua pemanggilan `$album->photos` — termasuk `PhotoRelationManager` (Filament) dan query di `scopePublished()`

---

## File Tambahan

#### `app/Models/Post.php`
- Tambah trait `HasFactory` (diperlukan untuk test but Post model belum punya factory)

#### `database/factories/PostFactory.php` (NEW)
- Factory minimal untuk model Post — digunakan di `UploadValidationTest`

---

## Hasil Test Keseluruhan

```
PASS  Tests\Feature\HeroSlideGuardTest
  ✓ test_promote_as_default_service_swaps_correctly
  ✓ test_promote_as_default_rejects_draft_slide
  ✓ test_promote_as_default_rejects_inactive_slide
  ✓ test_atomic_swap_on_create_ensures_single_default
  ✓ test_atomic_swap_on_update_ensures_single_default
  ✓ test_reject_direct_unset_default_even_with_replacement
  ✓ test_reject_unset_default_without_replacement
  ✓ test_reject_create_draft_default
  ✓ test_reject_create_inactive_default
  ✓ test_regression_guard_delete_default_still_works
  ✓ test_regression_guard_draft_default_still_works
  ✓ test_regression_guard_deactivate_default_still_works
  ✓ test_can_delete_non_default_slide
  ✓ test_reject_save_existing_default_as_draft
  ✓ test_reject_save_existing_default_as_inactive

PASS  Tests\Feature\UploadValidationTest
  ✓ test_download_rejects_invalid_text_mime
  ✓ test_download_rejects_gif_image
  ✓ test_download_accepts_valid_jpeg
  ✓ test_photo_rejects_invalid_text_mime
  ✓ test_photo_rejects_gif_image
  ✓ test_photo_accepts_valid_jpeg
  ✓ test_photo_accepts_valid_png
  ✓ test_model_rejects_file_larger_than_10mb
  ✓ test_download_rejects_file_larger_than_10mb
  ✓ test_hero_slide_rejects_file_larger_than_10mb
  ✓ test_photo_model_accepts_valid_file
  ✓ test_post_model_accepts_valid_file

Tests: 35 passed (80 assertions)
```

---

## Diff Summary

| File | Status | Deskripsi |
|------|--------|-----------|
| `app/Services/HeroSlideService.php` | NEW | Service `promoteAsDefault()` — atomic swap is_default |
| `app/Models/HeroSlide.php` | MOD | Strict guards: creating/saving/updating — service-only swap, reject draft/inactive default |
| `app/Providers/AppServiceProvider.php` | MOD | `Media::saving` listener — 10MB size validation model-level |
| `database/migrations/...hero_slides...php` | MOD | SQLite partial unique index support (WHERE is_default=1) |
| `database/seeders/AdminUserSeeder.php` | MOD | Hapus auto-assignment permission ke admin |
| `app/Models/Album.php` | MOD | Relasi `photos()` pakai `->ordered()` scope |
| `app/Models/Post.php` | MOD | Tambah trait `HasFactory` |
| `database/factories/PostFactory.php` | NEW | Factory untuk model Post |
| `tests/Feature/HeroSlideGuardTest.php` | MOD | 15 test (↑ dari 8): fix bug-validating test, tambah service/draft/inactive tests |
| `tests/Feature/UploadValidationTest.php` | MOD | Tambah 4 test model-level size validation via Media::saving |
