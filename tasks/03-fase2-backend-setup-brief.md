---
project: CMS Portal SMAN1 Ngraho
domain: sman1ngraho.sch.id
doc: 03-fase2-backend-setup-brief.md
fase: 2
jalur: Backend/coding (Orchestrator DSE↔CLX)
eksekutor: DSE
reviewer: CLX (via Orchestrator)
status: dispatched
created: 2026-08-03
router: CLA (Clara Adams)
---

# Brief Teknis — Fase 2: Setup Project, Auth, Permission (DSE)

## 1. Tujuan Fase

Setup awal project backend: inisialisasi repo, struktur project, panel admin (Filament), sistem autentikasi, dan sistem permission granular per fitur. Jadi fondasi untuk Fase 3 (skema database + CRUD menu) dan Fase 4 (integrasi frontend publik).

## 2. Stack

- **Framework:** Laravel + Filament (admin panel) — mengacu ke `02-wireframe-brief.md` (Login: Filament default; Unduhan: butuh media library → `spatie/laravel-medialibrary`)
- **Permission:** rekomendasi `spatie/laravel-permission` untuk model permission granular per fitur (bukan role tetap) — DSE final decide, boleh diganti kalau ada pendekatan lebih sesuai selama tetap granular per fitur.

## 3. Scope Task

1. **Init repo Git** — repo belum ada, DSE yang inisialisasi sebagai bagian task ini (branch kerja per task, sesuai `00-Workflow-Team-CMS-Project.md` §3).
2. **Setup project Laravel + Filament** — instalasi awal, struktur folder standar, `.env.example` disiapkan (tanpa credential asli).
3. **Auth** — sistem login berfungsi (Filament default untuk admin panel).
4. **User & Permission — model granular (bukan role tetap):**
   - **Admin** — akun awal/superuser, akses penuh, bisa **create user baru**
   - **User** — dibuat oleh Admin. Setiap user baru **tidak punya role tetap**, melainkan diberi **akses per fitur secara spesifik** oleh Admin (mis. fitur A boleh, fitur B tidak — assignable per user, bukan grup role)
   - Admin harus bisa **assign & cabut akses fitur** kapan saja per user (bukan sekali set saat create saja)
   - Kalau ada ambiguitas soal daftar fitur yang bisa di-permission-kan (karena modul CMS lengkap baru terbentuk di Fase 3+), DSE boleh mulai dari struktur permission generik dulu (fondasi sistem), lalu fitur konkret ditambahkan bertahap seiring modul CMS terbentuk — dicatat sebagai asumsi di ringkasan perubahan, bukan diputuskan sepihak tanpa dicatat.

## 4. Batasan & Kepatuhan Wajib

Mengacu ke `RDA-Universal-Standard-v1.1` (Approved 2026-08-03) — **wajib dipatuhi DSE**, dicek CLX per iterasi:

- **Git wajib mutlak, per-step** (§7) — commit tiap perubahan signifikan selesai, bukan ditumpuk di akhir. Commit message jelas & deskriptif.
- **Tag atribusi kode** (§A.8.1) — pakai `[THECHNOLOGY-{ACTION}-{ROLE}]` untuk perubahan/penambahan signifikan, contoh: `// [THECHNOLOGY-CRE-DSE] : setup Filament panel awal`
- **General Rules** (referensi `00-Workflow-Team-CMS-Project.md` §7): no hardcode credential, `.env` tidak boleh ter-commit, prepared statement wajib, no commit langsung ke branch utama, no silent failure.
- **Security dasar:** validasi input, escape output, proteksi auth standar Laravel (jangan nonaktifkan CSRF/middleware default tanpa alasan kuat).

## 5. Kriteria Approval (untuk Orchestrator & CLX)

**STATUS: APPROVED** kalau:
- Repo ter-init dengan struktur project Laravel+Filament standar
- Login admin panel berfungsi tanpa error
- Admin bisa create user baru
- Admin bisa assign & cabut akses fitur spesifik per user (granular, terbukti berfungsi minimal untuk 1-2 fitur contoh)
- Tidak ada credential hardcode, `.env.example` tersedia, `.env` tidak ter-commit
- Commit history mengikuti §7 (per-step, bukan 1 commit besar)

**STATUS: NEEDS_REVISION** kalau ada pelanggaran General Rules, bug fungsional (login gagal, permission tidak granular/malah jadi role tetap), atau struktur project tidak standar Laravel.

## 6. Konfigurasi Orchestrator

- **max_iteration:** 5 (default sesuai `00-Workflow-Team-CMS-Project.md` §3)
- Loop otomatis DSE↔CLX, tanpa approval manual CLA di tiap iterasi
- Laporan akhir (APPROVED / UNRESOLVED setelah 5 iterasi) dikirim ke CLA untuk Final Approval teknis, lanjut approval manual RDA

### 6.1 Penentuan Bobot Task & Model (§6 `00-Workflow-Team-CMS-Project.md`)

Fase 2 dinilai **BERAT** — menyentuh auth, permission granular per fitur, dan struktur project awal. Model di-set CLA sebelum dispatch:

| Agent | Model | ID (opencode) |
|---|---|---|
| CLX | Sonnet 5 | `anthropic/claude-sonnet-5` |
| DSE | DeepSeek Pro | `deepseek/deepseek-v4-pro` |

Config sudah diterapkan di `clx.md` dan `dse.md` per 2026-08-03. Kalau ada task susulan ber-bobot ringan (mis. styling minor di Fase 4), model akan diganti CLA ke `anthropic/claude-sonnet-4-6` (CLX) / `deepseek/deepseek-v4-flash` (DSE) sebelum dispatch task itu.

## 7. Log

| Tanggal | Aktivitas | Oleh |
|---|---|---|
| 2026-08-03 | Brief dibuat (draft awal: role tetap Admin+User) | CLA |
| 2026-08-03 | Revisi brief — model diubah ke permission granular per fitur (Admin create user + assign akses fitur spesifik), bukan role tetap | CLA |
| 2026-08-03 | Model CLX & DSE di-set sesuai bobot task BERAT — Sonnet 5 & DeepSeek Pro (§6.1), diterapkan ke `clx.md`/`dse.md` | CLA |
