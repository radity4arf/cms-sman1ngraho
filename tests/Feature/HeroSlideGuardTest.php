<?php

/**
 * HeroSlideGuardTest — Unit/Feature test untuk guard is_default HeroSlide
 *
 * Mencakup:
 * - Atomic swap is_default saat create/update
 * - Tolak unset is_default tanpa kandidat pengganti
 * - Regresi guard delete/draft/nonaktif existing (Task 1 Poin 2)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : HeroSlideGuardTest

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\HeroSlide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSlideGuardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * [THECHNOLOGY-CRE] : Atomic swap — dua create is_default=true.
     * Model-level saving event harus meng-unset default lama sehingga
     * hanya satu is_default=true di akhir.
     */
    public function test_atomic_swap_on_create_ensures_single_default(): void
    {
        // Arrange: buat default slide pertama
        $first = HeroSlide::factory()->default()->create(['title' => 'Slide A']);
        $this->assertTrue($first->fresh()->is_default);

        // Act: buat slide kedua dengan is_default=true
        $second = HeroSlide::factory()->default()->create(['title' => 'Slide B']);

        // Assert: hanya slide kedua yang is_default=true
        $this->assertFalse($first->fresh()->is_default, 'Slide lama harus di-unset');
        $this->assertTrue($second->fresh()->is_default, 'Slide baru harus menjadi default');
        $this->assertEquals(1, HeroSlide::where('is_default', true)->count(),
            'Hanya boleh ada 1 slide default');
    }

    /**
     * [THECHNOLOGY-CRE] : Atomic swap — update is_default false→true.
     * Model-level saving harus meng-unset default existing.
     */
    public function test_atomic_swap_on_update_ensures_single_default(): void
    {
        // Arrange
        $first = HeroSlide::factory()->default()->create(['title' => 'Slide A']);
        $second = HeroSlide::factory()->create(['title' => 'Slide B', 'is_default' => false]);
        $this->assertTrue($first->fresh()->is_default);

        // Act: set is_default=true pada slide kedua
        $second->is_default = true;
        $second->save();

        // Assert
        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
        $this->assertEquals(1, HeroSlide::where('is_default', true)->count());
    }

    /**
     * [THECHNOLOGY-CRE] : Tolak unset is_default true→false tanpa kandidat pengganti.
     * Harus throw RuntimeException, bukan silent fail.
     */
    public function test_reject_unset_default_without_replacement(): void
    {
        // Arrange: hanya 1 slide, is_default=true, published+aktif
        $slide = HeroSlide::factory()->default()->create([
            'status'    => ContentStatus::Published->value,
            'is_active' => true,
        ]);

        // Assert: tidak ada slide lain yang published+aktif sebagai kandidat
        $this->assertEquals(1, HeroSlide::where('status', ContentStatus::Published->value)
            ->where('is_active', true)->count());

        // Act & Assert: unset default tanpa pengganti → RuntimeException
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tidak dapat menghapus status default');

        $slide->is_default = false;
        $slide->save();
    }

    /**
     * [THECHNOLOGY-CRE] : Unset default DIPERBOLEHKAN jika ada kandidat pengganti
     * (published + aktif). Tidak boleh throw.
     */
    public function test_allow_unset_default_with_replacement(): void
    {
        // Arrange: dua slide published+aktif, salah satunya default
        $default = HeroSlide::factory()->default()->create([
            'title'     => 'Default',
            'status'    => ContentStatus::Published->value,
            'is_active' => true,
        ]);
        $candidate = HeroSlide::factory()->create([
            'title'     => 'Kandidat',
            'status'    => ContentStatus::Published->value,
            'is_active' => true,
            'is_default'=> false,
        ]);

        // Act: unset default — harus berhasil karena ada kandidat
        $default->is_default = false;
        $default->save();

        // Assert: tidak throw, default sekarang false
        $this->assertFalse($default->fresh()->is_default);
        $this->assertEquals(0, HeroSlide::where('is_default', true)->count());
    }

    /**
     * [THECHNOLOGY-CRE] : Regresi — guard delete default tetap aktif.
     * Model-level deleting hook harus menolak delete record is_default=true.
     */
    public function test_regression_guard_delete_default_still_works(): void
    {
        // Arrange
        $slide = HeroSlide::factory()->default()->create();

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Slide default tidak dapat dihapus');

        $slide->delete();
    }

    /**
     * [THECHNOLOGY-CRE] : Regresi — guard draft default tetap aktif.
     * Model-level updating hook harus menolak set status=draft pada record default.
     */
    public function test_regression_guard_draft_default_still_works(): void
    {
        // Arrange
        $slide = HeroSlide::factory()->default()->create([
            'status' => ContentStatus::Published->value,
        ]);

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Slide default tidak dapat diubah menjadi draft');

        $slide->status = ContentStatus::Draft->value;
        $slide->save();
    }

    /**
     * [THECHNOLOGY-CRE] : Regresi — guard nonaktifkan default tetap aktif.
     * Model-level updating hook harus menolak set is_active=false pada record default.
     */
    public function test_regression_guard_deactivate_default_still_works(): void
    {
        // Arrange
        $slide = HeroSlide::factory()->default()->create([
            'is_active' => true,
        ]);

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Slide default tidak dapat dinonaktifkan');

        $slide->is_active = false;
        $slide->save();
    }

    /**
     * [THECHNOLOGY-CRE] : Delete non-default slide — harus berhasil.
     * Memastikan guard delete hanya memblokir record is_default=true.
     */
    public function test_can_delete_non_default_slide(): void
    {
        // Arrange
        $slide = HeroSlide::factory()->create(['is_default' => false]);

        // Act
        $slide->delete();

        // Assert
        $this->assertSoftDeleted($slide);
    }
}
