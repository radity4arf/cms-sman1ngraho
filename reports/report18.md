# Laporan Kerja DSE — Revisi CGX (Issue #1, #2, #3)

- Tanggal: 2026-08-10
- Engineer: DSE (Delia Tse)
- Branch: `feature/fase3-database-crud`
- Sumber: Review CGX `report17.md` (STATUS: NEEDS_REVISION) — 3 dari 6 issue CRITICAL
- Scope: Issue #1, #2, #3 (Policy, Race Condition, PhotoRelationManager CRUD)
- Issue #4 (testing), #5 (file header), #6 (empty-state) → menyusul di dispatch terpisah

---

## Poin 1 — Policy Permission Granular (14 Policy Files)

### Konvensi Permission Fase 2/3 yang Ditemukan

Inspeksi `database/seeders/PermissionSeeder.php`:

| Aspek | Konvensi |
|---|---|
| **Format nama permission** | `snake_case` English: `{action}_{table}` |
| **Pola** | `view_any_{table}`, `create_{table}`, `update_{table}`, `delete_{table}` |
| **User (Fase 2 legacy)** | `manage_users` (tidak dipecah; dipakai untuk semua aksi) |
| **Label** | Bahasa Indonesia (untuk UI, bukan name) |
| **Guard** | `web` |
| **Assignment** | Langsung per user via `syncPermissions` — tidak ada role |
| **Super-admin bypass** | Flag `is_super_admin` + `Gate::before` di `AppServiceProvider` |

### File Dibuat (14 Policy)

| # | File | Permission Mapping |
|---|---|---|
| 1 | `app/Policies/AchievementPolicy.php` | `view_any/create/update/delete_achievements` |
| 2 | `app/Policies/AgendaPolicy.php` | `view_any/create/update/delete_agendas` |
| 3 | `app/Policies/AlbumPolicy.php` | `view_any/create/update/delete_albums` |
| 4 | `app/Policies/AlumniTestimonialPolicy.php` | `view_any/create/update/delete_alumni_testimonials` |
| 5 | `app/Policies/AnnouncementPolicy.php` | `view_any/create/update/delete_announcements` |
| 6 | `app/Policies/DownloadPolicy.php` | `view_any/create/update/delete_downloads` |
| 7 | `app/Policies/DownloadCategoryPolicy.php` | `view_any/create/update/delete_download_categories` |
| 8 | `app/Policies/ExtracurricularPolicy.php` | `view_any/create/update/delete_extracurriculars` |
| 9 | `app/Policies/FacilityPolicy.php` | `view_any/create/update/delete_facilities` |
| 10 | `app/Policies/HeroSlidePolicy.php` | `view_any/create/update/delete_hero_slides` + guard is_default |
| 11 | `app/Policies/PhotoPolicy.php` | `view_any/create/update/delete_photos` |
| 12 | `app/Policies/PostPolicy.php` | `view_any/create/update/delete_posts` |
| 13 | `app/Policies/StaffPolicy.php` | `view_any/create/update/delete_staff` |
| 14 | `app/Policies/UserPolicy.php` | `manage_users` (Fase 2 legacy) |

### Catatan Teknis

- **Registrasi**: Laravel 13 auto-discovery — policy di `app/Policies/` dengan nama `{Model}Policy` otomatis terdaftar. Tidak perlu `AuthServiceProvider` baru.
- **Gate::before**: Tetap di `AppServiceProvider` — super-admin tetap bypass semua pengecekan.
- **ToggleColumn**: Dengan Policy terdaftar, Filament otomatis memanggil `Policy::update()` saat ToggleColumn di-klik — tidak lagi bypass.
- **canAccess()**: Method existing di Resource tetap berfungsi via `Gate::allows()` (jalur Spatie permission, terpisah dari Policy).

### Commit

```
916f5a7 [DSE] POIN 1 — Buat 14 Model Policy (13 entitas Fase 3 + User Fase 2) untuk permission granular CRUD
```

---

## Poin 2 — Guard is_default HeroSlide (Race Condition)

### File Dimodifikasi/Dibuat

| File | Aksi |
|---|---|
| `app/Models/HeroSlide.php` | MOD — tambah transaction + lockForUpdate + reject unset |
| `database/migrations/2026_08_10_040108_*.php` | CRE — functional unique index |

### Detail Perubahan

**A. Database Level — Functional Unique Index**
```sql
CREATE UNIQUE INDEX hero_slides_is_default_true_unique
ON hero_slides ((CASE WHEN is_default = 1 THEN 1 END));
```
- MySQL 8.0.13+ — hanya enforce unique pada `is_default=true`
- `CASE WHEN` mengembalikan `NULL` untuk `is_default=false` → tidak dihitung duplicate
- Safety net ultimate untuk race condition yang lolos dari lock aplikasi

**B. Model Level — Transaction + Row Lock**
- **`saving` event**: Saat `is_default` diset true (baru/berubah), `lockForUpdate()` semua row `is_default=true`, unset existing defaults secara atomik.
- **`updating` event** (tambahan): Tolak update `is_default` true→false tanpa kandidat pengganti (published + active). Dilewati saat internal swap (flag `$swappingDefault`).
- Guard existing (delete/draft/nonaktif) **tetap aktif berdampingan** — tidak diganti.

**C. Edge Case yang Ditangani**
- Dua request concurrent buat slide default → diserialisasi oleh `lockForUpdate` + unique index.
- Unset default manual tanpa pengganti → ditolak dengan `RuntimeException` + pesan jelas.
- Internal swap (otomatis) → flag `$swappingDefault` mencegah false-positive rejection.

### Commit

```
cd8111d [DSE] POIN 2 — Guard is_default HeroSlide: atomic swap via DB transaction + lockForUpdate + reject unset tanpa kandidat
```

---

## Poin 3 — PhotoRelationManager CRUD

### File Dimodifikasi

| File | Aksi |
|---|---|
| `app/Filament/Resources/Albums/PhotoRelationManager.php` | MOD — tambah CreateAction + Edit/Delete actions |

### Detail Perubahan

- **`getHeaderActions()`**: Tambah `CreateAction::make()` → tombol "Tambah Foto" di header tabel foto.
- **`table()`**: Tambah `recordActions` → `EditAction` + `DeleteAction`.
- **Otorisasi**: Semua action otomatis ter-gate via `PhotoPolicy` (dibuat di Poin 1):
  - Create → `PhotoPolicy::create()` → `create_photos`
  - Edit → `PhotoPolicy::update()` → `update_photos`
  - Delete → `PhotoPolicy::delete()` → `delete_photos`
  - ToggleColumn `is_active` → `PhotoPolicy::update()`
- **Validasi RT-11**: Upload gambar tetap via `SpatieMediaLibraryFileUpload` dengan MIME + max 10MB — tidak diubah.

### Commit

```
99e7106 [DSE] POIN 3 — PhotoRelationManager: tambah header CreateAction + record Edit/Delete actions, otorisasi via PhotoPolicy
```

---

## Status Akhir

- **3 issue CRITICAL (#1, #2, #3)**: SELESAI — 3 commit terpisah, siap trial RDA.
- **3 issue tersisa (#4, #5, #6)**: BELUM — menunggu dispatch terpisah.
- **Push remote**: TIDAK dilakukan (sesuai aturan DSE) — menunggu RDA push manual.
- **Testing**: TIDAK dilakukan (Issue #4 di luar scope task ini).
- **File header/tag**: TIDAK dikerjakan (Issue #5 di luar scope).
- **Empty-state**: TIDAK dikerjakan (Issue #6 di luar scope).
