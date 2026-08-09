# Report #9 — DSE (Delia Tse)

**Tanggal:** 2026-08-09
**Task:** Riset pendekatan YouTube embed di RichEditor PostResource
**Status:** Menunggu keputusan RDA/CLA

---

## Ringkasan Riset

**Environment:**
- Filament v5.7.5 + tiptap-php 2.1.1
- npm packages TIDAK terinstall (UNMET DEPENDENCY) — tidak bisa pakai npm
- Tidak ada built-in YouTube extension di codebase

**Arsitektur Extension Filament RichEditor (v5):**
1. **PHP Extension** — class di namespace `Tiptap\Core\Node` atau `Tiptap\Core\Mark`, dipakai untuk server-side rendering (parse HTML → JSON dan sebaliknya)
2. **JS Extension** — module JS (ESM) yang di-load via dynamic `import(url)`, diekspos via `getTipTapJsExtensions()` → array URL string
3. **RichContentPlugin** — interface yang menjembatani PHP + JS extension + toolbar tools + actions
4. **RichEditorTool** — toolbar button, bisa pakai `jsHandler` (simple command) atau `action()` (modal-based)
5. **Action** (seperti `AttachFilesAction`, `LinkAction`) — class action dengan modal form untuk input kompleks

**Temuan Kunci:**
- `window.FilamentRichEditor.tiptap` sudah di-expose secara global (oleh Filament), berisi `core`, `pmState`, `pmView`, `pmModel` — custom JS extension bisa pakai referensi ini tanpa perlu npm/bundler
- JS file custom extension bisa ditempatkan di `public/js/` dan direferensi via URL relatif seperti `/js/rich-editor-youtube-extension.js`

---

## Opsi Implementasi

### Opsi A: Full Custom Plugin — Toolbar Button + Modal + PHP + JS Extension
**Cakupan:**
1. Buat JS extension file: `public/js/rich-editor-youtube-extension.js`
   - TipTap Node extension bernama `youtube`
   - Render sebagai `<div>` dengan aspect-ratio wrapper + `<iframe>` YouTube
   - Parse HTML: deteksi `<iframe>` YouTube → rekonstruksi node
   - Validasi: hanya domain `youtube.com`, `youtu.be` (whitelist)
2. Buat PHP extension class: `app/Filament/Extensions/TipTap/YoutubeExtension.php`
   - Extend `Tiptap\Core\Node`
   - Parse/render iframe YouTube dari/ke JSON
3. Buat Plugin class: `app/Filament/Plugins/YoutubePlugin.php`
   - Implement `RichContentPlugin`
   - Register PHP extension + JS extension URL + toolbar tool
4. Buat Action class: `app/Filament/Actions/InsertYoutubeAction.php`
   - Modal dengan TextInput untuk paste URL YouTube
   - Validasi URL (regex youtube.com / youtu.be)
   - Insert node `youtube` ke editor via `EditorCommand`
5. Daftarkan plugin di `PostResource::form()` via `->plugins([YoutubePlugin::make()])`
6. Tambah `youtube` ke `->toolbarButtons()`

**Kelebihan:**
- UX terbaik: toolbar button jelas, modal intuitif
- Konsisten dengan pattern Filament (mirip AttachFiles/Link)
- Validasi keamanan terpusat di PHP (server-side whitelist)
- Full control atas rendering (responsive wrapper, aspect-ratio)

**Kekurangan:**
- Paling banyak kode (5-6 file baru)
- JS file harus di-maintain terpisah
- Perlu testing menyeluruh untuk parse/render roundtrip

### Opsi B: JS-Only Extension + Paste-to-Embed (tanpa toolbar button)
**Cakupan:**
1. JS extension saja (no PHP extension — gunakan Raw HTML node TipTap)
2. User paste URL YouTube → extension deteksi via `addPasteRules` → auto konversi jadi embed
3. Tidak ada toolbar button khusus

**Kelebihan:**
- Kode paling sedikit (1 file JS + 1 plugin class untuk register JS URL)
- Natural UX: paste link langsung jadi embed

**Kekurangan:**
- Tidak ada toolbar button → user harus tahu bisa paste URL
- Tanpa PHP extension, roundtrip save/load bisa bermasalah (iframe bisa di-strip oleh sanitizer)
- Kurang eksplisit/tidak ada validasi server-side

### Opsi C: Tidak pakai extension editor — render di frontend saja
**Cakupan:**
1. User cukup tulis/paste URL YouTube sebagai teks biasa di editor
2. Frontend (Blade view website) yang mendeteksi URL YouTube dan me-render sebagai embed iframe
3. Tidak perlu modifikasi RichEditor sama sekali

**Kelebihan:**
- Zero perubahan di Filament admin
- Paling simpel

**Kekurangan:**
- Tidak ada preview embed di editor (WYSIWYG hilang)
- User tidak bisa atur posisi/ukuran embed
- Rentan XSS kalau render di frontend tidak hati-hati
- Bukan solusi "di dalam editor" seperti yang diminta

---

## Rekomendasi DSE

**Opsi A (Full Custom Plugin)** — karena:
1. User minta "posisi bebas di mana saja dalam konten" → perlu Node extension (bukan Mark/link biasa)
2. User minta "whitelist domain youtube.com/youtu.be" → perlu validasi server-side (PHP extension)
3. User minta "responsive" → perlu wrapper aspect-ratio yang dikontrol di render
4. Opsi B riskan karena tanpa PHP extension, sanitizer bisa strip iframe saat save
5. Opsi C tidak memenuhi requirement "di dalam editor"

---

**Menunggu keputusan RDA/CLA — opsi mana yang disetujui?**
