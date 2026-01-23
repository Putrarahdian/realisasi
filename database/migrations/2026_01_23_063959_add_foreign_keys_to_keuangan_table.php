<?php

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
        Schema::table('keuangan', function (Blueprint $table) {

            // FK ke tabel realisasi_induks (sesuai foto kamu)
            // on delete -> set null biar transaksi keuangan tetap aman walau induk dihapus
            $table->foreign('realisasi_induk_id')
                  ->references('id')
                  ->on('realisasi_induks')
                  ->nullOnDelete();

            // FK created_by ke tabel pengguna (karena user table kamu namanya 'pengguna')
            $table->foreign('created_by')
                  ->references('id')
                  ->on('pengguna')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan', function (Blueprint $table) {
            // nama constraint default laravel: {table}_{column}_foreign
            $table->dropForeign(['realisasi_induk_id']);
            $table->dropForeign(['created_by']);
        });
    }
};
