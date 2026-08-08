# Laporan Enhancement — RichEditor PostResource (Berita)

**Tanggal:** 08 Agustus 2026
**Branch:** `feature/fase3-database-crud`
**Scope:** Hanya PostResource (Berita) — entitas lain tidak terpengaruh

---

## Perubahan

### 1. Min-height RichEditor
RichEditor "Isi Berita" sekarang punya `min-height: 500px` via `->extraInputAttributes(['style' => 'min-height: 500px;'])`. Sebelumnya tinggi default terlalu kecil — tidak nyaman untuk menulis artikel panjang.

### 2. File Attachment Inline (Gambar di Dalam Konten)
Mengaktifkan fitur sisip gambar langsung di dalam konten RichEditor via toolbar button `attachFiles` (sudah ada di default toolbar Filament v5.7 — tidak perlu tambah manual).

**Konfigurasi:**
```php
->fileAttachmentsDisk('public')
->fileAttachmentsDirectory('posts/content')
->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
->fileAttachmentsMaxSize(10240)  // 10MB
```

| Parameter | Nilai | Keterangan |
|---|---|---|
| Disk | `public` | Storage lokal, konsisten dengan RT-11 |
| Directory | `posts/content` | Subfolder di `storage/app/public/posts/content/` |
| Tipe file | jpg, png, webp | Sama dengan validasi gambar lain (RT-11) |
| Maks size | 10 MB | 10240 KB |

### 3. Featured Image TETAP Terpisah
Section "Media" → `SpatieMediaLibraryFileUpload::make('featured_image')` **tidak diubah sama sekali**. Berfungsi sebagai gambar utama/thumbnail di atas artikel. Gambar yang disisipkan via RichEditor adalah TAMBAHAN — bebas diposisikan inline dalam konten oleh penulis.

---

## Arsitektur Double-Layer Image

```
┌─────────────────────────────────────────┐
│  featured_image (SpatieMediaLibrary)    │  ← thumbnail utama
│  collection: featured_image             │     upload terpisah via form
│  posisi: default atas artikel           │
├─────────────────────────────────────────┤
│  RichEditor body                        │
│  ┌─────────────────────────────────┐    │
│  │ Teks artikel...                 │    │
│  │ ![inline image](storage/...)    │    │  ← gambar inline (file attachment)
│  │ Teks lanjutan...                │    │     disisipkan via toolbar RichEditor
│  │ ![inline image 2](storage/...)  │    │     disimpan di posts/content/
│  └─────────────────────────────────┘    │
└─────────────────────────────────────────┘
```

---

## Test

| Halaman | Status |
|---|---|
| List Berita (`/admin/posts`) | ✅ OK |
| Create Berita (`/admin/posts/create`) | ✅ OK — form render, RichEditor 500px |
| Edit Berita (`/admin/posts/{id}/edit`) | ✅ (form sama dengan Create) |

---

## Commit

`c12a319` — `[THECHNOLOGY-MOD] RichEditor PostResource — tambah min-height 500px + file attachment inline (disk public, jpg/png/webp, max 10MB)`

---

**Dibuat oleh:** DSE (Delia Tse)
