# Report #24 — DSE (Delia Tse)

**Tanggal:** 2026-08-12  
**Branch:** `feature/fase3-database-crud`  
**Trigger:** CLA dispatch — CGX verdict round 4: NEEDS_REVISION, 2 CRITICAL  

---

## Task: Proteksi DB-Level — Singleton Enforcement + FK RESTRICT

### Issue #1 [CRITICAL] — hero_slide_config belum benar-benar singleton

**Root cause:** `firstOrCreate([])` race-prone — 2 request bersamaan bisa insert 2 row. Tidak ada constraint DB yang paksa tepat 1 row. `default_hero_slide_id` nullable tanpa jaminan tidak kembali null.

**Fix:**

| Layer | Mekanisme | Driver |
|-------|-----------|--------|
| **DB** | `CHECK(id = 1)` — blokir INSERT baris kedua (id≠1) | MySQL 8.0.16+ |
| **DB** | `BEFORE INSERT` trigger — blokir kalau `COUNT(*) >= 1` | SQLite |
| **Migration** | Seed row id=1 eksplisit — bukan lazy `firstOrCreate` runtime | Both |
| **Model** | `$incrementing = false`; `current()` = `firstOrCreate(['id' => 1])` | Both |
| **Model** | `updating` guard: tolak null-kan `default_hero_slide_id` setelah first init | Both |

### Issue #2 [CRITICAL] — Query Builder bypass delete/draft/deactivate; FK SET NULL fasilitasi state kosong

**Root cause:** Guard delete/draft/deactivate cuma di Eloquent event. Query Builder lewati ini. FK `ON DELETE SET NULL` malah memfasilitasi config kosong (bukan mencegah).

**Fix:**

| Operasi | Mekanisme MySQL | Mekanisme SQLite |
|---------|----------------|------------------|
| **DELETE** | `ON DELETE RESTRICT` (FK constraint) — DB tolak delete, jalur manapun | `BEFORE DELETE` trigger — cek `hero_slide_config.default_hero_slide_id` |
| **DRAFT** | `BEFORE UPDATE` trigger — blokir `status='draft'` jika slide adalah default | `BEFORE UPDATE` trigger (sama) |
| **DEACTIVATE** | `BEFORE UPDATE` trigger — blokir `is_active=0` jika slide adalah default | `BEFORE UPDATE` trigger (sama) |

### Diff Summary

| File | Aksi | Deskripsi |
|------|------|-----------|
| `database/migrations/...000013...` | **CRE** | 1 migration: seed id=1, CHECK/trigger singleton, FK RESTRICT, 2 trigger guard (draft + deactivate) |
| `app/Models/HeroSlideConfig.php` | **MOD** | `$incrementing = false`; `current()` via `firstOrCreate(['id' => 1])`; `updating` guard anti-null |
| `tests/Feature/HeroSlideGuardTest.php` | **MOD** | Hapus test `SET NULL` (obsolete). +6 test baru: singleton block, DB-level delete block, delete after swap, QB draft block, QB deactivate block, config can't be nulled |

### Hasil Test

```
Tests: 40 | Passed: 40 | Assertions: 81
```

Test baru (6):

| Test | Hasil | Verifikasi |
|------|-------|------------|
| `test_config_singleton_blocks_second_insert` | ✅ | INSERT id=2 → `QueryException` (CHECK/trigger) |
| `test_db_level_delete_of_default_slide_is_blocked` | ✅ | DB facade delete → `QueryException` (FK RESTRICT/trigger) |
| `test_can_delete_slide_after_promoting_another_as_default` | ✅ | Setelah swap, slide lama bisa dihapus normal |
| `test_query_builder_cannot_draft_default_slide` | ✅ | QB `update(['status' => 'draft'])` → `QueryException` |
| `test_query_builder_cannot_deactivate_default_slide` | ✅ | QB `update(['is_active' => false])` → `QueryException` |
| `test_config_cannot_be_nulled_after_first_init` | ✅ | Model `update(['default_hero_slide_id' => null])` → `RuntimeException` |

---

## Status: SELESAI

- Commit: `862c69f` — `[DSE] CGX round 4 fix: DB-level singleton CHECK/trigger + FK RESTRICT + QB bypass trigger draft/deactivate`
- Tests: 40/40 passed
- **Siap di-push manual oleh RDA**
