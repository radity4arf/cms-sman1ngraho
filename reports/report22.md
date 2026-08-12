# Report #22 — DSE (Delia Tse)

**Tanggal:** 2026-08-12  
**Branch:** `feature/fase3-database-crud`  
**Trigger:** CLA dispatch — Fix lanjutan Query Builder bypass gap (Issue #2 CGX)  

---

## Task: Tutup celah Query Builder bypass — unset is_default tanpa Eloquent events

### Root Cause

Report #21 mengakui bahwa guard "unset is_default terakhir" tidak bisa via row-level trigger biasa karena saat swap sah (`promoteAsDefault`) ada momen 0 default. Guard ini sebelumnya diserahkan ke **model-level `updating` event**.

Masalah: Query Builder `HeroSlide::query()->where('id', X)->update(['is_default' => false])` **TIDAK memicu Eloquent model events** (`saving`/`updating`). Jadi menyerahkan proteksi ke model-level event tidak menutup skenario yang justru jadi concern utama CGX.

### Pendekatan Dipilih: Opsi (a) — DB Trigger dengan Penanda Transaksi

Setelah evaluasi ketiga opsi:

| Opsi | Deskripsi | Feasibility |
|------|-----------|-------------|
| (a) DB trigger + penanda transaksi | Session variable (MySQL) / flag table (SQLite) sebagai sinyal ke trigger bahwa unset sedang dalam swap sah | ✅ **Dipilih** — feasible dengan stack MySQL+SQLite, risiko connection-pool leak sudah didokumentasikan & dimitigasi |
| (b) Revoke akses kolom langsung | DB view + stored procedure untuk mutasi is_default | ❌ Terlalu invasif — perlu arsitektur ulang total, tidak praktis |
| (c) Compensating control | Audit log + scheduled job deteksi 0-default | ❌ Bukan preventive control — tidak memenuhi requirement CGX yang minta proteksi database |

### Detail Implementasi

#### Mekanisme Penanda Transaksi

1. **`HeroSlide::beginSwap(SWAP_TOKEN)`** — dipanggil sebelum unset dalam swap sah:
   - **MySQL**: `SET @hero_swapping_default = 1` (session variable, connection-scoped)
   - **SQLite**: `INSERT INTO hero_slide_swap_flags (flag) VALUES (1)` (regular table)
   - Plus: set `static::$swappingDefault = true` (PHP flag untuk model-level guard)

2. **`HeroSlide::endSwap(SWAP_TOKEN)`** — dipanggil di `finally` block:
   - **MySQL**: `SET @hero_swapping_default = 0`
   - **SQLite**: `DELETE FROM hero_slide_swap_flags`
   - Plus: reset `static::$swappingDefault = false`

3. **DB Trigger** (`hero_slides_guard_default_unset`):
   - Cek penanda transaksi. Jika TIDAK disetel → BLOCK unset.
   - Jika disetel → izinkan (swap sah sedang berjalan).

#### File yang Diubah/Dibuat

| File | Aksi | Deskripsi |
|------|------|-----------|
| `app/Models/HeroSlide.php` | MOD | `beginSwap()`/`endSwap()`: tambah DB-level flag (session var MySQL / INSERT-DELETE flag table SQLite); `saving()` event: gunakan `beginSwap(SWAP_TOKEN)` + `endSwap(SWAP_TOKEN)` alih-alih manipulasi langsung `$swappingDefault` |
| `database/migrations/2026_08_12_000002_...` | CRE | Migration: table `hero_slide_swap_flags` (SQLite) + trigger `hero_slides_guard_default_unset` (MySQL + SQLite) |
| `tests/Feature/HeroSlideGuardTest.php` | MOD | 4 test baru: QB bypass tanpa pengganti ditolak, QB bypass dengan kandidat ditolak, regresi swap sah via service, regresi factory create default |

### Trade-off & Risiko

**Risiko: Connection-pool leak (MySQL session variable)**
- Session variable MySQL adalah **connection-scoped**, bukan transaction-scoped. Jika `finally` block GAGAL dieksekusi (fatal error/PHP crash), variable `@hero_swapping_default = 1` tetap tersetel di connection tersebut.
- Konsekuensi: request berikutnya yang mendapat connection yang sama akan **false-negative** — unset ilegal diizinkan karena trigger melihat flag swap=true.
- **Mitigasi**: 
  1. `finally` block selalu dieksekusi PHP (bahkan setelah exception, kecuali fatal error)
  2. Code review memastikan tidak ada `exit()`/`die()` antara beginSwap dan endSwap
  3. Test coverage: `test_flag_reset_after_promote_as_default` memverifikasi flag di-reset setelah operasi normal
  4. Connection pool Laravel default menggunakan `PDO::ATTR_EMULATE_PREPARES` + reconnect strategy — variable leak isolated ke 1 connection dan di-reset saat reconnect

**Risiko: SQLite flag table growth**
- Flag table `hero_slide_swap_flags` secara normal hanya berisi 1 row selama swap, lalu kosong setelahnya.
- Jika aplikasi crash saat swap → 1 row tersisa. Migrasi `migrate:fresh` akan drop & recreate table.
- **Mitigasi**: `DELETE FROM hero_slide_swap_flags` di `endSwap()`. Idempoten — bisa dipanggil berkali-kali.

**Mengapa bukan preventive control 100%:**
- Tanpa **deferred constraint** (hanya PostgreSQL), tidak ada cara murni DB-level untuk membedakan "unset dalam swap sah" vs "unset ilegal" tanpa penanda eksternal (session variable / flag table).
- Penanda eksternal bersifat **cooperative** — bergantung pada aplikasi menyetelnya dengan benar. Ini adalah batasan arsitektur MySQL/SQLite yang tidak bisa dihindari.
- Namun demikian, pendekatan ini **jauh lebih kuat** dari model-level guard saja karena Query Builder bypass TIDAK akan menyetel penanda → trigger memblokir.

### Hasil Test

```
Tests: 25 | Passed: 25 | Assertions: 56
```

4 test baru khusus celah ini:

| Test | Hasil | Skenario |
|------|-------|----------|
| `test_query_builder_unset_default_without_replacement_is_rejected` | ✅ PASS | `HeroSlide::query()->where(...)->update(['is_default' => false])` pada slide default tanpa pengganti → `QueryException` |
| `test_query_builder_unset_default_even_with_candidate_is_rejected` | ✅ PASS | QB unset dengan kandidat published+aktif → tetap ditolak (flag swap tidak disetel) |
| `test_legitimate_swap_via_service_still_works_with_trigger_active` | ✅ PASS | `promoteAsDefault()` via service → berhasil (tidak false-positive) |
| `test_factory_create_default_still_works_with_trigger_active` | ✅ PASS | `HeroSlide::factory()->default()->create()` → berhasil (saving event swap jalan) |

---

## Status: SELESAI

- Commit: menunggu commit
- Tests: 25/25 passed
- **Siap di-push manual oleh RDA**

### Catatan untuk CLA
- Opsi (a) dipilih sebagai yang paling feasible. **Bukan preventive control 100% murni** karena bergantung penanda koperatif — batasan arsitektur MySQL/SQLite.
- Kalau RDA meminta preventive control murni, perlu migrasi ke PostgreSQL (deferred constraint) atau restruktur total (opsi b — stored procedure).
- **Koreksi referensi**: reviewer yang benar adalah **CGX**, bukan CLX (seperti yang salah tulis di report21.md). Report ini dan seterusnya akan pakai "CGX".
