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
            $table->unsignedBigInteger('seksi_id')
                  ->nullable()
                  ->after('bidang_id');

            $table->foreign('seksi_id')
                  ->references('id')
                  ->on('seksis')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_induks', function (Blueprint $table) {
            $table->dropForeign(['seksi_id']);
            $table->dropColumn('seksi_id');
        });
    }
};
