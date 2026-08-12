# Report #27 — DSE (Delia Tse)

**Tanggal:** 2026-08-12  
**Branch:** `feature/fase3-database-crud`  
**Trigger:** CLA dispatch — CGX round 6: 1 CRITICAL — deleted_at belum dicek di target validity

---

## Ringkasan Issue

Verdict CGX (Codex 5.6 Luna Effort High) — 1 CRITICAL:

**Masalah:** Trigger `hero_slide_config_guard_target_valid` (migration 000014) dan validasi di `HeroSlideService::promoteAsDefault()` cuma cek `status='published'` dan `is_active=1`. Tidak ada cek `deleted_at IS NULL`.

**Skenario bypass:** Slide non-default di-soft-delete (masuk Trash) → status & is_active tetap sama seperti sebelum di-soft-delete → pointer config bisa diarahkan ke slide itu via Query Builder maupun `promoteAsDefault()`.

---

## Fix yang Diterapkan

### 1. Migration `000014` — Edit trigger existing

| Komponen | Sebelum | Sesudah |
|----------|---------|---------|
| **MySQL trigger** | `AND status = 'published' AND is_active = 1` | `AND status = 'published' AND is_active = 1 AND deleted_at IS NULL` |
| **SQLite trigger** | Sama seperti MySQL | Sama — ditambah `AND deleted_at IS NULL` |
| **Docblock** | Tidak mention deleted_at | Diupdate: "cek status='published' DAN is_active=1 **DAN deleted_at IS NULL**" |
| **@updated** | Hanya CGX round 4 | Ditambah: CGX round 6: tambah cek deleted_at IS NULL |

### 2. Migration `000016` — Drop & Recreate trigger (untuk DB existing)

File baru: `database/migrations/2026_08_12_000016_fix_target_validity_deleted_at_check.php`

- `up()`: DROP trigger lama → CREATE ulang dengan `deleted_at IS NULL`
- `down()`: DROP trigger → CREATE versi sebelumnya (rollback safe)

Rationale: DB yang sudah menjalankan migration 000014 tidak akan otomatis dapat fix kalau hanya edit migration — perlu migration baru untuk DROP & RECREATE trigger.

### 3. `HeroSlideService::promoteAsDefault()` — Tambah cek `trashed()`

```php
// [THECHNOLOGY-MOD] : CGX round 6 — cek soft-delete (trashed)
if ($slide->trashed()) {
    throw new \RuntimeException(
        'Slide yang sudah dihapus tidak dapat dijadikan default. Kembalikan dari Trash terlebih dahulu.'
    );
}
```

Cek dilakukan setelah cek status dan is_active, sebelum UPDATE config.

---

## Diff Summary

| File | Aksi | Deskripsi |
|------|------|-----------|
| `database/migrations/...000014...` | **MOD** | Tambah `AND deleted_at IS NULL` di trigger target-validity (MySQL + SQLite) |
| `database/migrations/...000016...` | **CRE** | Migration baru — DROP & RECREATE trigger untuk DB existing |
| `app/Services/HeroSlideService.php` | **MOD** | Tambah cek `$slide->trashed()` di `promoteAsDefault()` |

**Stats:** 3 files changed, 141 insertions(+), 1 deletion(-)

---

## Status: SELESAI

- Commit: `d57581f`
- Branch: `feature/fase3-database-crud`
- **Siap di-push manual oleh RDA**
