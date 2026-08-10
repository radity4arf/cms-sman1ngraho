# Resume Dispatch — DSE Task 2 (Issue #4, #5, #6 CGX)

Gunakan file ini untuk me-resume sesi DSE. Copy-paste seluruh isi di bawah ke DSE saat lanjut.

---

Kamu adalah DSE — Lead Programmer, project CMS Portal SMAN1 Ngraho (Laravel + Filament).
Branch kerja: `feature/fase3-database-crud` (lanjutkan, jangan bikin branch baru).

KONTEKS: TASK 2 dari 2 — melanjutkan Task 1 (report18.md, issue #1/#2/#3 selesai).
Sekarang kerjakan issue #4, #5, #6 dari review CGX (report17.md).

Commit TERPISAH per poin, tag [THECHNOLOGY-{ACTION}] — RDA Universal Standard v1.1 §7.

---

## POIN 4 — Testing (SCOPE DIPERSEMPIT, bukan full coverage 9 entitas)

CGX minta test untuk 6 kategori × 9 entitas. Ini TIDAK dikerjakan penuh — sudah dipertimbangkan
terhadap RDA Universal Standard §A.11 (test untuk logic penting, bukan coverage menyeluruh generik).
Yang WAJIB ditulis sekarang HANYA untuk logic berisiko tinggi berikut:

a) **Guard is_default HeroSlide (Poin 2, Task 1)** — Feature/Unit test:
   - Dua create/update bersamaan set is_default pada slide berbeda →
     assert cuma satu yang true di akhir (test lockForUpdate + unique index)
   - Coba update is_default true→false tanpa kandidat pengganti → assert
     RuntimeException dilempar
   - Assert guard delete/draft/nonaktif existing tetap jalan (regresi check)

b) **Validasi upload (Unduhan + Photo, RT-11)** — Feature test:
   - Upload file di luar whitelist MIME → assert ditolak server-side
     (bukan cuma form/client-side)
   - Upload file >10MB → assert ditolak
   - Uji untuk minimal 2 collection: Download dan Photo (yang paling
     baru dapat CRUD di Poin 3)

c) **FK constraint / cascade** — Feature test:
   - `downloads.download_category_id` RESTRICT → hapus kategori yang masih
     dipakai → assert ditolak dengan pesan jelas
   - `photos.album_id` cascade → force-delete album → assert foto ikut
     terhapus; soft-delete album → assert foto ikut soft-delete

Test CRUD generik & audit log created_by/updated_by untuk 9 entitas TETAP TIDAK
dikerjakan — confirmed out of scope, jadi task terpisah nanti.

## POIN 5 — File Header & Tag Atribusi (MINOR)

File: app/Filament/Resources/**/Pages/{Create,Edit,List}*.php

Tambahkan file header (RDA Universal Standard §A.7: nama file/class, deskripsi singkat,
@author, @created, @updated) pada Page files yang belum punya. Tambahkan tag
[THECHNOLOGY-MOD] untuk file yang disentuh sesi ini (Task 1 & 2). Cakupan: minimal
seluruh file yang dimodifikasi di Poin 1-3 (Task 1) + file Page yang menurut CGX
report17.md eksplisit disebut tidak punya header.

## POIN 6 — Empty-State (ADMIN-ONLY, publik TETAP di luar scope)

Yang DIKERJAKAN: warning di admin List Page kalau resource kosong — pakai
EmptyStateHeading/EmptyStateDescription bawaan Filament, tidak perlu komponen baru.
Terapkan ke semua 13 List Resource.

Yang TIDAK dikerjakan (tetap ditunda ke Fase 4, JANGAN disentuh): Perilaku publik
kosong/fallback. Mekanisme (named scope published(), RT-14) sudah tersedia dari Fase 3.
JANGAN bangun route/view/logic publik apa pun.

---

## ATURAN UMUM

- TIDAK git push — commit saja
- TIDAK merge sendiri
- Laporan: lanjutkan penomoran reports/ (append-only), laporkan status
  per-poin (selesai/kendala), termasuk hasil test (pass/fail) di Poin 4
- Kalau menemukan Policy Poin 1 (Task 1) ternyata TIDAK ter-load saat
  menulis test Poin 4a — laporkan sebagai temuan terpisah, JANGAN diam-diam
  diperbaiki di luar scope task ini tanpa dilaporkan dulu
