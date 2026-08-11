<?php

/**
 * HeroSlideGuardTest — Unit/Feature test untuk guard is_default HeroSlide
 *
 * Mencakup:
 * - Atomic swap is_default via HeroSlideService::promoteAsDefault()
 * - Tolak UNCONDITIONAL unset is_default true→false di luar service
 * - Tolak create draft+default
 * - Tolak create inactive+default
 * - Tolak save default dengan status draft/inactive
 * - Regresi guard delete/draft/nonaktif existing (Task 1 Poin 2)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 * @updated  2026-08-11 — strict CGX review fix: service-only swap, reject draft/inactive default
 */

// [THECHNOLOGY-CRE] : HeroSlideGuardTest
// [THECHNOLOGY-FIX] : Strict CGX — service-only swap, draft+default ditolak, unset langsung ditolak

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\HeroSlide;
use App\Services\HeroSlideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSlideGuardTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────
    // PROMOTE AS DEFAULT — service
    // ──────────────────────────────────────────────────

    /**
     * [THECHNOLOGY-FIX] : HeroSlideService::promoteAsDefault() — swap default.
     * Slide baru menjadi default, slide lama di-unset, hanya 1 default.
     */
    public function test_promote_as_default_service_swaps_correctly(): void
    {
        // Arrange: slide A default, slide B published+aktif non-default
        $slideA = HeroSlide::factory()->default()->create([
            'title'     => 'Slide A',
            'status'    => ContentStatus::Published->value,
            'is_active' => true,
        ]);
        $slideB = HeroSlide::factory()->create([
            'title'     => 'Slide B',
            'status'    => ContentStatus::Published->value,
            'is_active' => true,
            'is_default'=> false,
        ]);

        $this->assertTrue($slideA->fresh()->is_default);
        $this->assertFalse($slideB->fresh()->is_default);

        // Act: promosikan slide B via service
        HeroSlideService::promoteAsDefault($slideB);

        // Assert: slide B jadi default, slide A tidak
        $this->assertFalse($slideA->fresh()->is_default, 'Slide A harus di-unset');
        $this->assertTrue($slideB->fresh()->is_default, 'Slide B harus jadi default');
        $this->assertEquals(1, HeroSlide::where('is_default', true)->count(),
            'Hanya boleh ada 1 slide default');
    }

    /**
     * [THECHNOLOGY-FIX] : promoteAsDefault() tolak slide draft.
     * Service harus validasi status=published sebelum swap.
     */
    public function test_promote_as_default_rejects_draft_slide(): void
    {
        $slide = HeroSlide::factory()->draft()->create([
            'status'    => ContentStatus::Draft->value,
            'is_active' => true,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('draft');

        HeroSlideService::promoteAsDefault($slide);
    }

    /**
     * [THECHNOLOGY-FIX] : promoteAsDefault() tolak slide nonaktif.
     * Service harus validasi is_active=true sebelum swap.
     */
    public function test_promote_as_default_rejects_inactive_slide(): void
    {
        $slide = HeroSlide::factory()->inactive()->create([
            'status'    => ContentStatus::Published->value,
            'is_active' => false,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tidak aktif');

        HeroSlideService::promoteAsDefault($slide);
    }

    // ──────────────────────────────────────────────────
    // ATOMIC SWAP — via saving event (create/update is_default=true)
    // ──────────────────────────────────────────────────

    /**
     * [THECHNOLOGY-CRE] : Atomic swap — dua create is_default=true.
     * Model-level saving event harus meng-unset default lama sehingga
     * hanya satu is_default=true di akhir.
     */
    public function test_atomic_swap_on_create_ensures_single_default(): void
    {
        $first = HeroSlide::factory()->default()->create(['title' => 'Slide A']);
        $this->assertTrue($first->fresh()->is_default);

        $second = HeroSlide::factory()->default()->create(['title' => 'Slide B']);

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
        $first = HeroSlide::factory()->default()->create(['title' => 'Slide A']);
        $second = HeroSlide::factory()->create(['title' => 'Slide B', 'is_default' => false]);
        $this->assertTrue($first->fresh()->is_default);

        $second->is_default = true;
        $second->save();

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue($second->fresh()->is_default);
        $this->assertEquals(1, HeroSlide::where('is_default', true)->count());
    }

    // ──────────────────────────────────────────────────
    // TOLAK UNSET DEFAULT LANGSUNG (tanpa service)
    // ──────────────────────────────────────────────────

    /**
     * [THECHNOLOGY-FIX] : Tolak SEMUA unset is_default true→false
     * yang tidak melalui service/swap. Bahkan jika ada kandidat pengganti.
     * Ini perbaikan dari bug CGX — test lama MALAH mengesahkan bug ini.
     */
    public function test_reject_direct_unset_default_even_with_replacement(): void
    {
        // Arrange: slide default + kandidat published+aktif
        $default = HeroSlide::factory()->default()->create([
            'title'     => 'Default',
            'status'    => ContentStatus::Published->value,
            'is_active' => true,
        ]);
        HeroSlide::factory()->create([
            'title'     => 'Kandidat',
            'status'    => ContentStatus::Published->value,
            'is_active' => true,
            'is_default'=> false,
        ]);

        // Act & Assert: unset langsung → TETAP ditolak
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HeroSlideService::promoteAsDefault');

        $default->is_default = false;
        $default->save();
    }

    /**
     * [THECHNOLOGY-CRE] : Tolak unset is_default true→false tanpa kandidat pengganti.
     * Harus throw RuntimeException, bukan silent fail.
     */
    public function test_reject_unset_default_without_replacement(): void
    {
        $slide = HeroSlide::factory()->default()->create([
            'status'    => ContentStatus::Published->value,
            'is_active' => true,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('HeroSlideService::promoteAsDefault');

        $slide->is_default = false;
        $slide->save();
    }

    // ──────────────────────────────────────────────────
    // TOLAK CREATE DRAFT + DEFAULT / INACTIVE + DEFAULT
    // ──────────────────────────────────────────────────

    /**
     * [THECHNOLOGY-FIX] : Tolak create is_default=true dengan status=draft.
     * Default wajib published dari awal. Ini adalah bug yang ditemukan CGX.
     */
    public function test_reject_create_draft_default(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('draft');

        HeroSlide::factory()->default()->draft()->create([
            'status'    => ContentStatus::Draft->value,
            'is_active' => true,
        ]);
    }

    /**
     * [THECHNOLOGY-FIX] : Tolak create is_default=true dengan is_active=false.
     * Default wajib aktif dari awal. Ini adalah bug yang ditemukan CGX.
     */
    public function test_reject_create_inactive_default(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('nonaktif');

        HeroSlide::factory()->default()->inactive()->create([
            'status'    => ContentStatus::Published->value,
            'is_active' => false,
        ]);
    }

    // ──────────────────────────────────────────────────
    // REGRESI — guard delete/draft/nonaktif
    // ──────────────────────────────────────────────────

    /**
     * [THECHNOLOGY-CRE] : Regresi — guard delete default tetap aktif.
     */
    public function test_regression_guard_delete_default_still_works(): void
    {
        $slide = HeroSlide::factory()->default()->create();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Slide default tidak dapat dihapus');

        $slide->delete();
    }

    /**
     * [THECHNOLOGY-CRE] : Regresi — guard draft default tetap aktif.
     */
    public function test_regression_guard_draft_default_still_works(): void
    {
        $slide = HeroSlide::factory()->default()->create([
            'status' => ContentStatus::Published->value,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('draft');

        $slide->status = ContentStatus::Draft->value;
        $slide->save();
    }

    /**
     * [THECHNOLOGY-CRE] : Regresi — guard nonaktifkan default tetap aktif.
     */
    public function test_regression_guard_deactivate_default_still_works(): void
    {
        $slide = HeroSlide::factory()->default()->create([
            'is_active' => true,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Slide default tidak dapat dinonaktifkan');

        $slide->is_active = false;
        $slide->save();
    }

    // ──────────────────────────────────────────────────
    // REGRESI — operasi non-default tetap berfungsi
    // ──────────────────────────────────────────────────

    /**
     * [THECHNOLOGY-CRE] : Delete non-default slide — harus berhasil.
     */
    public function test_can_delete_non_default_slide(): void
    {
        $slide = HeroSlide::factory()->create(['is_default' => false]);

        $slide->delete();

        $this->assertSoftDeleted($slide);
    }

    /**
     * [THECHNOLOGY-FIX] : Save default dengan status=draft via update -> ditolak.
     * Test bahwa guard saving juga menolak update yang mengarah ke draft+default.
     */
    public function test_reject_save_existing_default_as_draft(): void
    {
        // Buat default yang valid dulu
        $slide = HeroSlide::factory()->default()->create([
            'status'    => ContentStatus::Published->value,
            'is_active' => true,
        ]);

        // Coba ubah status jadi draft
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('draft');

        $slide->status = ContentStatus::Draft->value;
        $slide->save();
    }

    /**
     * [THECHNOLOGY-FIX] : Save default dengan is_active=false via update -> ditolak.
     */
    public function test_reject_save_existing_default_as_inactive(): void
    {
        $slide = HeroSlide::factory()->default()->create([
            'status'    => ContentStatus::Published->value,
            'is_active' => true,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('dinonaktifkan');

        $slide->is_active = false;
        $slide->save();
    }
}
