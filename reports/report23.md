# Report #23 — DSE (Delia Tse)

**Tanggal:** 2026-08-12  
**Branch:** `feature/fase3-database-crud`  
**Trigger:** CLA dispatch — Restrukturisasi Arsitektur HeroSlide Default  

---

## Task: Restrukturisasi — HeroSlideConfig sebagai Single Source of Truth

### Root Cause

3 ronde CRITICAL beruntun dari CGX untuk masalah yang sama — integritas `is_default`:
1. Bisa 0 default (tidak ada `is_default=true`)
2. Bisa >1 default (race condition)
3. Token/flag bypass-able (Query Builder bypass, Tinker/job panggil `beginSwap()`)

Setiap fix adalah **patch** di atas patch sebelumnya, bukan solusi struktural:
- Token-guard `beginSwap()`/`endSwap()` → masih public, bisa dipanggil dengan token
- Session variable `@hero_swapping_default` → connection-scoped, bisa leak di connection pool
- DB trigger 4 buah → tidak bisa bedakan "unset dalam swap sah" vs "unset ilegal" tanpa penanda eksternal
- Flag table `hero_slide_swap_flags` → cooperative, bukan enforced

**RDA memutuskan: RESTRUKTURISASI, bukan patch lagi.**

### Desain Baru

| Dulu (boolean is_default) | Sekarang (HeroSlideConfig) |
|---|---|
| Kolom `is_default` di `hero_slides` — bisa 0 atau >1 | Tabel `hero_slide_config` — **tepat 1 row**, FK `default_hero_slide_id` |
| Swap = lock banyak row + unset + set baru + flag + trigger | Swap = **1 UPDATE** di config — **atomic native DB** |
| 3 file trigger, session variable, flag table, token | **Semua dihapus** — tidak diperlukan |
| ~250 baris kode guard di model | ~40 baris guard bisnis murni |

### Detail Perubahan (per poin)

| Poin | File | Aksi | Deskripsi |
|------|------|------|-----------|
| 1 | `database/migrations/...000010...` | **CRE** | Tabel `hero_slide_config`: id, default_hero_slide_id (FK→hero_slides, ON DELETE SET NULL), timestamps. Data migration: cari `is_default=true` existing → insert ke config |
| 2 | `database/migrations/...000012...` | **CRE** | Drop kolom `is_default` dari `hero_slides` |
| 3 | — | (di 000010) | Data migration otomatis: INSERT ke config dari `hero_slides WHERE is_default=true` |
| 4 | `database/migrations/...000011...` | **CRE** | Drop 4 trigger + tabel `hero_slide_swap_flags` + partial unique index |
| 5 | `app/Models/HeroSlideConfig.php` | **CRE** | Model singleton: `current()` (firstOrCreate), `defaultSlideId()`, `currentDefaultSlide()`, relasi `defaultSlide()` |
| 6 | `app/Models/HeroSlide.php` | **MOD** | **Hapus**: `SWAP_TOKEN`, `$swappingDefault`, `beginSwap()`, `endSwap()`, `is_default` cast/Fillable, guard `creating()` related to is_default, guard `saving()` atomic swap, guard `updating()` unset. **Tambah**: `isDefault()` via config. **Pertahankan**: guard delete/draft/nonaktif (pakai `isDefault()`) |
| 7 | `app/Services/HeroSlideService.php` | **MOD** | Sederhanakan: 1 baris `HeroSlideConfig::current()->update(...)`. Tanpa lock/transaction/flag/token |
| 8 | `app/Policies/HeroSlidePolicy.php` | **MOD** | `$heroSlide->is_default` → `$heroSlide->isDefault()` |
| 9 | `app/Filament/.../HeroSlideResource.php` | **MOD** | Placeholder + table column → pakai `isDefault()`; hapus `IconColumn` import |
| 10 | `app/Filament/.../Pages/EditHeroSlide.php` | **MOD** | Semua `->is_default` → `->isDefault()` |
| 11 | `database/factories/HeroSlideFactory.php` | **MOD** | Hapus `is_default` field + `default()` state |
| 12 | `database/seeders/HeroSlideSeeder.php` | **MOD** | `firstOrCreate` slide → `HeroSlideConfig::firstOrCreate` dengan `default_hero_slide_id` |
| 13 | `tests/Feature/HeroSlideGuardTest.php` | **REWRITE** | 15 test baru dari nol. Semua test lama (SWAP_TOKEN/beginSwap/QB bypass) dihapus |

### Hasil Test

```
Tests: 35 | Passed: 35 | Assertions: 74
```

Test baru (15):

| Test | Hasil |
|------|-------|
| `test_promote_as_default_works_for_published_active_slide` | ✅ |
| `test_promote_as_default_rejects_draft_slide` | ✅ |
| `test_promote_as_default_rejects_inactive_slide` | ✅ |
| `test_promote_as_default_swaps_existing_default` | ✅ |
| `test_is_default_returns_false_when_config_is_null` | ✅ |
| `test_is_default_returns_false_for_non_default_slide` | ✅ |
| `test_cannot_delete_default_slide` | ✅ |
| `test_can_delete_non_default_slide` | ✅ |
| `test_cannot_draft_default_slide` | ✅ |
| `test_cannot_deactivate_default_slide` | ✅ |
| `test_can_draft_non_default_slide` | ✅ |
| `test_config_always_has_exactly_one_row` | ✅ |
| `test_config_default_is_null_when_slide_deleted` | ✅ (FK ON DELETE SET NULL) |
| `test_concurrent_promotes_result_in_consistent_state` | ✅ |
| `test_can_update_non_guard_fields_on_default_slide` | ✅ |

### Statistik

- **Insertions:** 435
- **Deletions:** 741
- **Net:** −306 lines — kode **lebih sederhana** meskipun menambah 3 migration + 1 model baru
- **File dibuat:** 4 (1 model + 3 migration)
- **File dimodifikasi:** 8

---

## Status: SELESAI

- Commit: `ca73490` — `[DSE] Restrukturisasi arsitektur: HeroSlideConfig sbg single source of truth`
- Tests: 35/35 passed
- **Siap di-push manual oleh RDA**
