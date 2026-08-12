<?php

/**
 * Migration: Tutup 3 celah tersisa + SQLite id-update guard — audit menyeluruh
 *
 * Issue #1: Config row (id=1) bisa di-DELETE → current() diam-diam bikin ulang kosong
 * Issue #2: Soft-delete (deleted_at) slide default tidak dicegah trigger existing
 * Issue #3: Pointer config bisa di-null-kan setelah terisi (transisi non-null → null)
 *
 * Plus: SQLite tidak punya CHECK(id=1) → perlu BEFORE UPDATE trigger untuk mencegah
 * UPDATE id pada row singleton.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-12 — CGX round 5: audit menyeluruh + 3 celah
 */

// [THECHNOLOGY-CRE] : Trigger delete-config + soft-delete + null-pointer + SQLite id-update guard

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        // ═══════════════════════════════════════════════
        // Issue #1: Cegah DELETE row config id=1
        // ═══════════════════════════════════════════════
        if ($driver === 'mysql') {
            DB::statement('
                CREATE TRIGGER hero_slide_config_guard_delete
                BEFORE DELETE ON hero_slide_config
                FOR EACH ROW
                BEGIN
                    IF OLD.id = 1 THEN
                        SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = \'Row konfigurasi default tidak dapat dihapus.\';
                    END IF;
                END;
            ');
        } elseif ($driver === 'sqlite') {
            DB::statement('
                CREATE TRIGGER hero_slide_config_guard_delete
                BEFORE DELETE ON hero_slide_config
                FOR EACH ROW
                WHEN OLD.id = 1
                BEGIN
                    SELECT RAISE(ABORT, \'Row konfigurasi default tidak dapat dihapus.\');
                END;
            ');
        }

        // ═══════════════════════════════════════════════
        // Issue #2: Cegah soft-delete slide default
        // ═══════════════════════════════════════════════
        if ($driver === 'mysql') {
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_soft_delete_cfg
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                BEGIN
                    IF NEW.deleted_at IS NOT NULL AND OLD.deleted_at IS NULL AND EXISTS (
                        SELECT 1 FROM hero_slide_config WHERE default_hero_slide_id = OLD.id
                    ) THEN
                        SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = \'Slide default tidak dapat dihapus (soft-delete). Promosikan slide lain terlebih dahulu.\';
                    END IF;
                END;
            ');
        } elseif ($driver === 'sqlite') {
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_soft_delete_cfg
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                WHEN NEW.deleted_at IS NOT NULL AND OLD.deleted_at IS NULL
                 AND EXISTS (SELECT 1 FROM hero_slide_config WHERE default_hero_slide_id = OLD.id)
                BEGIN
                    SELECT RAISE(ABORT, \'Slide default tidak dapat dihapus (soft-delete). Promosikan slide lain terlebih dahulu.\');
                END;
            ');
        }

        // ═══════════════════════════════════════════════
        // Issue #3: Cegah null-kan pointer config
        // ═══════════════════════════════════════════════
        if ($driver === 'mysql') {
            DB::statement('
                CREATE TRIGGER hero_slide_config_guard_null_pointer
                BEFORE UPDATE ON hero_slide_config
                FOR EACH ROW
                BEGIN
                    IF NEW.default_hero_slide_id IS NULL AND OLD.default_hero_slide_id IS NOT NULL THEN
                        SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = \'Tidak dapat mengosongkan default slide. Promosikan slide lain terlebih dahulu.\';
                    END IF;
                END;
            ');
        } elseif ($driver === 'sqlite') {
            DB::statement('
                CREATE TRIGGER hero_slide_config_guard_null_pointer
                BEFORE UPDATE ON hero_slide_config
                FOR EACH ROW
                WHEN NEW.default_hero_slide_id IS NULL AND OLD.default_hero_slide_id IS NOT NULL
                BEGIN
                    SELECT RAISE(ABORT, \'Tidak dapat mengosongkan default slide. Promosikan slide lain terlebih dahulu.\');
                END;
            ');
        }

        // ═══════════════════════════════════════════════
        // SQLite only: Cegah UPDATE id pada singleton
        // (MySQL sudah di-cover CHECK(id=1))
        // ═══════════════════════════════════════════════
        if ($driver === 'sqlite') {
            DB::statement('
                CREATE TRIGGER hero_slide_config_guard_id_update
                BEFORE UPDATE ON hero_slide_config
                FOR EACH ROW
                WHEN OLD.id = 1 AND NEW.id != 1
                BEGIN
                    SELECT RAISE(ABORT, \'Tidak dapat mengubah id row konfigurasi.\');
                END;
            ');
        }
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS hero_slide_config_guard_delete');
        DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_soft_delete_cfg');
        DB::statement('DROP TRIGGER IF EXISTS hero_slide_config_guard_null_pointer');
        DB::statement('DROP TRIGGER IF EXISTS hero_slide_config_guard_id_update');
    }
};
