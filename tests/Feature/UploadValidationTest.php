<?php

/**
 * UploadValidationTest — Feature test untuk validasi upload RT-11
 *
 * Mencakup:
 * - Tolak MIME di luar whitelist (Download + Photo) — model-level via Spatie
 * - Terima MIME valid
 * - [THECHNOLOGY-FIX] Size limit >10MB di-enforce di level MODEL (Media::saving),
 *   bukan cuma Filament form. Test upload file besar via Tinker/direct model.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 * @updated  2026-08-11 — tambah model-level size validation test (CGX Fase 3 Critical #2)
 */

// [THECHNOLOGY-CRE] : UploadValidationTest
// [THECHNOLOGY-FIX] : Model-level file size validation — tolak >10MB via Media::saving

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\HeroSlide;
use App\Models\Photo;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Tests\TestCase;

class UploadValidationTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // DOWNLOAD — MIME validation
    // ──────────────────────────────────────────────

    /**
     * [THECHNOLOGY-CRE] : Download — tolak MIME tidak valid (teks).
     * Koleksi 'file' hanya menerima: pdf, doc, docx, xls, xlsx, jpg, png, webp.
     * File .txt dengan MIME text/plain HARUS ditolak server-side.
     */
    public function test_download_rejects_invalid_text_mime(): void
    {
        $category = DownloadCategory::factory()->create();
        $download = Download::factory()->forCategory($category)->create();

        // File teks — text/plain TIDAK ada di whitelist Download
        $invalidFile = UploadedFile::fake()->createWithContent(
            'dokumen.txt',
            "Ini adalah konten file teks biasa.\nBaris kedua."
        );

        $this->expectException(FileCannotBeAdded::class);

        $download->addMedia($invalidFile)->toMediaCollection('file');
    }

    /**
     * [THECHNOLOGY-CRE] : Download — tolak MIME gambar yang tidak ada di whitelist.
     * Koleksi 'file' menerima jpg/png/webp, BUKAN gif.
     */
    public function test_download_rejects_gif_image(): void
    {
        $category = DownloadCategory::factory()->create();
        $download = Download::factory()->forCategory($category)->create();

        // GIF — TIDAK diterima (bukan jpg/png/webp)
        $this->expectException(FileCannotBeAdded::class);

        $download->addMedia(UploadedFile::fake()->image('photo.gif', 100, 100))
            ->toMediaCollection('file');
    }

    /**
     * [THECHNOLOGY-CRE] : Download — terima MIME valid (JPEG).
     * Koleksi 'file' menerima image/jpeg.
     */
    public function test_download_accepts_valid_jpeg(): void
    {
        $category = DownloadCategory::factory()->create();
        $download = Download::factory()->forCategory($category)->create();

        $validFile = UploadedFile::fake()->image('foto.jpg', 800, 600);

        $media = $download->addMedia($validFile)->toMediaCollection('file');

        $this->assertNotNull($media);
        $this->assertStringContainsString('image/', $media->mime_type);
    }

    // ──────────────────────────────────────────────
    // PHOTO — MIME validation
    // ──────────────────────────────────────────────

    /**
     * [THECHNOLOGY-CRE] : Photo — tolak MIME tidak valid (teks).
     * Koleksi 'image' hanya menerima: jpg, png, webp.
     */
    public function test_photo_rejects_invalid_text_mime(): void
    {
        $album = Album::factory()->create();
        $photo = Photo::factory()->forAlbum($album)->create();

        // File teks TIDAK diterima di koleksi 'image'
        $invalidFile = UploadedFile::fake()->createWithContent(
            'dokumen.txt',
            "Konten teks biasa."
        );

        $this->expectException(FileCannotBeAdded::class);

        $photo->addMedia($invalidFile)->toMediaCollection('image');
    }

    /**
     * [THECHNOLOGY-CRE] : Photo — tolak GIF (bukan jpg/png/webp).
     */
    public function test_photo_rejects_gif_image(): void
    {
        $album = Album::factory()->create();
        $photo = Photo::factory()->forAlbum($album)->create();

        $this->expectException(FileCannotBeAdded::class);

        $photo->addMedia(UploadedFile::fake()->image('animasi.gif', 100, 100))
            ->toMediaCollection('image');
    }

    /**
     * [THECHNOLOGY-CRE] : Photo — terima MIME valid (JPEG).
     */
    public function test_photo_accepts_valid_jpeg(): void
    {
        $album = Album::factory()->create();
        $photo = Photo::factory()->forAlbum($album)->create();

        $validFile = UploadedFile::fake()->image('foto.jpg', 800, 600);

        $media = $photo->addMedia($validFile)->toMediaCollection('image');

        $this->assertNotNull($media);
        $this->assertStringContainsString('image/', $media->mime_type);
    }

    /**
     * [THECHNOLOGY-CRE] : Photo — terima MIME valid (PNG).
     */
    public function test_photo_accepts_valid_png(): void
    {
        $album = Album::factory()->create();
        $photo = Photo::factory()->forAlbum($album)->create();

        $validFile = UploadedFile::fake()->image('foto.png', 800, 600);

        $media = $photo->addMedia($validFile)->toMediaCollection('image');

        $this->assertNotNull($media);
    }

    // ──────────────────────────────────────────────
    // SIZE VALIDATION — MODEL LEVEL (Media::saving)
    // ──────────────────────────────────────────────

    /**
     * [THECHNOLOGY-FIX] : Tolak file >10MB di level model (Media::saving).
     * Validasi ukuran file sekarang di-enforce di level Spatie Media model,
     * bukan cuma Filament form. Mencakup upload via Tinker/API/CLI.
     *
     * Strategi test: upload file kecil yang valid dulu (lolos Spatie FileAdder),
     * lalu modifikasi size langsung di Media model dan re-save — simulasi
     * skenario bypass form (Tinker/CLI). Media::saving harus menolak.
     */
    public function test_model_rejects_file_larger_than_10mb(): void
    {
        $album = Album::factory()->create();
        $photo = Photo::factory()->forAlbum($album)->create();

        // Upload file kecil yang valid (lolos MIME check)
        $validFile = UploadedFile::fake()->image('small.jpg', 100, 100);
        $photo->addMedia($validFile)->toMediaCollection('image');

        // Dapatkan Media record, set ukuran > 10MB, lalu save ulang
        $media = $photo->getFirstMedia('image');
        $this->assertNotNull($media, 'Media harus sudah tersimpan');

        $media->size = 11 * 1024 * 1024; // 11MB — melebihi batas 10MB

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('10MB');

        $media->save(); // Media::saving listener harus menolak
    }

    /**
     * [THECHNOLOGY-FIX] : Download model — tolak file >10MB.
     * Validasi berlaku juga untuk koleksi 'file' (Download).
     */
    public function test_download_rejects_file_larger_than_10mb(): void
    {
        $category = DownloadCategory::factory()->create();
        $download = Download::factory()->forCategory($category)->create();

        // Upload gambar valid dulu — Download menerima image/jpeg di whitelist
        $validFile = UploadedFile::fake()->image('small.jpg', 100, 100);
        $download->addMedia($validFile)->toMediaCollection('file');

        $media = $download->getFirstMedia('file');
        $this->assertNotNull($media);

        $media->size = 11 * 1024 * 1024; // 11MB

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('10MB');

        $media->save();
    }

    /**
     * [THECHNOLOGY-FIX] : HeroSlide model — tolak file >10MB.
     * Mencakup semua model gambar (Post, Achievement, dll. — diwakili HeroSlide).
     */
    public function test_hero_slide_rejects_file_larger_than_10mb(): void
    {
        $slide = HeroSlide::factory()->create();

        $validFile = UploadedFile::fake()->image('small.jpg', 100, 100);
        $slide->addMedia($validFile)->toMediaCollection('image');

        $media = $slide->getFirstMedia('image');
        $this->assertNotNull($media);

        $media->size = 11 * 1024 * 1024; // 11MB

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('10MB');

        $media->save();
    }

    /**
     * [THECHNOLOGY-CRE] : Verifikasi model tidak crash dengan file yang valid.
     * File kecil (<10MB) harus tetap diproses normal.
     */
    public function test_photo_model_accepts_valid_file(): void
    {
        $album = Album::factory()->create();
        $photo = Photo::factory()->forAlbum($album)->create();

        $validFile = UploadedFile::fake()->image('foto.jpg', 800, 600);

        $media = $photo->addMedia($validFile)->toMediaCollection('image');

        $this->assertNotNull($media);
        $this->assertGreaterThan(0, $media->size, 'File harus punya ukuran > 0');
    }

    /**
     * [THECHNOLOGY-FIX] : Post model — file dalam batas 10MB tetap diterima.
     * Memastikan validasi tidak false-positive untuk file normal.
     */
    public function test_post_model_accepts_valid_file(): void
    {
        $post = Post::factory()->create([
            'status'    => 'published',
            'is_active' => true,
        ]);

        $validFile = UploadedFile::fake()->image('featured.jpg', 1200, 800);

        $media = $post->addMedia($validFile)->toMediaCollection('featured_image');

        $this->assertNotNull($media);
        $this->assertLessThanOrEqual(10 * 1024 * 1024, $media->size,
            'File valid harus ≤ 10MB');
    }
}
