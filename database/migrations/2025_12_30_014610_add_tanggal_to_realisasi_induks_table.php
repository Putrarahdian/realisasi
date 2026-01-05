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
        Schema::table('realisasi_induks', function (Blueprint $table) {
            // taruh setelah tahun kalau ada, biar rapi
            $table->date('tanggal')->nullable()->after('tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_induks', function (Blueprint $table) {
            $table->dropColumn('tanggal');
        });
    }
};
