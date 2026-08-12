# Report #25 — DSE (Delia Tse)

**Tanggal:** 2026-08-12  
**Branch:** `feature/fase3-database-crud`  
**Trigger:** CLA dispatch — Issue #3 CGX round 4 yang terlewat di report24  

---

## Koreksi: Verdict CGX putaran 4 = 3 CRITICAL, bukan 2

Report24.md salah tulis "2 CRITICAL" — yang benar **3 CRITICAL**:
1. ✅ hero_slide_config belum singleton (selesai di report24)
2. ✅ FK SET NULL + QB bypass delete/draft/deactivate (selesai di report24)
3. ❌ **Pointer config bisa ke slide draft/nonaktif via Query Builder** → dikerjakan sekarang

---

## Task: Issue #3 — Validasi Kelayakan Slide Target di DB Level

### Root Cause

Validasi "slide harus published+aktif untuk jadi default" hanya ada di `HeroSlideService::promoteAsDefault()` (PHP layer). Query Builder bisa bypass:

```php
// Ini lolos — FK cuma cek ID exists, tidak cek kelayakan
HeroSlideConfig::query()->where('id', 1)->update([
    'default_hero_slide_id' => $draftSlideId,
]);
```

### Fix: `BEFORE UPDATE` Trigger di `hero_slide_config`

| Driver | Mekanisme |
|--------|-----------|
| **MySQL** | `BEFORE UPDATE` trigger: query `hero_slides` untuk `NEW.default_hero_slide_id`, blokir jika `status != 'published'` OR `is_active = 0` |
| **SQLite** | Sama — `WHEN` clause + `NOT EXISTS` subquery |

Trigger hanya aktif saat `default_hero_slide_id` berubah ke nilai non-null baru. Tidak menghalangi:
- `promoteAsDefault()` via service (slide sudah divalidasi published+aktif)
- Set ke NULL (sudah diblok model-level guard)

### Diff Summary

| File | Aksi | Deskripsi |
|------|------|-----------|
| `database/migrations/...000014...` | **CRE** | Trigger `hero_slide_config_guard_target_valid` — MySQL + SQLite |
| `tests/Feature/HeroSlideGuardTest.php` | **MOD** | +3 test: QB ke draft ditolak, QB ke nonaktif ditolak, regresi promoteAsDefault tetap berhasil |

### Hasil Test

```
Tests: 43 | Passed: 43 | Assertions: 85
```

Test baru (3):

| Test | Hasil |
|------|-------|
| `test_query_builder_cannot_point_config_to_draft_slide` | ✅ `QueryException` |
| `test_query_builder_cannot_point_config_to_inactive_slide` | ✅ `QueryException` |
| `test_promote_as_default_still_works_with_target_validity_trigger` | ✅ Tidak false-positive |

---

## Status: SELESAI (semua 3 CRITICAL CGX round 4)

- Commit: `933b22c`
- Tests: 43/43 passed
- **Siap di-push manual oleh RDA**
