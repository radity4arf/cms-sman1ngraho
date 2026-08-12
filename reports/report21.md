# Report #21 — DSE (Delia Tse)

**Tanggal:** 2026-08-12  
**Branch:** `feature/fase3-database-crud`  
**Trigger:** CLX review — STATUS: NEEDS_REVISION (2 CRITICAL issues)  

---

## Task: Perbaikan 2 CRITICAL issues dari review CLX — HeroSlide is_default guard

### Issue 1 (CRITICAL): Tidak ada jalur UI yang memanggil HeroSlideService::promoteAsDefault()

**Root cause:** Form HeroSlideResource masih menggunakan `Toggle::make('is_default')` yang menyimpan langsung ke model, bypassing `HeroSlideService`. Toggle ini bergantung pada logic swap lama di model saving() event, sehingga klaim "satu-satunya jalur resmi" tidak benar dan terjadi duplikasi mekanisme.

**Fix:**
1. `app/Filament/Resources/HeroSlides/HeroSlideResource.php` — Hapus `Toggle::make('is_default')` dari form, ganti dengan `Placeholder` readonly yang menampilkan status default saat ini dan memberi instruksi gunakan tombol "Jadikan Default" di halaman edit.
2. `app/Filament/Resources/HeroSlides/Pages/EditHeroSlide.php` — Tambah `Action::make('promote_default')` sebagai header action yang:
   - Hanya visible jika slide bukan default, published, dan aktif
   - Meminta konfirmasi modal sebelum eksekusi
   - Memanggil `HeroSlideService::promoteAsDefault()` 
   - Menampilkan notifikasi sukses/gagal

### Issue 2 (CRITICAL): Partial unique index hanya menjamin maks 1 default, bukan min 1. beginSwap() public bisa dipanggil dari Tinker/job.

**Root cause:**
- `$swappingDefault` flag adalah `protected static`, bisa diakses subclass
- `beginSwap()` / `endSwap()` public tanpa validasi — bisa dipanggil dari mana saja (Tinker, job, CLI)
- Tidak ada try/finally — jika transaksi gagal, flag tetap true dan guard bypassed permanen
- Query Builder bypass (`HeroSlide::where(...)->update(...)`) tidak dicegah di level database

**Fix:**
1. `app/Models/HeroSlide.php`:
   - Ubah `$swappingDefault` dari `protected static` → `private static`
   - Tambah `public const SWAP_TOKEN` — token validasi untuk beginSwap/endSwap
   - `beginSwap(string $token)` dan `endSwap(string $token)` sekarang validasi token — throw `RuntimeException` jika token tidak cocok
   - Tambah try/finally di `saving()` event DB::transaction — flag selalu di-reset
   - Internal saving event akses `$swappingDefault` langsung (private access valid dari dalam class)
2. `app/Services/HeroSlideService.php`:
   - Panggil `beginSwap(HeroSlide::SWAP_TOKEN)` dan `endSwap(HeroSlide::SWAP_TOKEN)` dengan token valid
   - Bungkus operasi swap dalam try/finally — flag di-reset meskipun exception
3. `database/migrations/2026_08_12_000001_add_hero_slides_guard_triggers.php` (BARU):
   - 3 DB trigger (MySQL + SQLite) mencegah Query Builder bypass:
     - `hero_slides_guard_default_deactivate` — cegah is_active=0 pada default
     - `hero_slides_guard_default_draft` — cegah status='draft' pada default
     - `hero_slides_guard_default_delete` — cegah DELETE pada default
   - Catatan: guard "unset is_default terakhir" tidak bisa via row-level trigger karena saat swap sah akan ada momen 0 default. Guard ini di-handle model-level (updating event).
4. `tests/Feature/HeroSlideGuardTest.php`:
   - 6 test baru: validasi token beginSwap/endSwap (invalid/wrong/valid), flag-reset setelah promote, flag-reset setelah endSwap
   - Total: 21 tests, all passed

---

## Diff Summary

| File | Aksi | Deskripsi |
|------|------|-----------|
| `app/Filament/Resources/HeroSlides/HeroSlideResource.php` | MOD | Hapus Toggle is_default → ganti Placeholder readonly; instruksi gunakan tombol promote di halaman edit |
| `app/Filament/Resources/HeroSlides/Pages/EditHeroSlide.php` | MOD | Tambah PromoteAsDefaultAction via HeroSlideService — satu-satunya jalur UI resmi swap is_default |
| `app/Models/HeroSlide.php` | MOD | $swappingDefault → private; SWAP_TOKEN constant; beginSwap/endSwap token-guarded; try/finally di saving event |
| `app/Services/HeroSlideService.php` | MOD | Panggil beginSwap/endSwap dengan SWAP_TOKEN; try/finally untuk flag reset |
| `database/migrations/2026_08_12_000001_...triggers.php` | CRE | 3 DB trigger (deactivate/draft/delete default) untuk cegah Query Builder bypass |
| `tests/Feature/HeroSlideGuardTest.php` | MOD | 6 test baru: token-guard validation + flag reset verification (21 total, all pass) |

---

## Status: SELESAI

- Commit: `38ebb51` — `[DSE] CLX fix: secure is_default swap — token-guard beginSwap/endSwap, try/finally, DB triggers, UI promote action via HeroSlideService`
- Tests: 21/21 passed
- **Siap di-push manual oleh RDA**
