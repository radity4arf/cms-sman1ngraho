# Report #30 — Fix Skema: CHECK Constraint vs AUTO_INCREMENT Column

**Tanggal:** 2026-08-13
**Oleh:** DSE (Delia Tse) — mode Pro (task Berat: skema database inti)
**Status:** Selesai — test SQLite 51/51 PASS **DAN** `migrate:fresh --seed` sukses di MySQL asli 8.4.3

## Ringkasan Task

`migrate:fresh` gagal di MySQL asli (versi 8.4.3) dengan error:

```
SQLSTATE[HY000]: General error: 3818 Check constraint 'hero_slide_config_singleton'
cannot refer to an auto-increment column.
SQL: ALTER TABLE hero_slide_config ADD CONSTRAINT hero_slide_config_singleton CHECK (id = 1)
```

Ini adalah kejadian ketiga migrasi Fase 3 gagal di MySQL asli setelah lolos test SQLite (dua masalah sebelumnya sudah ditangani di report29).

## Root Cause

**Gap antara level aplikasi vs level database.**

Model `HeroSlideConfig` sudah di-set `$incrementing = false` (level Eloquent/PHP), TAPI migration pembuat tabel `2026_08_12_000010_create_hero_slide_config_table.php` masih memakai `$table->id()` bawaan Laravel. `$table->id()` **SELALU** membuat kolom `id` sebagai `AUTO_INCREMENT` di level database, terlepas dari setting Eloquent.

MySQL 8.0.16+ **melarang** CHECK constraint merujuk kolom `AUTO_INCREMENT` — sehingga `ADD CONSTRAINT hero_slide_config_singleton CHECK (id = 1)` di migration `000013` gagal dengan error 3818.

Bukti konfirmasi di MySQL asli (sebelum fix):
```sql
`id` bigint unsigned NOT NULL AUTO_INCREMENT   -- masih auto-increment
```

## Fix

### `database/migrations/2026_08_12_000010_create_hero_slide_config_table.php` (up)

1. Ganti `$table->id()` → `$table->unsignedBigInteger('id')->primary()` — primary key TANPA auto-increment.

   Sebelum:
   ```php
   $table->id();
   ```
   Sesudah:
   ```php
   $table->unsignedBigInteger('id')->primary();
   ```

2. Data migration insert harus mengisi `id` eksplisit (karena tidak ada lagi auto-increment):

   ```php
   DB::table('hero_slide_config')->insert([
       'id'                    => 1,
       'default_hero_slide_id' => $defaultSlide?->id,
       'created_at'            => now(),
       'updated_at'            => now(),
   ]);
   ```

Perubahan dilakukan di migration `000010` (tempat kolom `id` pertama kali dibuat), **bukan** di `000013` — sesuai urutan migration (000010 create table → 000013 tambah CHECK constraint).

**Rasionalitas desain:** Tabel ini memang didesain singleton (selalu tepat 1 row, `id=1`, di-seed eksplisit di migration). Menghapus auto-increment justru benar secara desain — sekarang DB-level dan app-level (`$incrementing=false` di model) sama-sama sepakat, tidak ada lagi gap.

## Audit Tambahan — Tabel Lain Berpotensi Singleton/Fixed-id

- Satu-satunya model dengan `$incrementing = false` → `HeroSlideConfig` (grep `incrementing` di seluruh `app/`).
- Satu-satunya CHECK constraint yang merujuk kolom `id` → `hero_slide_config_singleton CHECK (id = 1)` (grep `CHECK`/`constraint` di seluruh migrations).
- `->primary()` lain yang dipakai: `cache.key` (string), `sessions.id` (string), `password_reset_tokens.email` (string) — semuanya string primary key bawaan Laravel, **bukan** auto-increment dan tidak punya CHECK constraint. Aman.
- **Tidak ada** FK dari tabel lain yang reference ke `hero_slide_config.id` (grep `on('hero_slide_config')` → 0 hasil). Jadi perubahan tipe kolom `id` (tetap `unsignedBigInteger`) tidak berdampak ke FK manapun.
- **Kesimpulan audit:** tidak ada tabel lain dengan pola singleton/fixed-id + CHECK constraint di project ini. Tidak ada perbaikan tambahan yang diperlukan.

## Test Lokal (SQLite)

`php artisan test` → **51/51 PASS** (99 assertions) — tidak ada regresi.

## Validasi MySQL Asli (WAJIB — bukan opsional kali ini)

`php artisan migrate:fresh --seed` di MySQL 8.4.3 (localhost:3306, database `laravel`, user `root`):

- **Seluruh 31 migration selesai TANPA ERROR** (termasuk `000010` dan `000013` yang sebelumnya bermasalah).
- **Seluruh 4 seeder selesai** (PermissionSeeder, AdminUserSeeder, DownloadCategorySeeder, HeroSlideSeeder).

Konfirmasi struktur tabel setelah fix di MySQL asli:

```sql
CREATE TABLE `hero_slide_config` (
  `id` bigint unsigned NOT NULL,                              -- ← TIDAK lagi AUTO_INCREMENT
  `default_hero_slide_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hero_slide_config_default_hero_slide_id_foreign` (`default_hero_slide_id`),
  CONSTRAINT `hero_slide_config_default_hero_slide_id_foreign` FOREIGN KEY (`default_hero_slide_id`) REFERENCES `hero_slides` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `hero_slide_config_singleton` CHECK ((`id` = 1))  -- ← CHECK constraint BERHASIL dibuat
)
```

Row seeded: `id=1, default_hero_slide_id=1`.

**Verifikasi enforcement singleton aktif** (coba insert row kedua id=2):

```
ERROR 3819 (HY000): Check constraint 'hero_slide_config_singleton' is violated.
```

CHECK constraint sekarang benar-benar berfungsi di level database MySQL — singleton enforced.

## File yang Diubah

| File | Perubahan |
|---|---|
| `database/migrations/2026_08_12_000010_create_hero_slide_config_table.php` | `$table->id()` → `$table->unsignedBigInteger('id')->primary()`; data insert isi `id=1` eksplisit; header `@updated` + tag `[THECHNOLOGY-FIX]` |

## Commit

- Branch: `fix/hero-slide-config-auto-increment`
- Commit: `6015ae9` — `[DSE] fix hero_slide_config id jadi non-auto-increment (unsignedBigInteger+primary) agar CHECK(id=1) valid di MySQL 8.0.16+`

## Catatan untuk CLA / RDA

- Commit selesai, **belum di-push** (push manual oleh RDA, di luar sesi DSE).
- **Observasi minor (di luar scope task ini):** `HeroSlideSeeder` memakai `HeroSlideConfig::firstOrCreate([], [...])` dengan atribut kosong. Di happy path ini aman (migration `000010` + `000013` sudah menjamin row `id=1` selalu ada, jadi `first()` selalu return row yang ada dan tidak pernah masuk ke jalur create). Namun kalau suatu saat row hilang, jalur create akan gagal (id non-incrementing + `id` tidak di-fillable). Tidak diubah di task ini karena di luar scope permintaan CLA — silakan ditindaklanjuti terpisah kalau dianggap perlu.
- Migrasi Fase 3 kini tervalidasi end-to-end di MySQL 8.4.3 asli (bukan hanya SQLite), meliputi: functional unique index (8.0.13+), CHECK constraint (8.0.16+), trigger `SIGNAL`, dan FK RESTRICT.
