<?php

/**
 * UploadValidationTest — Feature test untuk validasi upload RT-11
 *
 * Mencakup:
 * - Tolak MIME di luar whitelist (Download + Photo) — model-level via Spatie
 * - Terima MIME valid
 * - Size limit >10MB di-enforce di Filament form (bukan model-level)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : UploadValidationTest

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\Photo;
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
    // CATATAN SIZE VALIDATION
    // ──────────────────────────────────────────────

    /**
     * [THECHNOLOGY-CRE] : Verifikasi model tidak crash dengan file yang valid.
     * Batas 10MB di-enforce di Filament form (SpatieMediaLibraryFileUpload::maxSize),
     * bukan di level model/collection. Test ini memastikan file valid tetap diproses.
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
}
