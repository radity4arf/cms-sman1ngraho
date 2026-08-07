<?php

/**
 * Migration: create extracurriculars table (RT-08 — Ekstrakurikuler)
 *
 * category: nullable enum (olahraga, seni, akademik, keagamaan).
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : migration extracurriculars — Ekstrakurikuler

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable(); // enum handled at app level
            $table->string('coach')->nullable();
            $table->string('schedule', 100)->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
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
        Schema::dropIfExists('extracurriculars');
    }
};
