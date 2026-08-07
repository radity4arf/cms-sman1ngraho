<?php

/**
 * Migration: create alumni_testimonials table (RT-03 — Alumni Menginspirasi)
 *
 * @author   DSE (Delia Tse)
 * @created  2026-08-08
 */

// [THECHNOLOGY-CRE] : migration alumni_testimonials

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('graduation_year');
            $table->string('profession');
            $table->text('quote');
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
        Schema::dropIfExists('alumni_testimonials');
    }
};
