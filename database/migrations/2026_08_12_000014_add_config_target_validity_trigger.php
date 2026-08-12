<?php

/**
 * Migration: DB trigger guard — validasi kelayakan slide target di hero_slide_config
 *
 * Issue CGX #3 (terlewat di report24): pointer config bisa diarahkan ke slide
 * draft/nonaktif via Query Builder.
 *
 * Masalah: Validasi "slide harus published+aktif" cuma di HeroSlideService (PHP).
 * Query Builder bisa update hero_slide_config.default_hero_slide_id langsung
 * ke slide draft/nonaktif — FK cuma jamin ID exists, tidak jamin kelayakan publik.
 *
 * Fix: BEFORE UPDATE trigger di hero_slide_config — query hero_slides untuk
 * cek status='published' DAN is_active=1 pada slide target.
 *
 * Trigger ini adalah lapisan kedua (defense-in-depth), bukan pengganti
 * validasi service. promoteAsDefault() yang sah tetap lolos karena slide-nya
 * memang published+aktif.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-12 — CGX round 4 issue #3: validasi pointer config
 */

// [THECHNOLOGY-CRE] : DB trigger — validasi kelayakan slide target di config

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS hero_slide_config_guard_target_valid');
    }
};
