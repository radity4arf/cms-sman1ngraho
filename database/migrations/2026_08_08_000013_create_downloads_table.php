<?php

/**
 * Migration: create downloads table (RT-10 — Unduhan)
 *
 * download_category_id FK RESTRICT (hapus kategori ditolak jika masih ada file).
 * File via spatie/laravel-medialibrary (koleksi: file).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : migration downloads — Unduhan

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('download_category_id')->constrained('download_categories')->restrictOnDelete();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('downloads');
    }
};
