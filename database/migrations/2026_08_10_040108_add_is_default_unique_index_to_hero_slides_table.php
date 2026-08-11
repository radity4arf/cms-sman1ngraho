<?php

/**
 * Migration: tambah partial unique index untuk is_default=true di hero_slides
 *
 * Invarian: tepat satu is_default=true setiap saat.
 * MySQL 8.0.13+ mendukung functional index via CASE expression.
 * SQLite 3.8.0+ mendukung partial unique index via WHERE clause.
 * NULL dari CASE (MySQL) atau row tidak matching WHERE (SQLite)
 * tidak dihitung sebagai duplicate.
 * Fallback: aplikasi-level transaction + lockForUpdate di model.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 * @updated  2026-08-11 — tambah SQLite partial unique index support (CGX review Fase 3)
 */

// [THECHNOLOGY-CRE] : partial unique index is_default — cegah race condition di level database
// [THECHNOLOGY-FIX] : Tambah SQLite partial index support — SQLite 3.8.0+ mendukung WHERE clause

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // [THECHNOLOGY-FIX] : driver-aware partial unique index.
        // MySQL 8.0.13+: functional index via CASE expression.
        // SQLite 3.8.0+: partial index via WHERE clause.
        // Aplikasi-level lock (HeroSlide model) tetap menjadi guard utama.
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                'CREATE UNIQUE INDEX hero_slides_is_default_true_unique'
                . ' ON hero_slides ((CASE WHEN is_default = 1 THEN 1 END))'
            );
        } elseif ($driver === 'sqlite') {
            // SQLite 3.8.0+ mendukung partial unique index dengan WHERE clause.
            // Hanya row dengan is_default = 1 yang diindeks; duplikat di antaranya
            // akan memicu constraint violation.
            DB::statement(
                'CREATE UNIQUE INDEX hero_slides_is_default_true_unique'
                . ' ON hero_slides (is_default) WHERE is_default = 1'
            );
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropIndex('hero_slides_is_default_true_unique');
        });
    }
};
