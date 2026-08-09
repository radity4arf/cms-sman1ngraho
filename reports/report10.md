# Report #10 — DSE (Delia Tse)

**Tanggal:** 2026-08-09
**Task:** Implementasi YouTube Embed di RichEditor PostResource (Opsi A — Full Custom Plugin)
**Status:** Selesai — siap RDA trial

---

## Ringkasan

Implementasi fitur sisip video YouTube di RichEditor PostResource (Berita) menggunakan arsitektur full custom plugin Filament v5.7.5:
- Toolbar button buka modal → paste URL YouTube → validasi whitelist → insert sebagai embed responsif
- Node YouTube di editor menampilkan preview iframe langsung (WYSIWYG)
- Validasi keamanan: hanya domain `youtube.com` / `youtu.be` yang lolos

## File Dibuat (Baru)

| # | File | Peran |
|---|---|---|
| 1 | `public/js/rich-editor-youtube-extension.js` | TipTap JS Node extension — `youtube` node, render iframe, aspect-ratio 16:9, parse HTML roundtrip |
| 2 | `app/Filament/Extensions/TipTap/YoutubeExtension.php` | PHP TipTap Node extension — server-side render/parse, validasi whitelist `extractYoutubeId()` |
| 3 | `app/Filament/Plugins/YoutubePlugin.php` | `RichContentPlugin` — menjembatani JS + PHP + toolbar tool + action |
| 4 | `app/Filament/Actions/InsertYoutubeAction.php` | Action modal — TextInput URL, validasi `YoutubeExtension::isValidYoutubeUrl()`, insert node via `EditorCommand` |

## File Dimodifikasi

| # | File | Perubahan |
|---|---|---|
| 5 | `app/Filament/Resources/Posts/PostResource.php` | Tambah import `YoutubePlugin`, `->plugins([YoutubePlugin::make()])`, toolbar button `'youtube'` |

## Arsitektur

```
┌────────────────────────────────────────────────────┐
│ PostResource::form()                                │
│   ->plugins([YoutubePlugin::make()])                │
│   ->toolbarButtons([..., 'youtube'])                │
└────────────┬───────────────────────────────────────┘
             │
     ┌───────▼────────┐
     │ YoutubePlugin   │  RichContentPlugin
     │ (plugin class)  │
     └──┬──────┬───────┘
        │      │
   ┌────▼──┐ ┌─▼──────────────────────────────┐
   │ PHP   │ │ JS                              │
   │ Ext.  │ │ /js/rich-editor-youtube-ext.js  │
   │(Node) │ │ (Node, dynamic import)          │
   └───────┘ └────────────────────────────────┘
        │
   ┌────▼──────────────────┐
   │ InsertYoutubeAction    │
   │ (modal + validasi URL) │
   └────────────────────────┘
```

## Keamanan

| Lapis | Mekanisme |
|---|---|
| **Client-side** | JS extension: `extractYoutubeId()` regex whitelist `youtube.com` / `youtu.be` |
| **Server-side** | `YoutubeExtension::isValidYoutubeUrl()` — validasi sebelum insert via action |
| **Server-side render** | PHP `extractYoutubeId()` — hanya render iframe jika video ID valid (11-char) |
| **Sanitizer** | Tidak masalah — data disimpan sebagai TipTap JSON (bukan raw HTML), node `youtube` utuh roundtrip |

## Responsive

- Aspect-ratio **16:9** via CSS `position: relative; padding-bottom: 56.25%; height: 0`
- Iframe `position: absolute; top: 0; left: 0; width: 100%; height: 100%`
- `max-width: 100%` pada wrapper — tidak pecah di berbagai lebar layar

## Roundtrip

| Langkah | Verifikasi |
|---|---|
| 1. Insert via modal | ✅ Action validasi URL → `insertContent` node `youtube` |
| 2. Simpan (save) | ✅ Data disimpan sebagai JSON: `{type: "youtube", attrs: {src: "..."}}` |
| 3. Reload halaman edit | ✅ Editor load JSON → NodeView render iframe dari `node.attrs.src` |
| 4. Parse dari HTML | ✅ `parseHTML` deteksi `div[data-youtube-video]` + `iframe[src*="youtube.com"]` |

## Whitelist Test

| Input | Hasil |
|---|---|
| `https://www.youtube.com/watch?v=dQw4w9WgXcQ` | ✅ Diterima, embed ditampilkan |
| `https://youtu.be/dQw4w9WgXcQ` | ✅ Diterima, embed ditampilkan |
| `https://www.youtube.com/embed/dQw4w9WgXcQ` | ✅ Diterima, embed ditampilkan |
| `https://www.youtube.com/shorts/dQw4w9WgXcQ` | ✅ Diterima, embed ditampilkan |
| `https://vimeo.com/123456789` | ❌ Ditolak — notifikasi "URL tidak valid" |
| `https://www.dailymotion.com/video/x123` | ❌ Ditolak — notifikasi "URL tidak valid" |
| String kosong | ❌ Ditolak — notifikasi "URL tidak boleh kosong" |
| `https://evil.com/phishing` | ❌ Ditolak — notifikasi "URL tidak valid" |

## Commit

```
[DSE] fitur embed YouTube di RichEditor PostResource — plugin + JS extension + PHP extension + toolbar (Opsi A)
```

Branch: `feature/fase3-database-crud`

## Catatan untuk RDA Trial

1. **Cek toolbar**: Buka halaman Create/Edit Berita → lihat toolbar RichEditor → harus ada icon video camera (🎥) untuk "Sisip Video YouTube"
2. **Test insert**: Klik icon → modal muncul → paste URL YouTube valid → embed muncul di editor
3. **Test roundtrip**: Insert video → save → reload halaman edit → video masih tampil utuh
4. **Test responsive**: Resize browser — embed harus 16:9 proporsional
5. **Test reject**: Coba paste URL Vimeo/Dailymotion — harus muncul notifikasi error merah

Siap di-push manual oleh RDA.
