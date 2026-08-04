<?php

// [THECHNOLOGY-CRE-DSE] : migration untuk menambahkan flag eksplisit is_super_admin di tabel users
// Flag boolean ini menggantikan logika dinamis hasAllPermissions() yang rapuh — super-admin dikenali
// secara eksplisit tanpa bergantung pada jumlah/daftar permission yang bisa berubah di Fase 3+.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // [THECHNOLOGY-CRE-DSE] : default false — hanya admin awal yang akan di-set true via seeder
            $table->boolean('is_super_admin')->default(false)->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_super_admin');
        });
    }
};
