# Report #11 — DSE (Delia Tse)

**Tanggal:** 2026-08-09
**Task:** Bug fix — YouTube embed hilang setelah save (roundtrip fail)
**Status:** Selesai — terverifikasi PASS

---

## Investigasi Forensik

### Analisis Data Flow

```
Save: Editor JSON → Livewire → RichEditorStateCast::get() → getHtml() → DB (HTML)
Load: DB (HTML) → RichEditorStateCast::set() → setContent(HTML) → getDocument() → Editor JSON
```

File `isJson()` return `false` (Post model tidak implement `HasRichContent`), sehingga content disimpan sebagai HTML, bukan JSON. Ini membuat roundtrip **parseHTML sangat kritis** — kalau parse gagal, node hilang.

### Root Cause #1: `addAttributes()` TIDAK ADA di PHP Extension

**Ditemukan di:** `vendor/ueberdosis/tiptap-php/src/Core/DOMParser.php:359-369`

```php
foreach ($this->schema->getAttributeConfigurations($class) as $attribute => $configuration) {
    if (isset($configuration['parseHTML'])) {
        $value = $configuration['parseHTML']($DOMNode);
    } else {
        $value = $DOMNode->getAttribute($attribute) ?: null;
    }
    // ...
}
```

Parser HANYA membaca atribut yang **didefinisikan di `addAttributes()`**. Karena `YoutubeExtension` extend `Tiptap\Core\Node` yang return `[]` empty array — maka saat parse HTML dari database, **node YouTube tercipta tanpa atribut `src` dan `start`**. Node kosong ini tidak bisa dirender, sehingga hilang.

**Fix:** Tambah method `addAttributes()` dengan `parseHTML` function untuk `src` dan `start`:
- `src`: cek `data-youtube-src` di div wrapper → cek iframe child → cek DOMNode sendiri kalau iframe
- `start`: cek `data-youtube-start` → cek parameter `?start=` di URL iframe

Juga tambah `renderHTML` di `addAttributes` untuk konsistensi output `data-youtube-src` (matching JS side behavior).

### Root Cause #2: `array_filter()` Strip Empty String Attributes

**Ditemukan di:** `vendor/ueberdosis/tiptap-php/src/Utils/HTML.php:63`

```php
foreach (array_filter($attrs) as $name => $value) {
```

`array_filter()` tanpa callback menghapus semua nilai falsy — termasuk empty string `''`. Atribut `data-youtube-video=""` di-strip dari HTML output. Akibatnya `parseHTML` rule `div[data-youtube-video]` tidak match saat load dari DB → node tidak terdeteksi → konten kosong.

**Fix:** Ubah value dari `''` ke `'true'` (truthy, tidak di-filter):
- `'data-youtube-video' => 'true'` (PHP dan JS extension)
- CSS selector `div[data-youtube-video]` tetap match karena tidak bergantung pada value

## Perubahan

| File | Perubahan |
|---|---|
| `app/Filament/Extensions/TipTap/YoutubeExtension.php` | **+86 lines:** Tambah `addAttributes()` dengan `parseHTML` + `renderHTML` untuk `src` dan `start`. Fix: `data-youtube-video` → `'true'` |
| `public/js/rich-editor-youtube-extension.js` | Fix: `data-youtube-video` → `'true'` (renderHTML + NodeView) |

## Test Verifikasi (PHP Roundtrip)

```
TEST 1: parseHTML from saved HTML
  Node type: youtube
  Node attrs: {"src":"...", "start":0}
  Has src attribute: YES (PASS!) ✅

TEST 2: full roundtrip JSON -> HTML -> JSON
  Input:  {"type":"youtube","attrs":{"src":"https://youtu.be/dQw4w9WgXcQ"}}
  HTML:   <div data-youtube-video="true" ...><iframe ...></iframe></div>
  Re-parse: {"type":"youtube","attrs":{"src":"...","start":0}}
  Roundtrip: PASS! ✅
```

## Commit

```
[DSE] [THECHNOLOGY-FIX] roundtrip YouTube embed — addAttributes() + fix empty data attr stripped by array_filter()
```

Branch: `feature/fase3-database-crud`

## Catatan

- Dua bug ditemukan dan difix: (1) missing `addAttributes()` → attributes not parsed, (2) `array_filter()` strip empty string → `data-youtube-video` hilang dari HTML
- Kedua bug menghasilkan gejala yang sama: YouTube node hilang setelah save → reload
- Fix diverifikasi via unit test PHP roundtrip di luar Laravel
- Siap di-push manual oleh RDA
