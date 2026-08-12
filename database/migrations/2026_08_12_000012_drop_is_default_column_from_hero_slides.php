<?php

/**
 * Migration: Hapus kolom is_default dari hero_slides
 *
 * Kolom boolean is_default digantikan oleh hero_slide_config.default_hero_slide_id.
 * Data sudah dimigrasikan di migration 000010, trigger/index dihapus di 000011.
 *
 * Setelah migration ini:
 * - Tidak ada lagi boolean flag yang bisa 0 atau >1
 * - Default slide ditentukan oleh 1 row di hero_slide_config
 * - Atomicity native: UPDATE 1 row = operasi atomik
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-12 — Restrukturisasi: drop is_default column
 */

// [THECHNOLOGY-DEL] : Drop kolom is_default — digantikan hero_slide_config

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }

    public function down(): void
    {
        Schema::table('hero_slides', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('cta_url');
        });
    }
};
