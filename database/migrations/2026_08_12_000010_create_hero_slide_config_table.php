<?php

/**
 * Migration: Buat tabel hero_slide_config — single source of truth untuk default slide
 *
 * Menggantikan kolom boolean is_default di hero_slides yang terbukti rawan:
 * - Bisa 0 default (tidak ada is_default=true)
 * - Bisa >1 default (race condition)
 * - Butuh 3 ronde patch CRITICAL CGX (trigger, token, swap flag, flag table)
 *
 * Desain baru:
 * - Tabel hanya berisi 1 row selamanya (firstOrCreate di model)
 * - Kolom default_hero_slide_id: FK ke hero_slides.id, ON DELETE SET NULL
 *   → kalau slide default dihapus, config otomatis jadi NULL (aman)
 *
 * Data migration: kalau ada hero_slides dengan is_default=true saat ini,
 * insert id-nya ke hero_slide_config sebelum kolom is_default dihapus
 * (migration berikutnya).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-12 — Restrukturisasi arsitektur: single source of truth
 */

// [THECHNOLOGY-CRE] : hero_slide_config table — ganti boolean is_default

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat tabel konfigurasi
        Schema::create('hero_slide_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('default_hero_slide_id')
                ->nullable()
                ->constrained('hero_slides')
                ->nullOnDelete();
            $table->timestamps();
        });

        // 2. Data migration: cari slide default existing dan simpan ke config
        $defaultSlide = DB::table('hero_slides')
            ->where('is_default', true)
            ->first();

        DB::table('hero_slide_config')->insert([
            'default_hero_slide_id' => $defaultSlide?->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slide_config');
    }
};
