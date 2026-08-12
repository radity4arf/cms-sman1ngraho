<?php

/**
 * Migration: Tambah database trigger untuk guard is_default di hero_slides
 *
 * Trigger di level database mencegah Query Builder bypass (e.g.,
 * HeroSlide::where(...)->update(['is_default' => false])) yang tidak
 * melewati Eloquent model events.
 *
 * Guard:
 *   - Cegah delete slide default (DELETE row is_default=1)
 *   - Cegah nonaktifkan slide default (UPDATE is_active 1→0 pada default)
 *   - Cegah draft slide default (UPDATE status ke 'draft' pada default)
 *
 * Catatan: guard "unset is_default terakhir" (1→0 tanpa pengganti) tidak
 * bisa diimplementasikan sebagai row-level trigger karena selama transaksi
 * swap yang sah, akan ada momen 0 default sebelum default baru diset.
 * Guard ini sudah ditangani di level model (updating event) dan partial
 * unique index (mencegah >1 default). Database trigger deferred tidak
 * didukung MySQL/SQLite — hanya PostgreSQL.
 *
 * MySQL 5.5+ / SQLite 3.8.0+ support.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-12 — CLX review fix: database-level guard mencegah bypass Query Builder
 */

// [THECHNOLOGY-CRE] : DB trigger is_default guard — cegah Query Builder bypass

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            // ── Guard 1: Cegah nonaktifkan slide default ──
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_deactivate
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                BEGIN
                    IF OLD.is_default = 1 AND NEW.is_active = 0 THEN
                        SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = \'Slide default tidak dapat dinonaktifkan.\';
                    END IF;
                END;
            ');

            // ── Guard 2: Cegah draft slide default ──
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_draft
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                BEGIN
                    IF OLD.is_default = 1 AND NEW.status = \'draft\' THEN
                        SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = \'Slide default tidak dapat diubah menjadi draft.\';
                    END IF;
                END;
            ');

            // ── Guard 3: Cegah delete slide default ──
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_delete
                BEFORE DELETE ON hero_slides
                FOR EACH ROW
                BEGIN
                    IF OLD.is_default = 1 THEN
                        SIGNAL SQLSTATE \'45000\'
                            SET MESSAGE_TEXT = \'Slide default tidak dapat dihapus.\';
                    END IF;
                END;
            ');
        } elseif ($driver === 'sqlite') {
            // ── Guard 1: Cegah nonaktifkan slide default ──
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_deactivate
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                WHEN OLD.is_default = 1 AND NEW.is_active = 0
                BEGIN
                    SELECT RAISE(ABORT, \'Slide default tidak dapat dinonaktifkan.\');
                END;
            ');

            // ── Guard 2: Cegah draft slide default ──
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_draft
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                WHEN OLD.is_default = 1 AND NEW.status = \'draft\'
                BEGIN
                    SELECT RAISE(ABORT, \'Slide default tidak dapat diubah menjadi draft.\');
                END;
            ');

            // ── Guard 3: Cegah delete slide default ──
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_delete
                BEFORE DELETE ON hero_slides
                FOR EACH ROW
                WHEN OLD.is_default = 1
                BEGIN
                    SELECT RAISE(ABORT, \'Slide default tidak dapat dihapus.\');
                END;
            ');
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_deactivate');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_draft');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_delete');
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_deactivate');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_draft');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_delete');
        }
    }
};
