<?php

/**
 * Migration: tambah partial unique index untuk is_default=true di hero_slides
 *
 * Invarian: tepat satu is_default=true setiap saat.
 * MySQL 8.0.13+ mendukung functional index via CASE expression.
 * NULL dari CASE (is_default=false) tidak dihitung sebagai duplicate.
 * Fallback: aplikasi-level transaction + lockForUpdate di model.
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-10
 */

// [THECHNOLOGY-CRE] : partial unique index is_default — cegah race condition di level database

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // [THECHNOLOGY-CRE] : functional unique index — hanya enforce unique pada is_default=true
        // CASE WHEN is_default = 1 THEN 1 END mengembalikan 1 untuk record default,
        // NULL untuk non-default. MySQL mengizinkan multiple NULL di unique index,
        // sehingga hanya record is_default=true yang dibatasi maksimal 1.
        DB::statement(
            'CREATE UNIQUE INDEX hero_slides_is_default_true_unique'
            . ' ON hero_slides ((CASE WHEN is_default = 1 THEN 1 END))'
        );
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropIndex('hero_slides_is_default_true_unique');
        });
    }
};
