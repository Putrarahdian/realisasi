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
        Schema::table('realisasi_sasarans', function (Blueprint $table) {
            $table->decimal('target_tw1', 18, 2)->nullable()->after('uraian');
            $table->decimal('target_tw2', 18, 2)->nullable()->after('target_tw1');
            $table->decimal('target_tw3', 18, 2)->nullable()->after('target_tw2');
            $table->decimal('target_tw4', 18, 2)->nullable()->after('target_tw3');

            $table->decimal('realisasi_tw1', 18, 2)->nullable()->after('target_tw4');
            $table->decimal('realisasi_tw2', 18, 2)->nullable()->after('realisasi_tw1');
            $table->decimal('realisasi_tw3', 18, 2)->nullable()->after('realisasi_tw2');
            $table->decimal('realisasi_tw4', 18, 2)->nullable()->after('realisasi_tw3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_sasarans', function (Blueprint $table) {
            $table->dropColumn([
                'target_tw1','target_tw2','target_tw3','target_tw4',
                'realisasi_tw1','realisasi_tw2','realisasi_tw3','realisasi_tw4',
            ]);
        });
    }
};
