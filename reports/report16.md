# Report 16 — DSE (Delia Tse)

**Tanggal:** 2026-08-09
**Branch:** `feature/fase3-database-crud`
**Commit:** `bf3ee48`

---

## Task: Fix Diskrepansi — `columnSpan(4)` di AgendaResource Tidak Berefek Visual

### Masalah
Screenshot aktual `/admin/agendas/create` menunjukkan field "Tanggal" **MASIH full-width** (sama lebar dengan Judul/Lokasi/Deskripsi), padahal report14.md/report15.md mengklaim sudah diubah ke `->columnSpan(4)` (narrow).

---

### Investigasi Root Cause

| Langkah | Hasil |
|---|---|
| **1. Cek isi file** | ✅ `->columnSpan(4)` benar ADA di line 54 — kode tidak hilang |
| **2. Cek git log** | ✅ Tidak ada commit overwrite setelah `f552221` (commit fix narrow). Commit terakhir yang menyentuh file ini adalah fix tersebut. |
| **3. Analisis Grid context** | ❌ **INILAH ROOT CAUSE:** `columnSpan(4)` ditempel langsung pada field di dalam Section yang **tidak punya `columns()` explicit**. Section tanpa `columns()` menggunakan layout 1-kolom default — `columnSpan(4)` tidak punya grid 12-kolom untuk diproporsikan, sehingga field tetap melebar penuh. |
| **4. Clear cache** | ✅ `php artisan optimize:clear` (termasuk `filament` cache) |

#### Bukti pembanding — resource yang BERHASIL:
- **AchievementResource** (Tahun+Urutan) — pakai `Grid::make(12)->schema([...])` wrapper → **user tidak complain**
- **AnnouncementResource** (Tanggal Mulai+Kadaluarsa) — pakai `Grid::make(12)->schema([...])` wrapper → **user tidak complain**

Keduanya memberikan grid 12-kolom eksplisit sehingga `columnSpan(N)` proporsional. AgendaResource **tidak** pakai wrapper — inilah penyebab diskrepansi.

---

### Fix

**File:** `app/Filament/Resources/Agendas/AgendaResource.php`

| Sebelum (tidak berefek) | Sesudah (proporsional) |
|---|---|
| `DatePicker::make('event_date')->...->columnSpan(4)` langsung di Section | Dibungkus `Grid::make(12)->schema([ DatePicker::make('event_date')->...->columnSpan(4) ])` |

```php
// Sebelum:
Section::make('Informasi Agenda')->schema([
    TextInput::make('title')->...->columnSpanFull(),
    DatePicker::make('event_date')->...->columnSpan(4),  // ← TIDAK berefek
    TextInput::make('location')->...->columnSpanFull(),
    ...
])->columnSpanFull(),

// Sesudah:
Section::make('Informasi Agenda')->schema([
    TextInput::make('title')->...->columnSpanFull(),
    Grid::make(12)->schema([                              // ← Grid wrapper
        DatePicker::make('event_date')->...->columnSpan(4), // ← sekarang proporsional (4/12)
    ]),
    TextInput::make('location')->...->columnSpanFull(),
    ...
])->columnSpanFull(),
```

Tambahan: import `use Filament\Schemas\Components\Grid;`.

---

### Layout Hasil Akhir

```
┌──────────────────────────────────────────────────────┐
│ Informasi Agenda                                     │
├──────────────────────────────────────────────────────┤
│ Judul     [______________________________________]   │ ← full-width
│ Tanggal   [__________]                               │ ← NARROW (4/12 grid) ✅
│ Lokasi    [______________________________________]   │ ← full-width
│ Deskripsi [______________________________________]   │ ← full-width
└──────────────────────────────────────────────────────┘
```

---

## Status

✅ **Selesai.** Root cause teridentifikasi dan difix. Commit `bf3ee48` di branch `feature/fase3-database-crud`, siap di-push manual oleh RDA. Cache sudah di-clear (`php artisan optimize:clear`).
