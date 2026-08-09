# Report 14 — DSE (Delia Tse)

**Tanggal:** 2026-08-09
**Branch:** `feature/fase3-database-crud`
**Commit:** `f552221`

---

## Task: Revisi Estetika — Field Pendek Narrow, Bukan Full-Width

Menyesuaikan ukuran lebar field yang isinya pendek (Tahun, Urutan, Tanggal) agar tidak full-width — proporsional terhadap panjang konten, tidak buang ruang kosong.

---

## File yang Diubah

### 1. `app/Filament/Resources/Achievements/AchievementResource.php`

**Perubahan:**
- Tambah import `use Filament\Schemas\Components\Grid;`
- Field `year` (Tahun) & `sort_order` (Urutan) — dikeluarkan dari `columnSpanFull()`, dibungkus dalam `Grid::make(12)->schema([...])`, masing-masing pakai `->columnSpan(2)` — **sejajar dalam 1 baris** (field pendek, ~2/12 grid width)
- Field lain (`title`, `name`, `description`) **tetap** `columnSpanFull()` sendiri-sendiri

**Layout akhir section `Informasi Prestasi`:**
```
┌──────────────────────────────────────────────┐
│ Informasi Prestasi                           │
├──────────────────────────────────────────────┤
│ Judul / Kejuaraan  [______________________]  │ ← full-width
│ Nama Siswa / Tim   [______________________]  │ ← full-width
│ Tahun [____]  Urutan [___]                   │ ← sejajar, columnSpan(2) masing2
│ Deskripsi          [______________________]  │ ← full-width (textarea)
│                   [______________________]  │
│                   [______________________]  │
└──────────────────────────────────────────────┘
```

### 2. `app/Filament/Resources/Agendas/AgendaResource.php`

**Perubahan:**
- Field `event_date` (Tanggal) — ganti dari `->columnSpanFull()` ke `->columnSpan(4)` — date picker tidak perlu full-width
- Field lain (`title`, `location`, `description`) **tetap** `columnSpanFull()` sendiri-sendiri

**Layout akhir section `Informasi Agenda`:**
```
┌──────────────────────────────────────────────┐
│ Informasi Agenda                             │
├──────────────────────────────────────────────┤
│ Judul     [______________________________]   │ ← full-width
│ Tanggal   [__________]                       │ ← columnSpan(4), narrow
│ Lokasi    [______________________________]   │ ← full-width
│ Deskripsi [______________________________]   │ ← full-width (textarea)
│           [______________________________]   │
│           [______________________________]   │
└──────────────────────────────────────────────┘
```

---

## Rangkuman Teknis

| Resource | Field | Sebelum | Sesudah |
|---|---|---|---|
| AchievementResource | `year` | `columnSpanFull()` | `Grid::make(12)` + `columnSpan(2)` |
| AchievementResource | `sort_order` | `columnSpanFull()` | `Grid::make(12)` + `columnSpan(2)` |
| AgendaResource | `event_date` | `columnSpanFull()` | `columnSpan(4)` |

- Grid default Filament = 12 kolom, `columnSpan(2)` ≈ 16.7% lebar, `columnSpan(4)` ≈ 33% lebar
- `year` + `sort_order` sejajar 1 baris dalam `Grid::make(12)` wrapper
- Section `Status` & resource lain **tidak berubah**

## Status

✅ **Selesai.** Commit tersimpan di branch `feature/fase3-database-crud`, siap di-push manual oleh RDA.
