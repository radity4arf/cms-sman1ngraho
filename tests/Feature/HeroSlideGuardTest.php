<?php

/**
 * HeroSlideGuardTest — Test guard & service untuk HeroSlide (restrukturisasi)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 * @updated  2026-08-12 — Restrukturisasi total: rewrite dari nol
 */

// [THECHNOLOGY-CRE] : HeroSlideGuardTest — restrukturisasi total

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\HeroSlide;
use App\Models\HeroSlideConfig;
use App\Services\HeroSlideService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroSlideGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        HeroSlideConfig::firstOrCreate([], ['default_hero_slide_id' => null]);
    }

    // ─── PROMOTE AS DEFAULT ──────────────────────────

    public function test_promote_as_default_works_for_published_active_slide(): void
    {
        $slide = HeroSlide::factory()->create([
            'status' => ContentStatus::Published->value, 'is_active' => true,
        ]);
        HeroSlideService::promoteAsDefault($slide);
        $this->assertEquals($slide->id, HeroSlideConfig::defaultSlideId());
        $this->assertTrue($slide->isDefault());
    }

    public function test_promote_as_default_rejects_draft_slide(): void
    {
        $slide = HeroSlide::factory()->draft()->create([
            'status' => ContentStatus::Draft->value, 'is_active' => true,
        ]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('draft');
        HeroSlideService::promoteAsDefault($slide);
    }

    public function test_promote_as_default_rejects_inactive_slide(): void
    {
        $slide = HeroSlide::factory()->inactive()->create([
            'status' => ContentStatus::Published->value, 'is_active' => false,
        ]);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tidak aktif');
        HeroSlideService::promoteAsDefault($slide);
    }

    public function test_promote_as_default_swaps_existing_default(): void
    {
        $slideA = HeroSlide::factory()->create(['title' => 'A', 'status' => ContentStatus::Published->value, 'is_active' => true]);
        $slideB = HeroSlide::factory()->create(['title' => 'B', 'status' => ContentStatus::Published->value, 'is_active' => true]);

        HeroSlideService::promoteAsDefault($slideA);
        $this->assertEquals($slideA->id, HeroSlideConfig::defaultSlideId());

        HeroSlideService::promoteAsDefault($slideB);
        $this->assertEquals($slideB->id, HeroSlideConfig::defaultSlideId());
        $this->assertFalse($slideA->isDefault());
        $this->assertTrue($slideB->isDefault());
    }

    // ─── IS DEFAULT CHECK ────────────────────────────

    public function test_is_default_returns_false_when_config_is_null(): void
    {
        HeroSlideConfig::current()->update(['default_hero_slide_id' => null]);
        $slide = HeroSlide::factory()->create();
        $this->assertFalse($slide->isDefault());
    }

    public function test_is_default_returns_false_for_non_default_slide(): void
    {
        $default = HeroSlide::factory()->create();
        $other = HeroSlide::factory()->create();
        HeroSlideService::promoteAsDefault($default);
        $this->assertTrue($default->isDefault());
        $this->assertFalse($other->isDefault());
    }

    // ─── GUARD: DELETE ───────────────────────────────

    public function test_cannot_delete_default_slide(): void
    {
        $slide = HeroSlide::factory()->create(['status' => ContentStatus::Published->value, 'is_active' => true]);
        HeroSlideService::promoteAsDefault($slide);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('tidak dapat dihapus');
        $slide->delete();
    }

    public function test_can_delete_non_default_slide(): void
    {
        $slide = HeroSlide::factory()->create();
        $slide->delete();
        $this->assertSoftDeleted($slide);
    }

    // ─── GUARD: DRAFT / NONAKTIF ─────────────────────

    public function test_cannot_draft_default_slide(): void
    {
        $slide = HeroSlide::factory()->create(['status' => ContentStatus::Published->value, 'is_active' => true]);
        HeroSlideService::promoteAsDefault($slide);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('draft');
        $slide->status = ContentStatus::Draft->value;
        $slide->save();
    }

    public function test_cannot_deactivate_default_slide(): void
    {
        $slide = HeroSlide::factory()->create(['status' => ContentStatus::Published->value, 'is_active' => true]);
        HeroSlideService::promoteAsDefault($slide);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('dinonaktifkan');
        $slide->is_active = false;
        $slide->save();
    }

    public function test_can_draft_non_default_slide(): void
    {
        $slide = HeroSlide::factory()->create(['status' => ContentStatus::Published->value, 'is_active' => true]);
        $slide->status = ContentStatus::Draft;
        $slide->save();
        $this->assertEquals(ContentStatus::Draft, $slide->fresh()->status);
    }

    // ─── CONFIG INTEGRITY ────────────────────────────

    public function test_config_always_has_exactly_one_row(): void
    {
        $this->assertEquals(1, HeroSlideConfig::count());
        HeroSlideConfig::current();
        $this->assertEquals(1, HeroSlideConfig::count());
        HeroSlideConfig::current();
        $this->assertEquals(1, HeroSlideConfig::count());
    }

    public function test_config_singleton_blocks_second_insert(): void
    {
        $this->assertEquals(1, HeroSlideConfig::count());

        // Coba insert row kedua via Query Builder → harus ditolak
        $this->expectException(\Illuminate\Database\QueryException::class);

        \Illuminate\Support\Facades\DB::table('hero_slide_config')->insert([
            'id'                    => 2,
            'default_hero_slide_id' => null,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
    }

    public function test_db_level_delete_of_default_slide_is_blocked(): void
    {
        $slide = HeroSlide::factory()->create();
        HeroSlideService::promoteAsDefault($slide);

        // Delete via DB facade (bypass Eloquent) → harus ditolak FK RESTRICT / trigger
        $this->expectException(\Illuminate\Database\QueryException::class);

        \Illuminate\Support\Facades\DB::table('hero_slides')->where('id', $slide->id)->delete();
    }

    public function test_can_delete_slide_after_promoting_another_as_default(): void
    {
        $slideA = HeroSlide::factory()->create(['status' => ContentStatus::Published->value, 'is_active' => true]);
        $slideB = HeroSlide::factory()->create(['status' => ContentStatus::Published->value, 'is_active' => true]);

        HeroSlideService::promoteAsDefault($slideA);
        HeroSlideService::promoteAsDefault($slideB); // B now default

        // A is no longer default → should be deletable
        $slideA->delete();
        $this->assertSoftDeleted($slideA);
    }

    // ─── QUERY BUILDER BYPASS GUARDS ─────────────────

    public function test_query_builder_cannot_draft_default_slide(): void
    {
        $slide = HeroSlide::factory()->create(['status' => ContentStatus::Published->value, 'is_active' => true]);
        HeroSlideService::promoteAsDefault($slide);

        $this->expectException(\Illuminate\Database\QueryException::class);

        \Illuminate\Support\Facades\DB::table('hero_slides')
            ->where('id', $slide->id)
            ->update(['status' => 'draft']);
    }

    public function test_query_builder_cannot_deactivate_default_slide(): void
    {
        $slide = HeroSlide::factory()->create(['status' => ContentStatus::Published->value, 'is_active' => true]);
        HeroSlideService::promoteAsDefault($slide);

        $this->expectException(\Illuminate\Database\QueryException::class);

        \Illuminate\Support\Facades\DB::table('hero_slides')
            ->where('id', $slide->id)
            ->update(['is_active' => false]);
    }

    public function test_config_cannot_be_nulled_after_first_init(): void
    {
        // Init: set default
        $slide = HeroSlide::factory()->create();
        HeroSlideService::promoteAsDefault($slide);
        $this->assertNotNull(HeroSlideConfig::defaultSlideId());

        // Coba null-kan langsung via model (bypass service)
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('mengosongkan');

        HeroSlideConfig::current()->update(['default_hero_slide_id' => null]);
    }

    // ─── RACE CONDITION ──────────────────────────────

    public function test_concurrent_promotes_result_in_consistent_state(): void
    {
        $slideA = HeroSlide::factory()->create(['title' => 'A', 'status' => ContentStatus::Published->value, 'is_active' => true]);
        $slideB = HeroSlide::factory()->create(['title' => 'B', 'status' => ContentStatus::Published->value, 'is_active' => true]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($slideA) {
            HeroSlideService::promoteAsDefault($slideA);
        });
        \Illuminate\Support\Facades\DB::transaction(function () use ($slideB) {
            HeroSlideService::promoteAsDefault($slideB);
        });

        $config = HeroSlideConfig::first()->refresh();
        $this->assertEquals($slideB->id, $config->default_hero_slide_id);
        $this->assertEquals(1, HeroSlideConfig::count());
    }

    // ─── REGRESI ─────────────────────────────────────

    public function test_can_update_non_guard_fields_on_default_slide(): void
    {
        $slide = HeroSlide::factory()->create(['title' => 'Old', 'status' => ContentStatus::Published->value, 'is_active' => true]);
        HeroSlideService::promoteAsDefault($slide);

        $slide->title = 'New';
        $slide->save();

        $this->assertEquals('New', $slide->fresh()->title);
        $this->assertTrue($slide->fresh()->isDefault());
    }

    // ─── CONFIG TARGET VALIDITY (Issue #3) ────────────

    public function test_query_builder_cannot_point_config_to_draft_slide(): void
    {
        $draftSlide = HeroSlide::factory()->draft()->create([
            'status'    => ContentStatus::Draft->value,
            'is_active' => true,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        HeroSlideConfig::query()->where('id', 1)->update([
            'default_hero_slide_id' => $draftSlide->id,
        ]);
    }

    public function test_query_builder_cannot_point_config_to_inactive_slide(): void
    {
        $inactiveSlide = HeroSlide::factory()->inactive()->create([
            'status'    => ContentStatus::Published->value,
            'is_active' => false,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        HeroSlideConfig::query()->where('id', 1)->update([
            'default_hero_slide_id' => $inactiveSlide->id,
        ]);
    }

    public function test_promote_as_default_still_works_with_target_validity_trigger(): void
    {
        $slide = HeroSlide::factory()->create([
            'status'    => ContentStatus::Published->value,
            'is_active' => true,
        ]);

        // Harus berhasil — slide published+aktif lolos trigger
        HeroSlideService::promoteAsDefault($slide);

        $this->assertEquals($slide->id, HeroSlideConfig::defaultSlideId());
        $this->assertTrue($slide->isDefault());
    }
}
