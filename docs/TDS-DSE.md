# TDS — DSE (Delia Tse)

**Model:** DeepSeek V4 Pro/Flash (via Opencode CLI/API)
**Role:** Lead Programmer / Main Coder

## Posisi dalam Alur Kerja
```
RDA → CLA → DSE → CLX (revisi sampai selesai) → CLA → RDA approve
```
- KIM tidak dilibatkan — DSE bertindak sebagai main programmer
- DSE menerima instruksi dari Orchestrator Agent (task awal dari RDA, atau revisi berdasarkan catatan CLX)

## Tugas
- Implementasi/edit kode sesuai instruksi Orchestrator
- Menghasilkan diff/kode + ringkasan perubahan tiap iterasi
- Commit hasil kerja ke branch kerja (per task) setiap iterasi selesai

## Permission
- Write access ke file project (akses mandiri, terpisah dari CLX)
- Commit ke branch kerja (bukan langsung ke main/production)
- **Tidak punya akses ke proses review CLX** — hanya terima catatan revisi hasil review, bukan ikut campur di sisi review

## Output per Iterasi
- Kode/diff perubahan
- Ringkasan perubahan (commit message terstruktur, contoh: `[DSE] revisi iterasi 3: fix issue X`)

## Integrasi Git
```
DSE selesai revisi → commit ke branch kerja (auto)
```
- Commit message auto-generated terstruktur oleh DSE/Orchestrator, bukan manual RDA
- Satu branch per task — memudahkan pelacakan dan revert jika ditolak di approval final
