---
title: RDA Universal Standard
version: 1.1
status: approved
scope: agnostic (semua jenis project — software & course/materi ajar)
created: 2026-07-27
updated: 2026-08-04
approved: 2026-08-03
changelog:
  - "1.1 (2026-07-30): tambah Stack Decision Principle, netralkan asumsi Laravel-spesifik jadi conditional, tambah Deployment Standard (Universal Core #8), tambah field versioning di File Header Standard — disahkan RDA"
  - "1.1-patch (2026-08-03): merge 3 klarifikasi tertunda — (1) tambah field Versi di baris status penutup dokumen, (2) §2 Documentation Standard: klausul course non-overlap Module A, (3) §3 Review Checklist: PIC ditambah CGA untuk Edtheria/course — diproses CLA atas instruksi RDA"
  - "1.1-patch2 (2026-08-03): (1) §7 Version Control Standard — Git wajib mutlak untuk semua project & jadi fundamental step per-perubahan (bukan lagi kondisional/di-akhir), (2) §A.8 Comment Standard — tambah standar tag atribusi [THECHNOLOGY-{ACTION}-{ROLE}] (ACTION: CRE/MOD/FIX/DEL) untuk penanda aksi + siapa yang mengerjakan perubahan kode signifikan — diproses CLA atas instruksi RDA"
  - "1.1-patch3 (2026-08-04): §7 Version Control Standard — tambah kewajiban PR minimal 1 approval sebelum merge ke branch utama, plus klausul exception solo-operator (required approvals boleh 0 kalau tidak ada reviewer independen, wajib dicatat eksplisit & diaktifkan ulang begitu ada kolaborator) — diproses CLA, menindaklanjuti exception yang dicatat CLI di project CMS SMAN1 Ngraho"
  - "1.1-patch4 (2026-08-04): §A.8.1 Tag Atribusi — format diubah dari [THECHNOLOGY-{ACTION}-{ROLE}] jadi [THECHNOLOGY-{ACTION}] (hapus identitas persona/role AI dari tag kode) — diproses CLA atas instruksi RDA"
---

# RDA Universal Standard

Dokumen ini berlaku **agnostik** — dipakai untuk semua jenis project di bawah RDA, baik project software (Thechnology) maupun course/materi ajar (Edtheria dan sejenisnya).

Struktur dokumen ini **2 lapis**:

- **Lapis 1 — Universal Core**: wajib dipatuhi semua project, apa pun jenisnya.
- **Lapis 2 — Domain Module**: dipilih sesuai jenis project. Satu project cukup pakai Universal Core + 1 module yang relevan, tidak perlu semua module sekaligus.

---

## Daftar Isi

**Lapis 1 — Universal Core**
1. [Naming Convention](#1-naming-convention)
2. [Documentation Standard](#2-documentation-standard)
3. [Review Checklist](#3-review-checklist)
4. [Clean Output Guideline](#4-clean-output-guideline)
5. [General Rules (Larangan)](#5-general-rules-larangan)
6. [Folder & File Structure Standard](#6-folder--file-structure-standard)
7. [Version Control Standard](#7-version-control-standard)
8. [Deployment Standard](#8-deployment-standard)

**Lapis 2 — Domain Module**
- [Module A — Software Development](#module-a--software-development)
- [Module B — Course / Materi Ajar](#module-b--course--materi-ajar)

- [Ringkasan Standar](#ringkasan-standar)

---

# LAPIS 1 — UNIVERSAL CORE

## 1. Naming Convention

Berlaku untuk semua jenis file & folder, apa pun isinya.

- Nama file: lowercase, kebab-case, tanpa spasi/simbol (`nama-file-project.md`, bukan `Nama File Project.md`)
- Nama folder kategori level atas boleh pakai spasi & penomoran (`03 - TUTORIAL`) mengikuti struktur vault
- Penomoran urut dipakai untuk file yang punya urutan logis (`01-`, `02-`, dst)
- Konsisten satu bahasa per elemen — kalau istilah teknis (nama tombol, command, variable), tetap pakai bahasa asli tanpa diterjemahkan

## 2. Documentation Standard

- Setiap output (kode, materi ajar, desain) yang jadi bagian dari deliverable wajib punya dokumentasi pendamping
- Dokumentasi ditulis Bahasa Indonesia, istilah teknis tetap bahasa asli
- Format dokumentasi mengikuti template yang berlaku untuk domain terkait (lihat Module A/B) — dokumen ini hanya mengatur prinsip umum, bukan template detail per jenis dokumen
- Untuk project course/materi ajar yang **tidak overlap** dengan Module A sama sekali (mis. course non-coding seperti soft skill, tanpa contoh kode di dalamnya), dokumentasi cukup mengikuti prinsip umum di Universal Core — tidak perlu memaksakan template Module A yang tidak relevan.
- Setiap dokumen wajib punya: judul, tanggal, penanggung jawab, status (draft/review/approved/outdated)

## 3. Review Checklist

Berlaku untuk semua jenis output sebelum dinyatakan selesai:

- [ ] Sudah sesuai brief/requirement awal
- [ ] Sudah dicek penanggung jawab domain (CLA untuk teknis/Thechnology, CGA untuk Edtheria/course, role terkait untuk domain lain)
- [ ] Tidak ada asumsi yang tidak diverifikasi (khusus tutorial/dokumentasi: tidak boleh mengarang langkah)
- [ ] Konsisten dengan Naming Convention & Folder Structure
- [ ] Status akhir dicatat (Approved / Revisi / Pending)

## 4. Clean Output Guideline

Prinsip umum kualitas output, berlaku baik untuk kode maupun materi ajar:

- Output harus bisa dipahami tanpa penjelasan lisan tambahan
- Hindari elemen yang tidak perlu (kode mati, konten filler, pengulangan tanpa tujuan)
- Struktur konsisten dari awal sampai akhir (heading, format, gaya penulisan)
- Setiap bagian punya tujuan yang jelas — kalau tidak menambah nilai, dihapus

## 5. General Rules (Larangan)

- Dilarang mengarang informasi/fakta yang belum diverifikasi
- Dilarang menyalin mentah dari sumber lain tanpa atribusi (khusus materi ajar: hindari plagiarisme konten sumber)
- Dilarang publish/finalisasi tanpa melalui Review Checklist
- Dilarang menyimpan data sensitif (kredensial, data pribadi) di dokumen yang disimpan permanen di Obsidian tanpa enkripsi/redaksi

## 6. Folder & File Structure Standard

- Struktur folder mengikuti kategori project (`/Thechnology/Projects/[nama]/`, `/Edtheria/Courses/[nama]/`, dst)
- File pendukung (gambar, aset) disimpan di subfolder khusus, tidak campur dengan file utama
- Subfolder aset memakai kebab-case tanpa spasi/simbol

## 7. Version Control Standard

- **Git wajib dipakai untuk semua project**, apa pun jenisnya (software maupun course/materi ajar) — bukan lagi opsional.
- Git adalah **langkah fundamental per-step**, bukan aktivitas di akhir kerja: setiap perubahan/penambahan signifikan (per task/iterasi/deliverable) wajib di-commit saat itu juga, tidak ditumpuk jadi satu commit besar di akhir.
- Setiap revisi signifikan pada dokumen/kode dicatat (siapa ubah, kapan, apa yang berubah) — commit message jelas & deskriptif, tidak commit langsung ke branch utama tanpa review.
- Dokumen yang sudah usang ditandai `status: outdated`, bukan dihapus langsung (jaga histori).
- **Pull Request wajib di-approve minimal 1 reviewer independen sebelum merge ke branch utama** (`main`/`master`), ditegakkan lewat branch protection/ruleset di platform Git (GitHub/GitLab/dst) — bukan sekadar SOP di atas kertas.
  - **Exception — solo-operator:** kalau project berjalan tanpa reviewer independen kedua (operator tunggal, platform Git tidak izinkan self-approve PR sendiri), syarat "required approvals" boleh diturunkan ke 0 **sebagai exception tercatat**, bukan default. Review teknis tetap wajib dijalankan manual (checklist CLX/setara) sebagai pengganti, dicatat di log project terkait sebagai bukti kepatuhan meski approval GitHub tidak enforced.
  - Exception ini **wajib dicatat eksplisit** di dokumen routing/log project (siapa mengesahkan, kapan, alasan) — bukan diam-diam diubah tanpa jejak.
  - Begitu ada kolaborator/reviewer independen bergabung ke project, syarat "required approvals" **wajib diaktifkan kembali** (minimal 1) — exception ini tidak boleh dibiarkan permanen tanpa peninjauan ulang.

## 8. Deployment Standard

- Alur wajib: **local → staging (kalau tersedia) → production**, tidak deploy langsung ke production dari kerja lokal tanpa lolos Review Checklist.
- Sebelum go-live, wajib ada konfirmasi: siapa yang approve (default: CLA untuk domain teknis, RDA untuk keputusan final bisnis/klien).
- Setiap deploy signifikan dicatat: tanggal, siapa yang deploy, ringkasan perubahan, status (deployed/pending revisi/rollback).
- Wajib ada backup (file & database bila ada) sebelum deploy perubahan besar, dan setelah deploy berhasil sebagai checkpoint terbaru.
- Rollback plan disiapkan untuk perubahan berisiko tinggi — minimal tahu versi/commit mana yang bisa dikembalikan kalau deploy bermasalah.
- Setelah deploy, status project diupdate (mis. "deployed, menunggu revisi client") sesuai kondisi aktual — bukan dibiarkan tidak sinkron dengan kenyataan.

---

## Kombinasi Module

Module tidak selalu berdiri sendiri — bisa digabung sesuai kebutuhan project.

Contoh: **Course PHP** (materi ajar yang mengajarkan coding) memakai:
- Universal Core (wajib)
- Module B — untuk struktur course (learning objective, urutan modul, assessment)
- Module A bagian relevan saja — mis. hanya A.1 PHP Coding Standard, dipakai untuk memastikan setiap contoh kode di dalam materi konsisten dengan standar yang sama seperti project software sungguhan

Prinsipnya: pilih Universal Core + module yang relevan dengan **isi** project, bukan sekadar kategori administratifnya.

---

# LAPIS 2 — DOMAIN MODULE

## Module A — Software Development

*Dipakai untuk project seperti Thechnology (CMS, aplikasi, sistem).*

### Stack Decision Principle

Thechnology **tidak terikat satu stack/framework tetap**. Bahasa & framework dipilih per-project sesuai kebutuhan — keputusan diambil oleh **CLA** berdasarkan karakteristik project (mis. Project A cocok Laravel, Project B butuh Python/Django, dst).

- Sub-standar A.1–A.6 di bawah berlaku **per bahasa/teknologi yang dipakai** — kalau project tidak memakai PHP, bagian A.1 tidak berlaku untuk project itu, dan seterusnya.
- Referensi ke framework tertentu (Laravel, dll) di sub-standar berikut bersifat **conditional**: berlaku *kalau* project memakai framework tersebut, bukan asumsi default untuk semua project PHP.
- Keputusan stack per-project dicatat di dokumentasi project masing-masing (lihat Documentation Standard di Universal Core).

Cakupan:
- PHP Coding Standard
- Python Coding Standard
- HTML Standard
- CSS Standard
- JavaScript Standard
- Bootstrap/Framework Frontend Standard
- File Header Standard
- Comment Standard
- Git & Version Control Standard
- Error Handling & Logging Standard
- Testing Standard
- Security Standard
- Database Standard
- Environment & Configuration Standard

> Semua sub-standar di bawah ini sudah lengkap.

### A.1 PHP Coding Standard

#### 1.1 Opening Tag
- Gunakan `<?php` penuh. **Dilarang** menggunakan short tag `<?` atau `<?=` kecuali untuk output singkat dalam template view (`<?= $var ?>` diperbolehkan khusus di file view/template).
- File yang murni berisi kode PHP (tanpa HTML di bawahnya) **tidak** perlu menutup dengan `?>` di akhir file, untuk menghindari masalah *unwanted whitespace/output*.

#### 1.2 Declare Strict Types
- Gunakan `declare(strict_types=1);` pada baris pertama setelah opening tag, khususnya untuk file class, service, dan helper.
- Untuk file view/template yang campur HTML, strict types tidak wajib.

#### 1.3 Variable Naming
- Gunakan `camelCase` untuk variabel: `$userName`, `$totalPrice`.
- Nama variabel harus deskriptif, hindari singkatan ambigu (`$d`, `$tmp`, `$x`) kecuali pada scope sangat lokal (contoh: index loop `$i`, `$j`).
- Boolean variable diberi prefix `is`, `has`, `can`: `$isActive`, `$hasPermission`.

#### 1.4 Function & Method Naming
- Gunakan `camelCase`, diawali kata kerja: `getUserById()`, `calculateTotal()`, `sendNotification()`.
- Nama function harus menjelaskan apa yang dilakukan, bukan bagaimana caranya.

#### 1.5 Class Naming
- Gunakan `PascalCase`: `UserController`, `InvoiceService`, `PaymentGateway`.
- Nama class adalah kata benda (noun), mencerminkan tanggung jawabnya (single responsibility).

#### 1.6 Constant Naming
- Gunakan `UPPER_SNAKE_CASE`: `MAX_LOGIN_ATTEMPT`, `DEFAULT_TIMEZONE`.
- Constant didefinisikan di class (`const`) atau config, **tidak** ditulis langsung sebagai magic number/string di tengah logic.

#### 1.7 Alignment & Indentation
- Indentasi menggunakan **4 spasi**, bukan tab.
- Operator assignment sejenis pada blok berdekatan boleh disejajarkan bila meningkatkan keterbacaan, namun tidak wajib dan tidak boleh dipaksakan bila merepotkan maintenance.
- Kurung kurawal `{` untuk function/class mengikuti gaya **PSR-12**: `{` di baris baru untuk class & function/method, `{` di baris yang sama untuk control structure (`if`, `for`, `foreach`, dll).

#### 1.8 Blank Line
- Satu baris kosong antar method dalam class.
- Tidak boleh ada lebih dari 1 baris kosong berturut-turut.
- Baris kosong digunakan untuk memisahkan blok logika yang berbeda dalam satu function.

#### 1.9 Return Style
- Gunakan **early return** untuk mengurangi nesting (guard clause), hindari nested `if-else` yang dalam.
- Satu function idealnya memiliki alur return yang jelas dan mudah ditelusuri.
- Tipe return harus konsisten (hindari function yang kadang return `array`, kadang `null`, kadang `false` tanpa alasan jelas) — gunakan return type declaration bila memungkinkan: `function getUser(int $id): ?User`.

### A.2 Python Coding Standard

#### 2.1 Style Guide Dasar
- Ikuti **PEP 8** sebagai baseline (indentasi, spacing, panjang baris).
- Panjang baris maksimal 79–99 karakter (sesuaikan dengan konfigurasi formatter tim, mis. Black default 88).

#### 2.2 Indentasi & Formatting
- Indentasi **4 spasi**, tidak boleh campur tab & spasi.
- Gunakan formatter otomatis (Black/Ruff format) supaya konsisten antar kontributor, tidak mengandalkan gaya manual masing-masing orang.

#### 2.3 Variable & Function Naming
- Variabel & function: `snake_case` — `total_price`, `get_user_by_id()`.
- Konstanta: `UPPER_SNAKE_CASE` — `MAX_LOGIN_ATTEMPT`.
- Class: `PascalCase` — `UserService`, `PaymentGateway`.
- Nama privat/internal diawali underscore tunggal: `_internal_helper()`.

#### 2.4 Type Hinting
- Gunakan type hint untuk function signature, terutama pada kode yang dipakai lintas modul: `def get_user(id: int) -> User | None:`.
- Type hint tidak wajib untuk script kecil sekali pakai, tapi wajib untuk kode yang jadi bagian aplikasi/reusable module.

#### 2.5 Import
- Urutan import: standard library → third-party package → local module, dipisah baris kosong antar grup.
- Hindari `from module import *` (wildcard import) — selalu import eksplisit apa yang dipakai.

#### 2.6 Error Handling
- Tangkap exception spesifik (`except ValueError:`), hindari `except:` polos atau `except Exception:` tanpa alasan jelas.
- Tidak boleh silent-fail (`except: pass` tanpa log), sama seperti aturan Error Handling & Logging Standard di Universal Core module.

#### 2.7 Docstring
- Function/class publik (dipakai modul lain) wajib punya docstring: tujuan, parameter, return value.
```python
def calculate_total(price: float, quantity: int) -> float:
    """
    Menghitung total harga berdasarkan harga satuan dan jumlah.

    Args:
        price: Harga satuan barang.
        quantity: Jumlah barang.

    Returns:
        Total harga (price * quantity).
    """
    return price * quantity
```

---

### A.3 HTML Standard

#### 3.1 Doctype & Struktur Dasar
- Selalu gunakan `<!DOCTYPE html>` (HTML5), hindari doctype versi lama.
- Struktur wajib: `<html lang="id">`, `<head>` lengkap dengan `<meta charset="UTF-8">` dan `<meta name="viewport" content="width=device-width, initial-scale=1.0">`.

#### 3.2 Indentasi & Nesting
- Indentasi 4 spasi, konsisten dengan PHP Standard.
- Elemen nested tidak boleh lebih dari 5–6 level tanpa alasan kuat — kalau terlalu dalam, pertimbangkan pecah jadi component/partial (Blade component, include, dll).

#### 3.3 Atribut & Penulisan Tag
- Gunakan tanda kutip ganda (`"`) untuk semua atribut, bukan kutip tunggal.
- Atribut boolean ditulis singkat: `disabled`, bukan `disabled="disabled"`.
- Tag self-closing (`<img>`, `<input>`, `<br>`) tidak perlu ditutup dengan `/>` (mengikuti HTML5), kecuali project secara eksplisit pakai XHTML.

#### 3.4 Semantic HTML
- Gunakan elemen semantik sesuai fungsinya: `<header>`, `<nav>`, `<main>`, `<section>`, `<article>`, `<footer>` — bukan `<div>` untuk semua struktur.
- `<div>`/`<span>` hanya dipakai kalau memang tidak ada elemen semantik yang sesuai (styling hook, generic container).

#### 3.5 Accessibility (a11y)
- Semua `<img>` wajib punya atribut `alt` yang deskriptif (kosong `alt=""` hanya untuk gambar dekoratif).
- Form input wajib punya `<label>` terhubung (`for` & `id` sesuai), bukan hanya placeholder.
- Gunakan atribut ARIA (`aria-label`, `aria-hidden`, dll) untuk elemen interaktif non-standar (custom dropdown, modal).

---

### A.4 CSS Standard

#### 4.1 Metodologi Penulisan
- Gunakan pendekatan class-based (BEM atau utility-first seperti Tailwind), hindari selector berbasis ID untuk styling.
- Class name deskriptif berdasarkan fungsi/komponen, bukan berdasarkan tampilan sesaat: `.card-header`, bukan `.blue-box`.

#### 4.2 Penamaan Class
- Format `kebab-case`: `.user-profile-card`, bukan `.userProfileCard` atau `.user_profile_card`.
- Kalau pakai BEM: `.block__element--modifier` (mis. `.card__title--highlighted`).

#### 4.3 Struktur File
- Pisahkan berdasarkan tanggung jawab: base/reset, layout, komponen, utility, tidak semua rule ditumpuk di satu file besar tanpa struktur.
- Hindari `!important` kecuali untuk override utility class yang memang didesain untuk itu (mis. utility framework).

#### 4.4 Responsif
- Mobile-first: tulis style default untuk layar kecil, tambahkan `@media (min-width: ...)` untuk layar lebih besar.
- Breakpoint konsisten mengikuti standar framework yang dipakai (mis. breakpoint Tailwind/Bootstrap), tidak membuat breakpoint custom acak per halaman.

#### 4.5 Unit & Value
- Gunakan `rem`/`em` untuk ukuran teks & spacing yang perlu scalable, `px` untuk border/hal yang memang harus fix.
- Hindari magic number tanpa penjelasan (mis. `margin-top: 37px` tanpa alasan) — pakai skala spacing konsisten (4px/8px increment).

---

### A.5 JavaScript Standard

#### 5.1 Deklarasi Variabel
- Gunakan `const` sebagai default, `let` hanya kalau nilai memang akan berubah, **hindari `var`**.
- Nama variabel `camelCase`, deskriptif: `const totalPrice = ...`, bukan `const tp = ...`.

#### 5.2 Function
- Gunakan arrow function untuk callback singkat, function declaration untuk fungsi utama yang perlu hoisting/readability.
- Satu function idealnya fokus satu tanggung jawab (single responsibility), hindari function >50 baris tanpa dipecah.

#### 5.3 Async/Promise
- Gunakan `async/await` sebagai default untuk operasi asynchronous, hindari nested `.then()` berlapis (callback hell).
- Setiap `async` function yang bisa gagal wajib dibungkus `try/catch`, jangan biarkan promise reject tanpa penanganan.

#### 5.4 Perbandingan & Tipe Data
- Gunakan `===` dan `!==` (strict comparison), hindari `==`/`!=` kecuali ada alasan eksplisit.
- Validasi tipe data input sebelum diproses, terutama data dari form/API eksternal.

#### 5.5 DOM Manipulation
- Minimalkan direct DOM manipulation kalau project pakai framework reaktif (Livewire/Alpine/Vue/React) — ikuti pola reaktif framework tersebut, bukan campur `document.querySelector` manual tanpa perlu.
- Event listener yang tidak lagi dipakai (mis. saat component unmount) wajib di-cleanup untuk mencegah memory leak.

---

### A.6 Bootstrap / Framework Frontend Standard

#### 6.1 Prinsip Umum
- Gunakan utility class bawaan framework (spacing, grid, flex) sebelum menulis CSS custom — jangan reinvent apa yang sudah disediakan framework.
- CSS custom hanya ditulis untuk kebutuhan yang benar-benar tidak tercakup utility class.

#### 6.2 Grid System
- Gunakan grid system resmi framework (`row`/`col` di Bootstrap, atau setara di framework lain) untuk layout, bukan `float`/`position: absolute` manual.
- Kolom grid disesuaikan breakpoint yang relevan (`col-md-`, `col-lg-`, dst), tidak asal `col-12` semua ukuran layar.

#### 6.3 Komponen
- Pakai komponen bawaan framework (modal, dropdown, navbar) sesuai dokumentasi resmi, hindari override struktur HTML komponen yang bisa merusak fungsionalitas JS bawaannya.
- Kalau perlu kustomisasi tampilan komponen, override lewat variable/theme resmi framework (bukan `!important` menimpa class bawaan).

---

### A.7 File Header Standard

Setiap file kode (PHP class, JS module, dll) yang cukup signifikan (bukan file kecil/trivial) diawali blok komentar header berisi:

```php
/**
 * [Nama File/Class]
 *
 * [Deskripsi singkat fungsi file ini]
 *
 * @author   [Nama/Role penanggung jawab]
 * @created  [YYYY-MM-DD]
 * @updated  [YYYY-MM-DD, diisi ulang tiap revisi signifikan]
 */
```

- Header wajib untuk: Controller, Service, Model kompleks, class inti bisnis logic.
- Header opsional untuk: file config sederhana, file view/template kecil.
- `@updated` diisi/diupdate setiap ada revisi signifikan pada file — selaras dengan Version Control Standard (Universal Core #7).

---

### A.8 Comment Standard

- Komentar menjelaskan **kenapa** (why), bukan mengulang apa yang sudah jelas dari kode itu sendiri.
  - Buruk: `// increment i by 1` di atas `$i++;`
  - Baik: `// retry maksimal 3x karena API pihak ketiga kadang timeout sesaat`
- Gunakan `TODO:` dan `FIXME:` untuk penanda kerja belum selesai, disertai konteks singkat: `// TODO: ganti ke queue job setelah load testing`.
- Kode yang di-comment-out (dinonaktifkan) tidak boleh dibiarkan menumpuk di production — hapus atau pindahkan ke Git history.

#### 8.1 Tag Atribusi Perubahan — `[THECHNOLOGY-{ACTION}]`

Untuk penambahan/perubahan kode yang **signifikan** (fitur baru, fix, penyesuaian struktur, penghapusan — bukan komentar receh), wajib ditandai dengan tag berisi **kode aksi**, ditulis sebelum penjelasan "why". **Tidak menyertakan identitas persona/role AI** yang mengerjakan — tag ini menandai jenis perubahan, bukan siapa pengeksekusinya:

```
// [THECHNOLOGY-{ACTION}] : <penjelasan singkat perubahan/penambahan>
```

**Kode ACTION yang berlaku:**

| Kode | Arti | Contoh |
|---|---|---|
| `CRE` | Create — file/fungsi/fitur baru dari nol | `[THECHNOLOGY-CRE] : controller baru untuk handle SPMB` |
| `MOD` | Modify — ubah/sesuaikan kode yang sudah ada | `[THECHNOLOGY-MOD] : Session lifecycle (read-once + unset)` |
| `FIX` | Fix — perbaikan bug | `[THECHNOLOGY-FIX] : favicon path 404, temuan QC` |
| `DEL` | Delete — hapus kode/file (dead code, dll) | `[THECHNOLOGY-DEL] : app.js dihapus, file placeholder kosong` |

- Prefix `THECHNOLOGY` mengikuti domain project (ganti sesuai domain lain, mis. `EDTHERIA-CRE`, kalau dipakai di luar Thechnology).
- Tag ini melengkapi (bukan menggantikan) `TODO:`/`FIXME:` — dipakai spesifik untuk atribusi jenis aksi, bukan penanda kerja belum selesai.
- Berlaku untuk file kode signifikan yang sama dengan ambang File Header Standard (A.7) — tidak wajib untuk file trivial/config sederhana.

---

### A.9 Git & Version Control Standard

#### 9.1 Branching
- `main`/`master` selalu dalam kondisi stabil/deployable.
- Kerja fitur baru di branch terpisah: `feature/nama-fitur`, bugfix di `fix/nama-bug`.

#### 9.2 Commit Message
- Format singkat & deskriptif, gunakan awalan konsisten: `feat:`, `fix:`, `docs:`, `refactor:`, `test:`.
- Satu commit idealnya satu perubahan logis, hindari commit raksasa yang campur banyak hal tidak berhubungan.

#### 9.3 Pull Request / Merge
- Tidak merge langsung ke `main` tanpa review (sesuai Review Checklist di Universal Core).
- Deskripsi PR menjelaskan apa yang berubah & kenapa, bukan hanya "update code".

---

### A.10 Error Handling & Logging Standard

#### 10.1 Error Handling
- Gunakan `try/catch` untuk operasi yang berisiko gagal (koneksi eksternal, file I/O, parsing data).
- Jangan silent-fail (`catch` kosong tanpa log/handling) — minimal dicatat ke log meskipun error di-suppress dari user.
- Pesan error ke user harus ramah & tidak membocorkan detail teknis sensitif (stack trace, query SQL) — detail teknis cukup di log internal.

#### 10.2 Logging
- Gunakan level log yang sesuai: `debug`, `info`, `warning`, `error`, `critical` — tidak semua log ditulis sebagai `error`.
- Log wajib menyertakan konteks yang cukup untuk debugging (user/ID terkait, timestamp, aksi yang dilakukan) tanpa mencatat data sensitif (password, token, data pribadi mentah).

---

### A.11 Testing Standard

- Setiap fitur inti (bukan sekadar tampilan statis) idealnya punya test — minimal unit test untuk logic penting (kalkulasi, validasi, transformasi data).
- Test ditulis mengikuti pola Arrange–Act–Assert: siapkan kondisi, jalankan aksi, verifikasi hasil.
- Test tidak bergantung pada data/state eksternal yang tidak terkontrol (mis. tanggal hari ini, API pihak ketiga live) — gunakan mock/fake untuk dependency eksternal.

---

### A.12 Security Standard

- Semua input dari user wajib divalidasi & di-sanitize sebelum diproses/disimpan.
- Query database wajib pakai prepared statement/parameter binding (ORM Eloquent otomatis menangani ini) — dilarang keras concatenate input user langsung ke query SQL.
- Password wajib di-hash (`bcrypt`/`argon2`, atau setara di framework/bahasa yang dipakai — bawaan Laravel kalau memakai Laravel), tidak pernah disimpan/di-log dalam bentuk plain text.
- File upload dari user divalidasi tipe & ukurannya, disimpan di luar direktori yang bisa dieksekusi langsung sebagai script.
- Credential (API key, DB password) disimpan di file environment (`.env`), tidak pernah di-hardcode di kode atau ter-commit ke Git.

---

### A.13 Database Standard

- Nama tabel: `snake_case`, plural: `users`, `order_items`.
- Nama kolom: `snake_case`, deskriptif: `created_at`, `is_active`, `user_id` (foreign key format `[tabel_singular]_id`).
- Setiap tabel wajib punya primary key (`id`), dan `timestamps` (`created_at`, `updated_at`) kecuali ada alasan eksplisit untuk tidak memakainya.
- Migration (atau setara tracked-schema-change di stack yang dipakai, mis. Alembic untuk Python) wajib dipakai untuk setiap perubahan struktur database (tidak mengubah struktur langsung lewat GUI tanpa perubahan tercatat).
- Relasi antar tabel didefinisikan eksplisit lewat foreign key constraint, bukan hanya diasumsikan di level aplikasi.

---

### A.14 Environment & Configuration Standard

- Konfigurasi yang berbeda per environment (local/staging/production) disimpan di `.env`, dibaca lewat layer config resmi framework yang dipakai (mis. `config/*.php` + `env()` dibatasi ke file config saja untuk Laravel; `settings.py`/`python-dotenv` untuk Python, dst) — prinsipnya: jangan akses env variable mentah tersebar di banyak file, sentralisasi di satu layer config.
- File `.env` tidak pernah di-commit ke Git (`.env.example` disediakan sebagai template tanpa nilai sensitif).
- Mode debug (`APP_DEBUG=true`) hanya aktif di local/staging, wajib `false` di production untuk mencegah kebocoran detail error ke publik.

## Module B — Course / Materi Ajar

Tidak dibuat sebagai module terpisah. Project course/materi ajar cukup mengacu ke **Universal Core + bagian relevan dari Module A** (lihat contoh "Kombinasi Module" di atas — kasus PHP Course).

---

# Ringkasan Standar

Dokumen ini menggantikan TDS lama (khusus coding) dengan versi yang lebih luas cakupannya:

- **Universal Core** wajib untuk semua project apa pun jenisnya — kini 8 poin (tambah Deployment Standard)
- **Module A (Software)** dipakai project seperti Thechnology — 14 sub-standar, kini eksplisit **stack-agnostic**: bahasa/framework ditentukan CLA per-project, referensi Laravel/framework spesifik bersifat conditional
- **Course/Materi ajar** (mis. Edtheria) tidak punya module terpisah — cukup pakai Universal Core + bagian relevan dari Module A (lihat "Kombinasi Module")
- Module baru bisa ditambah ke depan kalau ada jenis project lain yang benar-benar tidak tercakup Module A
- **Belum masuk di v1.1** (calon revisi lanjutan): standar format API/response — ditunda sampai ada project yang benar-benar butuh endpoint API

Versi: 1.1
Status: **approved** (disahkan RDA, 2026-08-03) — menggantikan resmi acuan/TDS lama.
