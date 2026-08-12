---
project: CMS Portal SMAN1 Ngraho
domain: sman1ngraho.sch.id
doc: 00-Workflow-Team-CMS-Project.md
status: active
created: 2026-07-27
updated: 2026-08-12
---

# Workflow Team — CMS Portal SMAN1 Ngraho

## 1. Tim & Peran

| Kode | Nama | Peran di Project Ini |
|---|---|---|
| CLA | Clara Adams | Router, Final Authority, Standards Authority |
| CLI | Clio Ingram | Backup Router & Final Authority — khusus saat CLA limit |
| CLU | Camila Lucero | UI/UX Design & Implementation Specialist; User Interface Design System Specialist |
| CLE | Chloe Nguyen | Backup CLU — role sama: UI/UX Design & Implementation; User Interface Design System |
| QWE | Qian Wen | Technical Requirements Analysis; Software Solution Architecture; Implementation Planning & Technical Specification — dilibatkan per-task atas keputusan CLA, sebelum DSE mulai coding |
| DSE | Delia Tse | Lead Programmer — setup, database, backend, integrasi; implementasi manual (dispatch langsung dari CLA, tanpa loop otomatis) |
| CLX | Celixa Laurent | QA & Compliance — cek kepatuhan standar, code review teknis, bug hunting aktif. **Status: OFF sementara** (keterbatasan dana RDA, mulai Fase 4, 2026-08-12) — tidak di-dispatch sampai diaktifkan kembali. Setara: CGX, GNC |
| CGX | Chantal Gaux | Setara CLX/GNC (bukan backup) — role identik (QA & Compliance, code review, bug hunting). CLA pilih reviewer per-task dari yang aktif, dicatat di log. Jalan di luar OpenCode (interface terpisah, seperti QWE) |
| GNC | Ginevra Niccoli | **Baru (2026-08-12)** — Software Quality Assurance & Verification Specialist; Technical Standards Compliance & Code Review Specialist; Software Review Documentation & Quality Reporting Specialist. Setara CLX/CGX (bukan backup). Platform Antigravity, model Gemini 3.1 Pro High. Jalan di luar OpenCode |
| CGA | Catherine Gemma | Visual Brief (Hero/Favicon) untuk GNI, Documentation Standard Author |

**Reviewer aktif saat ini:** CGX dan GNC. CLX tetap tercatat di tim (bukan dihapus), tinggal diaktifkan lagi kapan saja RDA putuskan.

## 2. Model Kerja: Flat Routing

CLA dispatch langsung ke eksekutor — tidak ada perantara wajib. CGA bukan gerbang untuk semua task, hanya untuk task visual (Hero/Favicon).

```
                    |--> QWE   (spec/arsitektur, opsional pre-DSE — keputusan CLA)
                    |        |
                    |       DSE   (backend/logic/deployment)
                    |
CLA (router) -------|--> CLU   (UI code)
                    |
                    |--> CGA   (visual brief untuk GNI)
                    |        |
                    |       GNI (eksekusi visual: hero, favicon)
                    |
                    |--> CLO   (portfolio/brand content, project selesai)

Backup: CLI <- backup CLA (full authority) | CLE <- backup CLU
Setara (reviewer): CLX <-> CGX <-> GNC (bukan backup, tiga reviewer sejajar — CLX off sementara)
```

## 3. Alur Kerja Coding: Manual — QWE (opsional) → DSE → RDA Trial → Reviewer (Sekali di Akhir Fase)

**Berlaku khusus untuk task coding** (jalur Backend/coding, UI+backend bagian implementasi logic). **Orchestrator Agent dihapus dari workflow** (per keputusan RDA, 2026-08-04) — alasan: biaya token tinggi dan kualitas hasil loop otomatis di bawah ekspektasi. Semua dispatch sekarang manual, dikendalikan CLA/RDA langsung, bukan otomatis.

```
CLA dispatch task ke DSE (langsung — tidak ada perantara otomatis)
        ↓
   [OPSIONAL — keputusan CLA per-task, lihat kriteria di bawah]
   QWE — analisis requirement + rancangan arsitektur + spec implementasi
        ↓
DSE — implementasi kode sesuai instruksi CLA (atau spec QWE kalau dilibatkan)
      Commit per-step tetap WAJIB (RDA Universal Standard v1.1 §7) — bukan loop otomatis,
      tapi disiplin commit tetap berlaku
        ↓
RDA — trial manual (testing langsung di aplikasi, BUKAN reviewer teknis).
      Catatan: trial manual bisa di-skip atas keputusan eksplisit RDA per-task
      (mis. mengandalkan automated test coverage DSE) — dicatat di log, bukan default
        ↓
   Ada masalah → revisi manual: RDA/CLA instruksikan DSE langsung, ulangi trial
   Trial OK (atau di-skip RDA) → lanjut ke reviewer
        ↓
Reviewer (CLX / CGX / GNC — CLA pilih dari yang aktif) — review SEKALI untuk
keseluruhan fase (bukan per-iterasi DSE), verdict tetap terstruktur:
   STATUS: APPROVED / NEEDS_REVISION
   ISSUES: [file/fungsi] deskripsi → saran perbaikan
   SEVERITY: CRITICAL / MINOR (opsional)
        ↓
   NEEDS_REVISION → balik ke DSE dengan catatan ISSUES (manual, bukan auto-loop).
                     Kalau butuh berapa ronde untuk 1 fase, itu normal — dicatat
                     lengkap di 01-routing-decision.md per ronde
   APPROVED → lanjut ke CLA Final Approval
        ↓
CLA — Final Approval teknis
        ↓
RDA — Approval final manual (sign-off). Tidak cocok → balik manual ke DSE.
```

### 3.1 Kriteria Keterlibatan QWE (keputusan CLA per-task)

QWE dilibatkan kalau salah satu terpenuhi:
- Requirement task ambigu / butuh diterjemahkan jadi spec teknis konkret sebelum DSE mulai
- Fitur baru yang menyentuh arsitektur (bukan sekadar revisi/tambahan kecil ke struktur yang sudah ada)
- Task berisiko tinggi menghasilkan banyak revisi kalau DSE langsung coding tanpa spec matang (tujuan utama QWE: minimalkan iterasi revisi DSE)

QWE **tidak perlu** dilibatkan untuk: bug fix, revisi minor, task dengan requirement sudah jelas dari brief sebelumnya (mis. lanjutan task yang sudah punya konteks lengkap).

### 3.2 Dua Lapis Approval (tetap berlaku, mekanisme berubah)

| Lapis | Pihak | Sifat |
|---|---|---|
| Teknis | CLX / CGX / GNC (setara, CLA pilih dari yang aktif per-task) | **Manual, sekali di akhir fase** — bukan otomatis per-iterasi. Bisa berulang beberapa ronde kalau NEEDS_REVISION, tetap manual per ronde |
| Final | RDA (via CLA) | Manual — bisa reject walau reviewer APPROVED, memicu revisi ulang manual |

**Integrasi Git:**
```
DSE selesai revisi → commit ke branch kerja (message: [DSE] <ringkasan>)
RDA trial OK → Reviewer review (sekali) → APPROVED → merge ke branch staging
RDA approve final → merge ke main/production
```
- Reviewer (CLX/CGX/GNC) tidak commit/push — read-only.
- Satu branch per task/fase.

**Konfigurasi teknis (update 2026-08-12):**
- `.opencode/agents/dse-pro.md` dan `.opencode/agents/dse-flash.md` — menggantikan `dse.md` tunggal. Model dipilih lewat file config terpisah (bukan edit manual), dijalankan via `dsepro.bat` / `dseflash.bat` di Desktop (masing-masing memanggil Task Cmder `dsepro` / `dseflash`). CLA menyebutkan `.bat` yang harus dijalankan di setiap brief dispatch, berdasarkan bobot task (lihat §6).
- `.opencode/agents/clx.md` — **dihapus** (CLX off sementara). Kalau CLX diaktifkan kembali, file ini perlu dibuat ulang.
- `.opencode/agents/qwe.md` — tetap ada, `mode: primary`.
- CGX dan GNC **tidak** dijalankan lewat OpenCode — interface terpisah (ChatGPT Codex untuk CGX, Antigravity untuk GNC). Tidak ada file `.md` config di `.opencode/agents/` untuk keduanya; context lengkap diberikan CLA langsung di setiap dispatch brief.
- `orchestrator.md` tetap dihapus dari `.opencode/agents/` (keputusan 2026-08-04).

## 4. Alur Kerja Umum — Non-Coding (UI/Visual)

Berlaku untuk task UI (CLU/CLE) yang tidak menyentuh backend, dan task visual (CGA/GNI):

```
Eksekutor (CLU/CLE) mengerjakan
        ↓
QWE — review (spec teknis & kelayakan implementasi, menggantikan reviewer teknis untuk output CLU/CLE)
        ↓
   Sesuai? → Tidak: balik ke eksekutor, revisi, ulang ke QWE
            → Ya: lanjut ke CLA
        ↓
CLA — Final Approval
```

**Catatan:** Berlaku untuk **semua** output CLU/CLE (jalur UI-only maupun UI+backend) — QWE selalu review dulu sebelum ke CLA, tidak ada pengecualian. Untuk jalur UI+backend, spec hasil review QWE ini yang diteruskan ke DSE untuk implementasi (lihat §5). Task visual CGA/GNI (Hero/Favicon) tidak melalui alur ini — langsung ke Design Approval RDA (lihat §5, jalur Visual asset).

## 5. Jalur yang Tersedia

| Jalur | Kapan dipakai | Urutan |
|---|---|---|
| Super-minimum | Task simpel & instruksi sudah jelas | CLA → eksekutor manapun langsung → CLA (review) |
| Backend/coding | Task backend/logic/deployment | CLA → (QWE opsional) → DSE (manual) → RDA trial (atau skip atas keputusan RDA) → Reviewer aktif (CLX/CGX/GNC, sekali — bisa berulang ronde) → CLA (final approval) → RDA approve → publish |
| UI-only | UI presentational saja | CLA → CLU (desain+kode langsung) → QWE (review) → CLA (final approval) → publish |
| UI + backend | UI yang butuh sentuh logic backend | CLA → CLU (desain saja) → QWE (review + siapkan spec untuk DSE) → CLA → DSE (manual) → RDA trial → Reviewer aktif (sekali) → CLA (final approval) → RDA approve → publish |
| Visual asset | Hero Visualization / Favicon | CLA → CGA (brief) → GNI (eksekusi visual) → Design Approval (RDA) → publish |
| Portfolio/brand content | Project sudah selesai | CLA → CLO (sintesis draf) → approval CLA/RDA → publish |

## 6. Prinsip Routing

- 1 task = 1 owner default.
- Maksimal 2–3 handoff per task.
- CLU/CLE **tidak boleh** sentuh PHP logic/backend/query database (tetap DSE) atau perubahan arsitektur besar (tetap CLI).
- Kalau CLU menemukan task ternyata butuh backend di tengah jalan → CLU wajib stop & eskalasi ke CLA untuk di-route ulang ke DSE.
- QWE **tidak menulis kode** — output QWE murni spec/requirement/arsitektur untuk dipakai DSE, bukan implementasi. Keterlibatan QWE per-task adalah keputusan CLA (lihat §3.1), bukan default otomatis untuk semua task backend.
- CGA/GNI tidak pernah membersihkan watermark hasil generate image — dilakukan manual oleh RDA, di luar alur AI.
- DSE tidak punya wewenang eksekusi/deployment otonom — seluruh eksekusi (termasuk deploy ke hosting) wajib berdasarkan instruksi yang sudah disahkan CLA.
- Rotasi akun: CLA limit ~90% → backup CLI (mewarisi seluruh wewenang CLA). CLU limit → backup CLE.
- **Pemilihan model DSE otomatis oleh CLA berdasarkan bobot task**, dieksekusi lewat pemilihan `.bat` yang benar (bukan lagi edit manual `dse.md` — lihat §3.2):

  | Bobot Task | Kriteria | `.bat` yang dipakai | Model DSE |
  |---|---|---|---|
  | **Berat** | Menyentuh auth/security/permission, skema database/migration, business logic kompleks/integrasi banyak komponen | `dsepro.bat` | DeepSeek Pro |
  | **Ringan** | Styling/CSS minor, copy text, config kecil, bug fix trivial, cleanup dead code | `dseflash.bat` | DeepSeek Flash |

  **Model reviewer (CLX/CGX/GNC):** CLX (kalau aktif kembali) mengikuti tiering bobot task yang sama (Sonnet 5 untuk Berat, Sonnet 4.6 untuk Ringan). CGX dan GNC berjalan dengan konfigurasi model tetap di platform masing-masing (CGX: ChatGPT Codex, effort High — model spesifik dicatat CLA di log per sesi karena bisa berubah tergantung ketersediaan/limit, mis. Terra vs Luna; GNC: Gemini 3.1 Pro High) — tidak mengikuti tiering Berat/Ringan seperti CLX/DSE.

  RDA tetap bisa override manual kapan saja kalau menilai keputusan CLA kurang tepat — tapi default-nya otomatis via CLA, dicatat di brief dispatch task terkait & log routing-decision.

## 7. Code Review & Final Approval

Checklist wajib (TDS Section 11 — detail lengkap di `references/tds.md`):
- Kepatuhan TDS (naming convention, file header, comment standard, folder structure).
- General Rules (Section 13) — no hardcode berulang, no magic number/string, no duplicate/dead code, no credential di kode, prepared statement wajib, no commit langsung ke main, no silent failure, `.env` tidak boleh ter-commit.
- Kesesuaian wewenang eksekutor (DSE vs CLU tidak saling lewati batas).
- Security & error handling (validasi input, escape output/XSS, CSRF protection, proteksi DB-level untuk invariant data kritis — lihat preseden HeroSlideConfig Fase 3).

Hasil review: **Approved** / **Approved with Minor Revision** / **Revisi Diperlukan** (dilarang merge kalau status ini).

**Catatan:** Code Review (CLA) dan Design Approval (RDA) terpisah. CLA tidak menilai kecocokan visual/mockup — itu wewenang RDA. Task CGA/GNI tidak lewat Code Review CLA (bukan output kode).

## 8. Dokumen Terkait

| Dokumen | Fungsi | Lokasi |
|---|---|---|
| `01-routing-decision.md` | Identitas project & jalur kerja per fase — log kronologis lengkap | Vault Obsidian (dipindah dari `sman1ngraho/`, 2026-08-12 — governance, tidak perlu diakses DSE) |
| `02-wireframe-brief.md` | Brief wireframe untuk CLU | Vault (Fase 1, closed) |
| `03-fase3-qwe-spec.md` | Spec teknis Fase 3 dari QWE | Vault (Fase 3, closed) |
| `RDA-Universal-Standard-v1.1.md` | Governance umum lintas-project (naming, Git, comment standard, dll) | `sman1ngraho/docs/` — aktif |
| `TDS-QWE.md` | Spec peran QWE | `sman1ngraho/docs/` (kalau ada) |
| `TDS-DSE.md` | Spec peran DSE — permission, output, integrasi Git | `sman1ngraho/docs/` — aktif |
| `TDS-CLX.md` | Spec peran CLX | **Dihapus** (CLX off, 2026-08-12) — buat ulang kalau CLX diaktifkan kembali |
| `TDS-CGX.md` | Spec peran CGX | **Dihapus** (2026-08-12) — CGX selalu dapat full context di dispatch brief, file ini tidak pernah benar-benar dipakai |
| `TDS-GNC.md` | Spec peran GNC — format review setara CLX/CGX, batasan scope, kriteria pemilihan reviewer per-task | `sman1ngraho/docs/` — **aktif, dibuat 2026-08-12** (formal, atas permintaan eksplisit RDA — beda dari CGX) |
| `.opencode/agents/dse-pro.md`, `dse-flash.md` | Konfigurasi teknis DSE untuk OpenCode, `mode: primary`, dijalankan via `dsepro.bat` / `dseflash.bat` | `sman1ngraho/.opencode/agents/` — aktif |
| `.opencode/agents/clx.md` | Konfigurasi CLX | **Dihapus** (CLX off) |
| `reports/report29.md`, dst | Laporan kerja DSE per sesi/task — append-only. **Penomoran lanjut dari report29 (bukan reset)** — report.md s/d report28.md (Fase 2 & 3) diarsipkan ke vault, 2026-08-12 | `sman1ngraho/reports/` — folder aktif kosong per 2026-08-12, siap isi mulai Fase 4 |

## 9. Log Perubahan

| Tanggal | Perubahan | Disahkan oleh |
|---|---|---|
| 2026-07-27 | Dokumen dibuat | CLA |
| 2026-08-02 | Tambah Orchestrator Loop DSE↔CLX (otomatis, format STATUS/ISSUES, max_iteration, integrasi git), pisahkan alur coding vs non-coding, update peran CLU/CLE (+Design Reference & Pattern Library), tambah dua lapis approval (teknis CLX vs final RDA) | RDA |
| 2026-08-03 | §6: pemilihan model CLX & DSE berdasarkan bobot task otomatis oleh CLA (bukan manual RDA), dengan kriteria berat/ringan eksplisit + tabel model | CLA (atas instruksi RDA) |
| 2026-08-04 | **Restrukturisasi besar** — Orchestrator Agent dihapus total (alasan: biaya token tinggi, kualitas hasil di bawah ekspektasi). §3 diganti jadi alur manual: QWE (opsional, keputusan CLA) → DSE (manual, tanpa loop) → RDA trial manual → CLX review sekali di akhir fase (bukan per-iterasi) → CLA Final Approval → RDA approve. Role baru **QWE** (Qian Wen) ditambahkan — Technical Requirements Analysis, Software Solution Architecture, Implementation Planning & Technical Specification. Role CLU/CLE diganti total jadi 2: UI/UX Design & Implementation Specialist, User Interface Design System Specialist (menggantikan 3 role lama termasuk Visual Design & Digital Asset Production, Design Reference & Pattern Library) | RDA |
| 2026-08-05 | §4 — alur review non-coding (CLU/CLE) diubah: **QWE menggantikan CLX** sebagai reviewer, berlaku untuk semua output CLU/CLE (UI-only maupun UI+backend). CLX tetap berperan di jalur UI+backend, tapi posisinya pindah ke tahap implementasi DSE (review sekali di akhir fase, bukan review wireframe/desain). §5 jalur table disesuaikan | RDA |
| 2026-08-07 | §8 diperbaiki — hapus referensi `opencode-setup/`/Orchestrator yang sudah tidak berlaku, lengkapi daftar dokumen governance aktif (RDA-Universal-Standard, TDS-QWE, TDS-DSE, TDS-CLX, TDS-CLU-CLE, config `.opencode/agents/`) | CLA |
| 2026-08-10 | Role baru **CGX** (Chantal Gaux, model ChatGPT Codex) ditambahkan — **setara CLX** (bukan backup), CLA pilih salah satu per-task, dicatat di log. Jalan di luar OpenCode (interface terpisah, seperti QWE). Format output identik CLX (STATUS/ISSUES/SEVERITY). Detail lengkap di `TDS-CGX.md` (baru, kini dihapus per 2026-08-12) | RDA |
| 2026-08-12 | **Restrukturisasi tim & tooling pasca-Fase 3:** (1) **CLX di-set OFF sementara** — keterbatasan dana RDA; (2) Role baru **GNC** (Ginevra Niccoli) ditambahkan — setara CLX/CGX, platform Antigravity, model Gemini 3.1 Pro High; reviewer aktif sekarang CGX + GNC; (3) `dse.md` tunggal diganti **`dse-pro.md`** + **`dse-flash.md`**, model dipilih via `.bat` terpisah (`dsepro.bat`/`dseflash.bat`) bukan edit manual file config; (4) `clx.md`, `TDS-CLX.md`, `TDS-CGX.md` dihapus dari `sman1ngraho/docs/` & `.opencode/agents/` (diarsipkan konsepnya di log ini, bukan hilang total dari histori); (5) folder `tasks/` dan `reports/` diarsipkan ke vault (Fase 2 & 3 closed), `reports/` restart isi kosong tapi **penomoran lanjut dari `report29.md`**, bukan reset; (6) `01-routing-decision.md` dipindah permanen ke vault Obsidian. Trigger: Fase 3 resmi CLOSED (7 ronde review CGX untuk subsistem HeroSlideConfig, APPROVED final) | RDA |
