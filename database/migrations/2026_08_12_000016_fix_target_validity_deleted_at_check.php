<?php

/**
 * Migration: Fix celah deleted_at di trigger target-validity
 *
 * Issue CGX round 6 (CRITICAL): Trigger hero_slide_config_guard_target_valid
 * (dibuat di migration 000014) cek status='published' dan is_active=1 tapi
 * tidak cek deleted_at IS NULL.
 *
 * Skenario bypass: slide non-default di-soft-delete → status & is_active
 * tetap sama → pointer config bisa diarahkan ke slide yang sudah masuk Trash
 * via Query Builder.
 *
 * Fix: DROP & RECREATE trigger dengan tambahan kondisi `AND deleted_at IS NULL`
 * pada subquery target slide.
 *
 * Migration induk (000014) juga sudah di-update untuk developer baru yang
 * menjalankan migration dari awal.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-12 — CGX round 6: tambah cek deleted_at di target-validity
 */

// [THECHNOLOGY-CRE] : DB trigger fix — recreate target-validity dengan deleted_at IS NULL

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        // Drop trigger existing (dibuat di 000014)
        DB::statement('DROP TRIGGER IF EXISTS hero_slide_config_guard_target_valid');

        // Recreate dengan cek deleted_at IS NULL
        if ($driver === 'mysql') {
            DB::statement('
                CREATE TRIGGER hero_slide_config_guard_target_valid
                BEFORE UPDATE ON hero_slide_config
                FOR EACH ROW
                BEGIN
                    IF NEW.default_hero_slide_id IS NOT NULL
                       AND NEW.default_hero_slide_id != OLD.default_hero_slide_id
                    THEN
                        IF NOT EXISTS (
                            SELECT 1 FROM hero_slides
                            WHERE id = NEW.default_hero_slide_id
                              AND status = \'published\'
                              AND is_active = 1
                              AND deleted_at IS NULL
                        ) THEN
                            SIGNAL SQLSTATE \'45000\'
                                SET MESSAGE_TEXT = \'Slide target harus published, aktif, dan tidak dihapus untuk dijadikan default.\';
                        END IF;
                    END IF;
                END;
            ');
        } elseif ($driver === 'sqlite') {
            DB::statement('
                CREATE TRIGGER hero_slide_config_guard_target_valid
                BEFORE UPDATE ON hero_slide_config
                FOR EACH ROW
                WHEN NEW.default_hero_slide_id IS NOT NULL
                 AND NEW.default_hero_slide_id IS NOT OLD.default_hero_slide_id
                 AND NOT EXISTS (
                     SELECT 1 FROM hero_slides
                     WHERE id = NEW.default_hero_slide_id
                       AND status = \'published\'
                       AND is_active = 1
                       AND deleted_at IS NULL
                 )
                BEGIN
                    SELECT RAISE(ABORT, \'Slide target harus published, aktif, dan tidak dihapus untuk dijadikan default.\');
                END;
            ');
        }
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS hero_slide_config_guard_target_valid');

        // Kembalikan trigger versi sebelumnya (tanpa deleted_at check)
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('
                CREATE TRIGGER hero_slide_config_guard_target_valid
                BEFORE UPDATE ON hero_slide_config
                FOR EACH ROW
                BEGIN
                    IF NEW.default_hero_slide_id IS NOT NULL
                       AND NEW.default_hero_slide_id != OLD.default_hero_slide_id
                    THEN
                        IF NOT EXISTS (
                            SELECT 1 FROM hero_slides
                            WHERE id = NEW.default_hero_slide_id
                              AND status = \'published\'
                              AND is_active = 1
                        ) THEN
                            SIGNAL SQLSTATE \'45000\'
                                SET MESSAGE_TEXT = \'Slide target harus published dan aktif untuk dijadikan default.\';
                        END IF;
                    END IF;
                END;
            ');
        } elseif ($driver === 'sqlite') {
            DB::statement('
                CREATE TRIGGER hero_slide_config_guard_target_valid
                BEFORE UPDATE ON hero_slide_config
                FOR EACH ROW
                WHEN NEW.default_hero_slide_id IS NOT NULL
                 AND NEW.default_hero_slide_id IS NOT OLD.default_hero_slide_id
                 AND NOT EXISTS (
                     SELECT 1 FROM hero_slides
                     WHERE id = NEW.default_hero_slide_id
                       AND status = \'published\'
                       AND is_active = 1
                 )
                BEGIN
                    SELECT RAISE(ABORT, \'Slide target harus published dan aktif untuk dijadikan default.\');
                END;
            ');
        }
    }
};
