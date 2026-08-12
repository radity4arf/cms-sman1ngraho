---
project: CMS Portal SMAN1 Ngraho
doc: TDS-DSE.md
status: active
created: 2026-08-04
updated: 2026-08-12
---

# TDS — DSE (Delia Tse)

**Model:** DeepSeek V4 Pro/Flash (via Opencode CLI, mode primary — dijalankan langsung lewat `dsepro.bat` untuk task Berat, atau `dseflash.bat` untuk task Ringan; masing-masing memuat `.opencode/agents/dse-pro.md` / `dse-flash.md`)
**Status:** APPROVED v1.1 — disahkan RDA
**Versi:** v1.1
**Final Authority:** CLA
**Role:** Lead Programmer / Main Coder

## Posisi dalam Alur Kerja
```
CLA dispatch task ke DSE (langsung — tidak ada Orchestrator/perantara otomatis)
        ↓
   [OPSIONAL — keputusan CLA per-task]
   QWE — spec pre-DSE (kalau dilibatkan)
        ↓
DSE — implementasi kode sesuai instruksi CLA (atau spec QWE)
        ↓
RDA — trial manual (bisa di-skip atas keputusan eksplisit RDA per-task)
        ↓
   Ada masalah → revisi manual: RDA/CLA instruksikan DSE langsung, ulangi trial
   Trial OK (atau di-skip) → lanjut ke Reviewer aktif
        ↓
Reviewer (CLX / CGX / GNC — CLA pilih dari yang aktif) — review sekali di akhir
fase → CLA Final Approval → RDA approve
```
- DSE bertindak sebagai main programmer
- DSE menerima instruksi langsung dari CLA/RDA (task awal, atau spec dari QWE kalau dilibatkan) — **tidak ada Orchestrator**
- Revisi (kalau ada, dari trial RDA atau review reviewer) diinstruksikan manual oleh CLA/RDA — bukan loop otomatis
- **Catatan (2026-08-12):** CLX sedang OFF sementara (keterbatasan dana RDA). Reviewer aktif saat ini: CGX dan GNC. CLA memilih salah satu per-task, dicatat di `01-routing-decision.md`

## Tugas
- Implementasi/edit kode sesuai instruksi CLA/RDA (atau spec QWE)
- Menghasilkan diff/kode + ringkasan perubahan
- Commit hasil kerja ke branch kerja (per task)

## Permission
- Write access ke file project (akses mandiri, terpisah dari reviewer)
- Commit ke branch kerja (bukan langsung ke main/production)
- **Tidak melakukan `git push`** — push ke remote GitHub dilakukan manual oleh RDA, di luar sesi DSE
- **Tidak punya akses ke proses review reviewer (CLX/CGX/GNC)** — hanya terima catatan revisi hasil review (diteruskan lewat CLA), bukan ikut campur di sisi review

## Output
- Kode/diff perubahan
- Ringkasan perubahan (commit message terstruktur, contoh: `[DSE] fix issue validasi form user`)

## Integrasi Git
```
DSE selesai kerja → commit ke branch kerja
(push manual oleh RDA, terpisah dari sesi DSE)
```
- Commit message terstruktur oleh DSE, bukan auto-generated Orchestrator (sudah dihapus)
- Satu branch per task — memudahkan pelacakan dan revert jika ditolak di approval final

## Penomoran Laporan (`reports/`)

**Catatan (2026-08-12):** folder `reports/` diarsipkan ke vault (28 laporan Fase 2 & 3). Laporan berikutnya **lanjut dari `report29.md`** — bukan reset ke `report.md`. Lihat `00-Workflow-Team-CMS-Project.md` §8 untuk detail lengkap.

---

## Revision Log
- v0.1 (2026-08-11) — Draft disusun mengikuti struktur governance TDS (frontmatter & header lengkap: Status/Versi/Final Authority, diagram alur kerja disamakan dengan `00-Workflow-Team-CMS-Project.md` §3 — CLA dispatch langsung ke DSE tanpa RDA di depan, ditambah cabang revisi manual dari RDA trial), belum disahkan RDA.
- v1.0 (2026-08-11) — Disahkan RDA. Status DRAFT v0.1 → APPROVED v1.0, versi dikunci.
- v1.1 (2026-08-12) — Update pasca-restrukturisasi tim: referensi `run-dse.bat` diganti `dsepro.bat`/`dseflash.bat` (config `dse.md` tunggal diganti `dse-pro.md`/`dse-flash.md`), referensi "CLX" digeneralisasi jadi "Reviewer aktif (CLX/CGX/GNC)" mengikuti CLX yang di-set OFF sementara, tambah catatan override penomoran report mulai `report29.md` | CLA
