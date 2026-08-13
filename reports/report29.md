# Report #29 — Fix Syntax MySQL: DROP INDEX / DROP CONSTRAINT IF EXISTS Tidak Valid

**Tanggal:** 2026-08-13
**Oleh:** DSE (Delia Tse) — mode Flash (task Ringan: syntax fix, bukan perubahan logic/arsitektur)
**Status:** Selesai — test SQLite 51/51 PASS

## Ringkasan Task

`migrate:fresh` pertama kali dijalankan ke MySQL asli (sebelumnya semua testing hanya SQLite) gagal dengan:

```
SQLSTATE[42000]: Syntax error ... near 'IF EXISTS hero_slides_is_default_true_unique ON hero_slides'
SQL: DROP INDEX IF EXISTS hero_slides_is_default_true_unique ON hero_slides
```

## Root Cause

MySQL **tidak mendukung** syntax `DROP INDEX IF EXISTS` — berbeda dengan SQLite yang mendukungnya. MySQL hanya menerima `DROP INDEX index_name ON table_name` (tanpa `IF EXISTS`). Baris yang gagal berada di migration `2026_08_12_000011_drop_legacy_default_guards.php` (branch MySQL, `up()`).

Audit menyeluruh menemukan **satu masalah kedua** yang sama-sama syntax tidak valid di MySQL: `DROP CONSTRAINT IF EXISTS` di `2026_08_12_000013_enforce_hero_slide_config_singleton.php` (branch MySQL, `down()`) — MySQL memakai `DROP CHECK`, dan tidak punya varian `IF EXISTS`. Bug ini baru muncul saat `migrate:rollback` (bukan `migrate:fresh`), tapi tetap diperbaiki agar tidak menjadi bom waktu.

## Migration yang Diperbaiki

### 1. `database/migrations/2026_08_12_000011_drop_legacy_default_guards.php` (up, branch MySQL)

Sebelum:
```php
DB::statement('DROP INDEX IF EXISTS hero_slides_is_default_true_unique ON hero_slides');
```

Sesudah — conditional check via `information_schema.statistics` sebelum drop:
```php
$indexExists = DB::select("
    SELECT COUNT(*) AS cnt FROM information_schema.statistics
    WHERE table_schema = DATABASE()
    AND table_name = 'hero_slides'
    AND index_name = 'hero_slides_is_default_true_unique'
");

if ($indexExists[0]->cnt > 0) {
    DB::statement('DROP INDEX hero_slides_is_default_true_unique ON hero_slides');
}
```

Branch SQLite (`DROP INDEX IF EXISTS hero_slides_is_default_true_unique` tanpa `ON table`) **tidak diubah** — valid di SQLite. Tag `[THECHNOLOGY-FIX]` ditambahkan.

### 2. `database/migrations/2026_08_12_000013_enforce_hero_slide_config_singleton.php` (down, branch MySQL)

Sebelum:
```php
DB::statement('ALTER TABLE hero_slide_config DROP CONSTRAINT IF EXISTS hero_slide_config_singleton');
```

Sesudah — conditional check via `information_schema.check_constraints`, lalu `DROP CHECK` (syntax MySQL 8.0.16+):
```php
$checkExists = DB::select("
    SELECT COUNT(*) AS cnt FROM information_schema.check_constraints
    WHERE constraint_schema = DATABASE()
    AND table_name = 'hero_slide_config'
    AND constraint_name = 'hero_slide_config_singleton'
");

if ($checkExists[0]->cnt > 0) {
    DB::statement('ALTER TABLE hero_slide_config DROP CHECK hero_slide_config_singleton');
}
```

Tag `[THECHNOLOGY-FIX]` ditambahkan.

## Audit Menyeluruh — Semua Migration Fase 3 (status per file)

| Migration | Raw DDL? | Driver-aware? | Status |
|---|---|---|---|
| `0001_01_01_000000..000002` (users/cache/jobs) | Schema builder saja | n/a | OK — driver-agnostic |
| `2026_08_04_032257_create_permission_tables` | Schema builder saja (spatie) | n/a | OK |
| `2026_08_04_032357_add_label_to_permissions_table` | Schema builder saja | n/a | OK |
| `2026_08_04_080515_add_is_super_admin_to_users_table` | Schema builder saja | n/a | OK |
| `2026_08_07_210555_create_media_table` | Schema builder saja | n/a | OK |
| `2026_08_08_000001` s/d `000013` (posts, achievements, alumni_testimonials, agendas, announcements, albums, photos, staff, extracurriculars, facilities, hero_slides, download_categories, downloads) | Schema builder saja | n/a | OK |
| `2026_08_10_040108_add_is_default_unique_index_to_hero_slides_table` | Raw `CREATE UNIQUE INDEX`, branch per driver sudah benar | Ya | OK — MySQL: functional index `((CASE WHEN is_default = 1 THEN 1 END))` (8.0.13+); SQLite: partial index `WHERE is_default = 1` |
| `2026_08_12_000001_add_hero_slides_guard_triggers` | Raw `CREATE/DROP TRIGGER`, branch per driver benar | Ya | OK — `SIGNAL SQLSTATE` hanya di branch MySQL; `RAISE(ABORT)` hanya di branch SQLite; `DROP TRIGGER IF EXISTS` valid di kedua driver |
| `2026_08_12_000002_add_hero_slides_unset_guard_trigger` | Raw trigger + `Schema::create` flag table (SQLite only) | Ya | OK — session variable MySQL vs flag table SQLite sudah dibedakan benar |
| `2026_08_12_000010_create_hero_slide_config_table` | Schema builder saja | n/a | OK |
| `2026_08_12_000011_drop_legacy_default_guards` | Raw `DROP TRIGGER`/`DROP INDEX`, branch per driver | Ya | **FIXED** — `DROP INDEX IF EXISTS` MySQL → conditional info_schema check |
| `2026_08_12_000012_drop_is_default_column_from_hero_slides` | Schema builder (`dropColumn`) | n/a | OK — Laravel handle rebuild/ALTER per driver |
| `2026_08_12_000013_enforce_hero_slide_config_singleton` | Raw `ALTER TABLE ... CHECK`, `DROP TRIGGER`, branch per driver | Ya | **FIXED** — `DROP CONSTRAINT IF EXISTS` MySQL (down) → conditional `DROP CHECK` |
| `2026_08_12_000014_add_config_target_validity_trigger` | Raw `CREATE/DROP TRIGGER`, branch per driver | Ya | OK |
| `2026_08_12_000015_close_remaining_config_gaps` | Raw `CREATE/DROP TRIGGER`, branch per driver | Ya | OK — catatan: `down()` menjalankan `DROP TRIGGER IF EXISTS hero_slide_config_guard_id_update` (trigger SQLite-only) juga di MySQL; syntax-nya valid (no-op + warning saja), tidak diubah agar diff minimal |
| `2026_08_12_000016_fix_target_validity_deleted_at_check` | Raw `CREATE/DROP TRIGGER`, branch per driver | Ya | OK |

**Catatan audit tambahan:**
- `DROP TRIGGER IF EXISTS` → valid di MySQL dan SQLite, semua pemakaian aman.
- `CREATE UNIQUE INDEX ... ((CASE ...))` (functional index) → butuh **MySQL 8.0.13+**; `CHECK` constraint (`ADD CONSTRAINT ... CHECK` / `DROP CHECK`) → butuh **MySQL 8.0.16+**. Version requirement ini sudah tertera di header migration 000013 dan 2026_08_10, dipastikan konsisten.
- Tidak ada pemakaian syntax SQLite-only lain (mis. `WITHOUT ROWID`, `PRAGMA`, `AUTOINCREMENT` eksplisit, `ALTER TABLE ... RENAME COLUMN` manual) di luar cabang yang sudah driver-aware.
- Grep menyeluruh `DROP INDEX IF EXISTS` / `DROP CONSTRAINT` / `DROP INDEX` di seluruh project (bukan cuma migrations): hanya 3 baris, semuanya di 2 file yang sudah diperbaiki (L42 000011 + L53 000011 branch SQLite yang sah + L161 000013).

## Test Lokal (SQLite)

`php artisan test` → **51/51 PASS** (99 assertions) — sesuai target sebelum fix, tidak ada regresi.

## File yang Diubah

| File | Perubahan |
|---|---|
| `database/migrations/2026_08_12_000011_drop_legacy_default_guards.php` | Branch MySQL: `DROP INDEX IF EXISTS` → conditional check info_schema + `DROP INDEX` |
| `database/migrations/2026_08_12_000013_enforce_hero_slide_config_singleton.php` | Branch MySQL (down): `DROP CONSTRAINT IF EXISTS` → conditional check info_schema + `DROP CHECK` |

## Catatan untuk CLA / RDA

- Commit selesai, **belum di-push** (push manual oleh RDA, di luar sesi DSE).
- Langkah selanjutnya (dikoordinasikan CLA): RDA jalankan `migrate:fresh` ke MySQL — disarankan pastikan versi MySQL ≥ 8.0.16 (karena CHECK constraint di 000013 dan functional index di migration 2026_08_10 butuh 8.0.13+/8.0.16+).
