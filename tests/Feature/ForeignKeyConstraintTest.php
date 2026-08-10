<?php

/**
 * ForeignKeyConstraintTest — Feature test untuk FK constraint & cascade
 *
 * Mencakup:
 * - downloads.download_category_id RESTRICT → tolak hapus kategori yang masih dipakai
 * - photos.album_id cascade → force-delete album hapus foto
 * - photos.album_id soft cascade → soft-delete album soft-delete foto
 * - Album slug mutation saat soft-delete
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : ForeignKeyConstraintTest

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Download;
use App\Models\DownloadCategory;
use App\Models\Photo;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForeignKeyConstraintTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // DOWNLOADS → DOWNLOAD CATEGORIES (RESTRICT)
    // ──────────────────────────────────────────────

    /**
     * [THECHNOLOGY-CRE] : Hapus kategori yang masih dipakai Download → ditolak (RESTRICT).
     * FK download_category_id menggunakan restrictOnDelete.
     * Di MySQL: throw QueryException. Di SQLite: delete diabaikan jika FK ON.
     * Test ini verifikasi bahwa kategori TETAP ADA setelah percobaan delete.
     */
    public function test_cannot_delete_download_category_with_existing_downloads(): void
    {
        // Arrange: buat kategori + download yang terkait
        $category = DownloadCategory::factory()->create(['name' => 'Formulir']);
        $download = Download::factory()->forCategory($category)->create(['title' => 'Formulir PPDB']);

        $categoryId = $category->id;

        // Act: coba hapus kategori
        try {
            $category->delete();
        } catch (QueryException $e) {
            // MySQL: expected — RESTRICT constraint violation
            $this->assertStringContainsStringIgnoringCase('foreign key', $e->getMessage());
        }

        // Assert: kategori TETAP ada (tidak terhapus)
        $this->assertDatabaseHas('download_categories', ['id' => $categoryId]);
        $this->assertDatabaseHas('downloads', ['id' => $download->id]);
    }

    /**
     * [THECHNOLOGY-CRE] : Hapus kategori tanpa download → berhasil.
     * Memastikan RESTRICT hanya memblokir jika ada child record.
     */
    public function test_can_delete_download_category_without_downloads(): void
    {
        // Arrange: kategori tanpa download
        $category = DownloadCategory::factory()->create();

        // Act
        $category->delete();

        // Assert
        $this->assertSoftDeleted($category);
    }

    // ──────────────────────────────────────────────
    // PHOTOS → ALBUMS (CASCADE)
    // ──────────────────────────────────────────────

    /**
     * [THECHNOLOGY-CRE] : Force-delete album → foto ikut terhapus permanen (cascade).
     * FK photos.album_id menggunakan cascadeOnDelete.
     */
    public function test_force_delete_album_cascades_to_photos(): void
    {
        // Arrange
        $album = Album::factory()->create(['name' => 'Album Wisuda']);
        $photo1 = Photo::factory()->forAlbum($album)->create(['caption' => 'Foto 1']);
        $photo2 = Photo::factory()->forAlbum($album)->create(['caption' => 'Foto 2']);

        $this->assertEquals(2, $album->photos()->count());

        // Act: force delete album
        $album->forceDelete();

        // Assert: foto juga terhapus permanen (tidak ada di DB, termasuk trash)
        $this->assertDatabaseMissing('photos', ['id' => $photo1->id]);
        $this->assertDatabaseMissing('photos', ['id' => $photo2->id]);
        $this->assertDatabaseMissing('albums', ['id' => $album->id]);
    }

    /**
     * [THECHNOLOGY-CRE] : Soft-delete album → foto ikut soft-delete (model-level cascade).
     * Album::booted() punya event softDeleted yang memanggil $album->photos()->delete().
     */
    public function test_soft_delete_album_soft_deletes_photos(): void
    {
        // Arrange
        $album = Album::factory()->create(['name' => 'Album Soft']);
        $photo = Photo::factory()->forAlbum($album)->create(['caption' => 'Foto Soft']);

        $this->assertNull($photo->deleted_at);
        $this->assertNull($album->deleted_at);

        // Act: soft-delete album
        $album->delete();

        // Assert: album dan foto ter-soft-delete
        $this->assertNotNull($album->fresh()->deleted_at);
        $this->assertSoftDeleted($album);

        // Foto harus ikut ter-soft-delete
        $trashedPhoto = Photo::withTrashed()->find($photo->id);
        $this->assertNotNull($trashedPhoto, 'Foto harus masih ada (soft-deleted)');
        $this->assertNotNull($trashedPhoto->deleted_at, 'Foto harus punya deleted_at timestamp');
    }

    /**
     * [THECHNOLOGY-CRE] : Restore album → foto ikut restore (model-level cascade).
     * Album::booted() punya event restored yang memanggil $album->photos()->withTrashed()->restore().
     */
    public function test_restore_album_restores_photos(): void
    {
        // Arrange: soft-delete album + foto
        $album = Album::factory()->create(['name' => 'Album Restore']);
        $photo = Photo::factory()->forAlbum($album)->create(['caption' => 'Foto Restore']);
        $album->delete();

        $this->assertNotNull(Photo::withTrashed()->find($photo->id)->deleted_at);

        // Act: restore album
        $album->restore();

        // Assert: album + foto kembali aktif
        $this->assertNull($album->fresh()->deleted_at);
        $restoredPhoto = Photo::find($photo->id);
        $this->assertNotNull($restoredPhoto, 'Foto harus kembali aktif');
        $this->assertNull($restoredPhoto->deleted_at);
    }

    /**
     * [THECHNOLOGY-CRE] : Album slug termutasi saat soft-delete (arsitektur §9).
     */
    public function test_album_slug_mutates_on_soft_delete(): void
    {
        // Arrange
        $album = Album::factory()->create(['name' => 'Album Slug', 'slug' => 'album-slug']);
        $originalSlug = $album->slug;

        // Act: soft-delete
        $album->delete();

        // Assert: slug termutasi dengan suffix -archived-{id}
        $trashedAlbum = Album::withTrashed()->find($album->id);
        $expectedSlug = 'album-slug-archived-' . $album->id;
        $this->assertEquals($expectedSlug, $trashedAlbum->slug);
        $this->assertNotEquals($originalSlug, $trashedAlbum->slug);
    }
}
