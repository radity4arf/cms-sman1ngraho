<?php

/**
 * Migration: Enforce hero_slide_config singleton + FK restrict + DB trigger guard
 *
 * Issue CGX #1: hero_slide_config belum benar-benar singleton (bisa >1 row via race condition)
 * Issue CGX #2: Query Builder bisa delete/draft/deactivate slide default; FK SET NULL memfasilitasi
 *
 * Fix:
 *   1. Singleton enforcement:
 *      - MySQL:   CHECK(id = 1) — blokir INSERT kedua
 *      - SQLite:  BEFORE INSERT trigger — blokir kalau sudah ada row
 *      - Model:   firstOrCreate(['id' => 1]) — idempoten, selalu tepat 1 row
 *   2. FK ON DELETE RESTRICT (MySQL) — slide default TIDAK BISA dihapus di level database
 *      SQLite tidak support ALTER FK → trigger BEFORE DELETE sebagai gantinya
 *   3. DB trigger draft/deactivate guard untuk slide default:
 *      Cegah Query Builder UPDATE status='draft' atau is_active=0 pada slide yg sedang default
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-12 — CGX round 4: DB-level enforcement
 */

// [THECHNOLOGY-CRE] : DB-level singleton + FK restrict + trigger guard

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        // ── 1. Pastikan row id=1 selalu ada ──
        $exists = DB::table('hero_slide_config')->where('id', 1)->exists();

        if ($exists) {
            // Update existing row (mungkin dari migration 000010) supaya id=1
            DB::table('hero_slide_config')->update(['id' => 1]);
        } else {
            // Insert row eksplisit — migration adalah source of truth, bukan lazy runtime
            DB::table('hero_slide_config')->insert([
                'id'                    => 1,
                'default_hero_slide_id' => null,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }

        if ($driver === 'mysql') {
            // ── 2. CHECK constraint — block INSERT kedua ──
            DB::statement('ALTER TABLE hero_slide_config ADD CONSTRAINT hero_slide_config_singleton CHECK (id = 1)');

            // ── 3. FK: RESTRICT instead of SET NULL ──
            Schema::table('hero_slide_config', function (Blueprint $table) {
                $table->dropForeign(['default_hero_slide_id']);
                $table->foreign('default_hero_slide_id')
                    ->references('id')->on('hero_slides')
                    ->restrictOnDelete();
            });

            // ── 4. Trigger: cegah draft slide default ──
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_draft_cfg
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                BEGIN
                    IF NEW.status = \'draft\' AND EXISTS (
                        SELECT 1 FROM hero_slide_config WHERE default_hero_slide_id = OLD.id
                    ) THEN
                        SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = \'Slide default tidak dapat diubah menjadi draft. Promosikan slide lain terlebih dahulu.\';
                    END IF;
                END;
            ');

            // ── 5. Trigger: cegah nonaktifkan slide default ──
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_deactivate_cfg
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                BEGIN
                    IF NEW.is_active = 0 AND EXISTS (
                        SELECT 1 FROM hero_slide_config WHERE default_hero_slide_id = OLD.id
                    ) THEN
                        SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = \'Slide default tidak dapat dinonaktifkan. Promosikan slide lain terlebih dahulu.\';
                    END IF;
                END;
            ');
        } elseif ($driver === 'sqlite') {
            // ── 2. Trigger: singleton — block INSERT kalau sudah ada row ──
            DB::statement('
                CREATE TRIGGER hero_slide_config_singleton_guard
                BEFORE INSERT ON hero_slide_config
                FOR EACH ROW
                WHEN (SELECT COUNT(*) FROM hero_slide_config) >= 1
                BEGIN
                    SELECT RAISE(ABORT, \'hero_slide_config must have exactly 1 row.\');
                END;
            ');

            // ── 3. Trigger: cegah delete slide default — pengganti FK RESTRICT ──
            // SQLite tidak support ALTER TABLE DROP FOREIGN KEY / ADD CONSTRAINT,
            // jadi trigger adalah alternatif yang valid.
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_delete_cfg
                BEFORE DELETE ON hero_slides
                FOR EACH ROW
                WHEN EXISTS (SELECT 1 FROM hero_slide_config WHERE default_hero_slide_id = OLD.id)
                BEGIN
                    SELECT RAISE(ABORT, \'Slide default tidak dapat dihapus. Promosikan slide lain terlebih dahulu.\');
                END;
            ');

            // ── 4. Trigger: cegah draft slide default ──
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_draft_cfg
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                WHEN NEW.status = \'draft\' AND EXISTS (
                    SELECT 1 FROM hero_slide_config WHERE default_hero_slide_id = OLD.id
                )
                BEGIN
                    SELECT RAISE(ABORT, \'Slide default tidak dapat diubah menjadi draft. Promosikan slide lain terlebih dahulu.\');
                END;
            ');

            // ── 5. Trigger: cegah nonaktifkan slide default ──
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_deactivate_cfg
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                WHEN NEW.is_active = 0 AND EXISTS (
                    SELECT 1 FROM hero_slide_config WHERE default_hero_slide_id = OLD.id
                )
                BEGIN
                    SELECT RAISE(ABORT, \'Slide default tidak dapat dinonaktifkan. Promosikan slide lain terlebih dahulu.\');
                END;
            ');
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_draft_cfg');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_deactivate_cfg');

            Schema::table('hero_slide_config', function (Blueprint $table) {
                $table->dropForeign(['default_hero_slide_id']);
                $table->foreign('default_hero_slide_id')
                    ->references('id')->on('hero_slides')
                    ->nullOnDelete();
            });

            DB::statement('ALTER TABLE hero_slide_config DROP CONSTRAINT IF EXISTS hero_slide_config_singleton');
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS hero_slide_config_singleton_guard');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_delete_cfg');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_draft_cfg');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_deactivate_cfg');
        }
    }
};
