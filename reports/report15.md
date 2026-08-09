# Report 15 — DSE (Delia Tse)

**Tanggal:** 2026-08-09
**Branch:** `feature/fase3-database-crud`
**Commit:** `b88a27f`

---

## Task: Revisi 2 Resource — Agenda (klarifikasi) & Announcement (field narrow + published_at)

### 1. AgendaResource — Verifikasi Klarifikasi

**Status:** ✅ Sudah sesuai dari revisi sebelumnya — tidak ada perubahan.

HANYA field `event_date` (Tanggal) yang `columnSpan(4)`. Semua field lain (`title`, `location`, `description`) tetap `columnSpanFull()` baris sendiri.

---

### 2. AnnouncementResource — Publish Date + Expired Date Narrow

#### Temuan: `published_at` SUDAH ada di form sebelumnya
- Sebelum revisi: `published_at` ada di **Status section** sebagai `DateTimePicker` dengan label **"Tanggal Terbit"**
- Posisinya tidak dekat dengan `expired_at` (Kadaluarsa) — terpisah section berbeda

#### Perubahan:

| # | Perubahan | Detail |
|---|---|---|
| 1 | Tambah import | `Use Filament\Schemas\Components\Grid;` |
| 2 | `published_at` dipindahkan | Dari Status section → Informasi Pengumuman section |
| 3 | Label diubah | "Tanggal Terbit" → **"Tanggal Mulai"** |
| 4 | Tipe diubah | `DateTimePicker` → `DatePicker` (use case tanggal, bukan datetime) |
| 5 | `published_at` + `expired_at` sejajar | Dibungkus `Grid::make(12)`, masing-masing `columnSpan(3)` |
| 6 | `title` full-width | Tambah `->columnSpanFull()` |
| 7 | `body` tetap full-width | `->columnSpanFull()` tidak berubah |
| 8 | Status section | `published_at` dihapus, `columns` dari 3 → 2 |

#### Layout akhir section `Informasi Pengumuman`:
```
┌──────────────────────────────────────────────────────┐
│ Informasi Pengumuman                                 │
├──────────────────────────────────────────────────────┤
│ Judul          [__________________________________]  │ ← full-width
│ Isi            [__________________________________]  │ ← full-width (RichEditor)
│                [__________________________________]  │
│ Tanggal Mulai [__________]  Kadaluarsa [__________]  │ ← sejajar, columnSpan(3)
└──────────────────────────────────────────────────────┘
┌────────────────────┬────────────────────┐
│ Status             │ Aktif              │  ← columns(2)
└────────────────────┴────────────────────┘
```

---

### 3. AgendaResource — Tidak Ada Perubahan

```
┌──────────────────────────────────────────────────────┐
│ Informasi Agenda                                     │
├──────────────────────────────────────────────────────┤
│ Judul     [______________________________________]   │ ← full-width
│ Tanggal   [__________]                               │ ← columnSpan(4), narrow
│ Lokasi    [______________________________________]   │ ← full-width
│ Deskripsi [______________________________________]   │ ← full-width
└──────────────────────────────────────────────────────┘
```

---

## Status

✅ **Selesai.** 1 file diubah (`AnnouncementResource.php`), 0 file baru. Commit `b88a27f` di branch `feature/fase3-database-crud`, siap di-push manual oleh RDA.
