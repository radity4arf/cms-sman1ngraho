# Report #26 — DSE (Delia Tse)

**Tanggal:** 2026-08-12  
**Branch:** `feature/fase3-database-crud`  
**Trigger:** CLA dispatch — CGX round 5: 3 issue + audit menyeluruh semua jalur mutasi  

---

## Audit Menyeluruh — Semua Jalur Mutasi 2 Tabel

### hero_slide_config

| # | Operasi | Jalur | Diproteksi? | Mekanisme |
|---|---------|-------|-------------|-----------|
| 1 | INSERT row kedua (id≠1) | Query Builder | ✅ Ya | MySQL: `CHECK(id = 1)`. SQLite: `BEFORE INSERT` trigger singleton |
| 2 | INSERT row (id=1, duplikat) | Query Builder | ✅ Ya | PK constraint (duplicate key) |
| 3 | DELETE row id=1 | Query Builder | ✅ Ya **(fix ini)** | `BEFORE DELETE` trigger — tolak tanpa syarat |
| 4 | UPDATE id → non-1 | Query Builder | ✅ Ya | MySQL: `CHECK(id = 1)`. SQLite: `BEFORE UPDATE` trigger **(fix ini)** |
| 5 | UPDATE default_hero_slide_id → valid slide | Service / Eloquent | ✅ Ya | Trigger target-validity (cek published+aktif). Service validasi ganda |
| 6 | UPDATE default_hero_slide_id → draft slide | Query Builder | ✅ Ya | Trigger target-validity (000014) |
| 7 | UPDATE default_hero_slide_id → inactive slide | Query Builder | ✅ Ya | Trigger target-validity (000014) |
| 8 | UPDATE default_hero_slide_id → non-existent ID | Query Builder | ✅ Ya | FK constraint |
| 9 | UPDATE default_hero_slide_id → NULL | Query Builder | ✅ Ya **(fix ini)** | Trigger null-pointer. Model guard (lapis pertama) |
| 10 | UPDATE default_hero_slide_id → NULL (state awal) | Migration seed | ✅ Ya | NULL → NULL: tidak trigger (IS NOT NULL check) |
| 11 | TRUNCATE TABLE | SQL | ⚠️ **LIMITASI** | MySQL: `TRUNCATE` = DDL, **bypass trigger**. SQLite: tidak ada TRUNCATE (`DELETE FROM` kena trigger). Mitigasi: revoke TRUNCATE privilege dari user aplikasi |

### hero_slides (terkait slide default)

| # | Operasi | Jalur | Diproteksi? | Mekanisme |
|---|---------|-------|-------------|-----------|
| 12 | Hard DELETE slide default | Query Builder | ✅ Ya | MySQL: FK `ON DELETE RESTRICT`. SQLite: `BEFORE DELETE` trigger |
| 13 | Soft DELETE (deleted_at) slide default | Query Builder | ✅ Ya **(fix ini)** | `BEFORE UPDATE` trigger — cek `NEW.deleted_at IS NOT NULL` |
| 14 | RESTORE (deleted_at → NULL) slide default | Query Builder | ✅ Ya | Tidak apply: restore mengembalikan slide, tidak merusak invariant |
| 15 | UPDATE status → draft pada slide default | Query Builder | ✅ Ya | Trigger draft_cfg (000013) |
| 16 | UPDATE is_active → false pada slide default | Query Builder | ✅ Ya | Trigger deactivate_cfg (000013) |
| 17 | FORCE DELETE slide default | Eloquent | ✅ Ya | `forceDelete()` = hard DELETE → kena FK RESTRICT / trigger delete |
| 18 | UPDATE kolom lain (title, caption, dll) pada slide default | Eloquent / QB | ✅ Ya | Tidak perlu blokir — tidak merusak invariant |

### Catatan Limitasi

| Limitasi | Detail | Mitigasi |
|----------|--------|----------|
| **TRUNCATE hero_slide_config** (MySQL) | `TRUNCATE` = DDL, bypass semua trigger (termasuk delete guard dan singleton guard). User dengan privilege TRUNCATE bisa menghapus config row. | **Rekomendasi: revoke TRUNCATE privilege** dari user aplikasi. Atau terima sebagai known limitation (admin/DBA dengan akses TRUNCATE punya akses destruktif ke semua tabel — ini di luar scope aplikasi) |

---

## Fix 3 Issue

### Issue #1 — Config row bisa di-DELETE, current() bikin ulang kosong

| Komponen | Sebelum | Sesudah |
|----------|---------|---------|
| **DB** | Tidak ada proteksi DELETE | `BEFORE DELETE` trigger — blokir DELETE id=1 |
| **Model** | `firstOrCreate(['id' => 1])` — diam-diam bikin row baru | `findOrFail(1)` — **fail LOUD** kalau invariant rusak |

### Issue #2 — Soft-delete slide default tidak dicegah trigger

| Komponen | Sebelum | Sesudah |
|----------|---------|---------|
| **DB** | Trigger existing (draft/deactivate) tidak cek `deleted_at` | `BEFORE UPDATE` trigger: blokir `NEW.deleted_at IS NOT NULL AND OLD.deleted_at IS NULL` untuk slide default |

### Issue #3 — Pointer config bisa di-null-kan

| Komponen | Sebelum | Sesudah |
|----------|---------|---------|
| **DB** | Trigger target-validity hanya cek kelayakan slide target, tidak cek null | `BEFORE UPDATE` trigger: blokir transisi `non-null → null`. Tidak menghalangi state awal (null → null) |

### Plus: SQLite id-update guard

MySQL punya `CHECK(id=1)` yang otomatis blokir `UPDATE id`. SQLite tidak punya CHECK → ditambah `BEFORE UPDATE` trigger untuk mencegah perubahan id.

---

## Diff Summary

| File | Aksi | Deskripsi |
|------|------|-----------|
| `database/migrations/...000015...` | **CRE** | 4 trigger: delete-config, soft-delete-default, null-pointer, SQLite id-update |
| `app/Models/HeroSlideConfig.php` | **MOD** | `current()`: `firstOrCreate` → `findOrFail(1)` |
| `tests/Feature/HeroSlideGuardTest.php` | **MOD** | +5 test: delete config, findOrFail, soft-delete, null-pointer QB, update id |

## Hasil Test

```
Tests: 48 | Passed: 48 | Assertions: 92
```

Test baru (5):

| Test | Hasil | Cakupan |
|------|-------|---------|
| `test_cannot_delete_config_row` | ✅ | Issue #1 — DELETE id=1 → QueryException |
| `test_current_find_or_fail_works_when_row_exists` | ✅ | Issue #1 regresi — findOrFail(1) berhasil |
| `test_query_builder_cannot_soft_delete_default_slide` | ✅ | Issue #2 — soft-delete default → QueryException |
| `test_query_builder_cannot_null_config_pointer_after_init` | ✅ | Issue #3 — null-kan pointer via QB → QueryException |
| `test_cannot_update_config_id` | ✅ | Audit — UPDATE id → QueryException |

---

## Status: SELESAI

- Commit: `39bf8e1`
- Tests: 48/48 passed
- **Siap di-push manual oleh RDA**

### Rekomendasi ke CLA untuk Diskusi RDA
- **TRUNCATE hero_slide_config** (MySQL) adalah satu-satunya celah tersisa yang tidak bisa ditutup di level aplikasi karena TRUNCATE adalah operasi DDL yang by design skip trigger. Rekomendasi: revoke TRUNCATE privilege dari user aplikasi MySQL.
