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
            $table->dropForeign(['target_id']);

            $table->foreign('target_id')
                ->references('id')
                ->on('target')
                ->onDelete('cascade'); // 🔥 INI KUNCINYA
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_induks', function (Blueprint $table) {
            $table->dropForeign(['target_id']);

            $table->foreign('target_id')
                ->references('id')
                ->on('target')
                ->onDelete('set null');
        });
    }
};
