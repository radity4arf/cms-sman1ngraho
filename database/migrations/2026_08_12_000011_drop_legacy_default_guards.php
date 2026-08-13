<?php

/**
 * Migration: Bersihkan mekanisme guard lama yang sudah tidak relevan
 *
 * Dengan hero_slide_config sebagai single source of truth, semua mekanisme
 * guard is_default lama harus dihapus:
 *
 *   1. DB trigger (hero_slides_guard_default_deactivate, _draft, _delete, _unset)
 *   2. Tabel hero_slide_swap_flags (SQLite, untuk flag transaksi swap)
 *   3. Partial unique index hero_slides_is_default_true_unique
 *
 * Semua mekanisme ini adalah patch yang menumpuk dari 3 ronde CGX review —
 * sekarang tidak diperlukan karena struktur schema baru tidak memungkinkan
 * state 0 atau >1 default.
 *
 * Kolom is_default sendiri dihapus di migration berikutnya (000012).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-12 — Restrukturisasi: hapus semua patch lama
 */

// [THECHNOLOGY-DEL] : Drop semua trigger + swap_flags + partial unique index lama

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_deactivate');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_draft');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_delete');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_unset');

            // [THECHNOLOGY-FIX] : MySQL tidak support "DROP INDEX IF EXISTS" (beda SQLite).
            // Cek keberadaan index dulu via information_schema.statistics, baru drop.
            $indexExists = DB::select("
                SELECT COUNT(*) AS cnt FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = 'hero_slides'
                AND index_name = 'hero_slides_is_default_true_unique'
            ");

            if ($indexExists[0]->cnt > 0) {
                DB::statement('DROP INDEX hero_slides_is_default_true_unique ON hero_slides');
            }
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_deactivate');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_draft');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_delete');
            DB::statement('DROP TRIGGER IF EXISTS hero_slides_guard_default_unset');

            // Drop flag table (regular table, dibuat migration 000002)
            Schema::dropIfExists('hero_slide_swap_flags');

            // Drop partial unique index
            DB::statement('DROP INDEX IF EXISTS hero_slides_is_default_true_unique');
        }
    }

    public function down(): void
    {
        // No rollback — trigger dan mekanisme lama sudah obsolete.
        // Kalau butuh rollback ke desain lama, restore dari backup.
    }
};
