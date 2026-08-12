<?php

/**
 * Migration: Tambah DB trigger + flag table — guard unset is_default pada hero_slides
 *
 * Trigger ini mencegah Query Builder bypass untuk operasi unset is_default
 * (true→false) — misal: HeroSlide::query()->where('id', X)->update(['is_default' => false]).
 * Operasi ini TIDAK memicu Eloquent model events, jadi model-level guard
 * tidak berlaku. Trigger menutup celah ini.
 *
 * Mekanisme:
 *   - Sebelum swap sah (promoteAsDefault/saving event), HeroSlide::beginSwap()
 *     menyetel penanda transaksi:
 *       MySQL:   SET @hero_swapping_default = 1 (session variable)
 *       SQLite:  INSERT INTO hero_slide_swap_flags (flag) VALUES (1) (regular table)
 *   - Trigger membaca penanda ini. Jika penanda TIDAK tersetel → unset
 *     adalah operasi ilegal (Query Builder bypass) → BLOCK.
 *   - Setelah swap selesai (finally), HeroSlide::endSwap() mereset penanda.
 *
 * Table `hero_slide_swap_flags` adalah regular table (bukan temp) supaya:
 *   1. Survive RefreshDatabase (migrate:fresh ulangi CREATE → table selalu ada)
 *   2. Tidak ada masalah "no such table" dari trigger saat table belum dibuat
 *
 * Catatan risiko (sudah didokumentasikan & dimitigasi):
 *   - Session variable MySQL connection-scoped (bukan transaction-scoped) —
 *     di-reset manual di finally.
 *   - Dalam connection pool, variable BISA leak ke request berikutnya jika
 *     finally tidak jalan (fatal error). Risiko: false negative — unset ilegal
 *     di request berikutnya diizinkan. Mitigasi: code review + test coverage
 *     memastikan finally selalu dieksekusi.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-12 — CGX fix lanjutan: tutup celah Query Builder bypass
 */

// [THECHNOLOGY-CRE] : DB trigger + flag table — guard unset is_default via Query Builder bypass

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            // Trigger MySQL: blokir unset is_default jika @hero_swapping_default tidak disetel.
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_unset
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                BEGIN
                    IF OLD.is_default = 1 AND NEW.is_default = 0 THEN
                        IF @hero_swapping_default IS NULL OR @hero_swapping_default = 0 THEN
                            SIGNAL SQLSTATE \'45000\'
                                SET MESSAGE_TEXT = \'Tidak dapat menghapus status default di luar mekanisme swap resmi. Gunakan HeroSlideService::promoteAsDefault().\';
                        END IF;
                    END IF;
                END;
            ');
        } elseif ($driver === 'sqlite') {
            // Table flag — regular table, dibuat ulang oleh migrate:fresh / RefreshDatabase
            Schema::create('hero_slide_swap_flags', function (Blueprint $table) {
                $table->id();
                $table->integer('flag')->default(1);
            });

            // Trigger SQLite: blokir unset is_default jika table flag kosong.
            DB::statement('
                CREATE TRIGGER hero_slides_guard_default_unset
                BEFORE UPDATE ON hero_slides
                FOR EACH ROW
                WHEN OLD.is_default = 1 AND NEW.is_default = 0
                     AND NOT EXISTS (SELECT 1 FROM hero_slide_swap_flags)
                BEGIN
                    SELECT RAISE(ABORT, \'Tidak dapat menghapus status default di luar mekanisme swap resmi. Gunakan HeroSlideService::promoteAsDefault().\');
                END;
            ');
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_unset');
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_unset');
            Schema::dropIfExists('hero_slide_swap_flags');
        }
    }
};
