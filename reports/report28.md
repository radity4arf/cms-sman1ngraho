# Report #28 — DSE (Delia Tse)

**Tanggal:** 2026-08-12  
**Branch:** `feature/fase3-database-crud`  
**Trigger:** CLA dispatch — lengkapi 3 test yang terlewat di report27.md (CGX round 6)

---

## Ringkasan

Report27.md fix Issue CGX round 6 (deleted_at check) sudah benar dari sisi kode, tapi tidak ada test tambahan. Brief ini menambahkan 3 test ke `tests/Feature/HeroSlideGuardTest.php`.

---

## Test Baru (3)

### 1. `test_promote_as_default_rejects_soft_deleted_slide`
- Soft-delete sebuah slide (published+aktif sebelumnya)
- Panggil `HeroSlideService::promoteAsDefault()` ke slide itu
- Assert: `RuntimeException` dilempar dengan message mengandung "dihapus"

### 2. `test_query_builder_cannot_point_config_to_soft_deleted_slide`
- Soft-delete sebuah slide via Query Builder (bypass Eloquent guard)
- Query Builder langsung: `HeroSlideConfig::query()->where('id', 1)->update(...)` ke slide yang di-trash
- Assert: `QueryException` — trigger DB `hero_slide_config_guard_target_valid` menolak karena `deleted_at IS NOT NULL`

### 3. `test_promote_as_default_still_works_for_normal_slide` (Regresi)
- Slide published+aktif+tidak di-trash (`assertFalse($slide->trashed())`)
- Panggil `HeroSlideService::promoteAsDefault()` → harus tetap berhasil
- Assert: `defaultSlideId()` = slide->id, `isDefault()` = true
- Memastikan tidak ada false-positive dari guard `trashed()` baru

---

## Hasil Test

```
Tests: 51 | Passed: 51 | Assertions: 99
```

| Kategori | Jumlah |
|----------|--------|
| Test existing (report26) | 48 |
| Test baru (CGX round 6) | 3 |
| **Total** | **51** |

Semua 51 test lolos tanpa regresi.

---

## Diff Summary

| File | Aksi | Deskripsi |
|------|------|-----------|
| `tests/Feature/HeroSlideGuardTest.php` | **MOD** | +3 test: soft-deleted reject service, soft-deleted reject QB, regresi normal slide |

---

## Status: SELESAI

- Commit akan menyusul setelah report ini
- Branch: `feature/fase3-database-crud`
- **Siap di-push manual oleh RDA**
