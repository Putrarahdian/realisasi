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
            $table->enum('triwulan', ['I','II','III','IV'])->nullable()->after('tanggal');
            $table->index(['realisasi_induk_id', 'triwulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keuangan', function (Blueprint $table) {
            $table->dropIndex(['realisasi_induk_id', 'triwulan']);
            $table->dropColumn('triwulan');
        });
    }
};
