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

            // === KOLOM LAMA YANG DIHAPUS ===
            if (Schema::hasColumn('realisasi_induks', 'sasaran_strategis')) {
                $table->dropColumn('sasaran_strategis');
            }

            if (Schema::hasColumn('realisasi_induks', 'program')) {
                $table->dropColumn('program');
            }

            if (Schema::hasColumn('realisasi_induks', 'indikator')) {
                $table->dropColumn('indikator');
            }

            if (Schema::hasColumn('realisasi_induks', 'target')) {
                $table->dropColumn('target'); // kolom target lama (varchar)
            }

            if (Schema::hasColumn('realisasi_induks', 'hambatan')) {
                $table->dropColumn('hambatan');
            }

            if (Schema::hasColumn('realisasi_induks', 'rekomendasi')) {
                $table->dropColumn('rekomendasi');
            }

            if (Schema::hasColumn('realisasi_induks', 'tindak_lanjut')) {
                $table->dropColumn('tindak_lanjut');
            }

            if (Schema::hasColumn('realisasi_induks', 'dokumen')) {
                $table->dropColumn('dokumen');
            }

            if (Schema::hasColumn('realisasi_induks', 'strategi')) {
                $table->dropColumn('strategi');
            }

            if (Schema::hasColumn('realisasi_induks', 'alasan')) {
                $table->dropColumn('alasan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_induks', function (Blueprint $table) {
            //
        });
    }
};
