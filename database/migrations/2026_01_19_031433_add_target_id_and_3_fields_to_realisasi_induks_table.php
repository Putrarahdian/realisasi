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
            $table->foreignId('target_id')
                ->nullable()
                ->after('seksi_id')
                ->constrained('target')
                ->nullOnDelete();

            $table->text('output')->nullable()->after('target_id');
            $table->text('outcome')->nullable()->after('output');
            $table->text('sasaran')->nullable()->after('outcome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_induks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('target_id');
            $table->dropColumn(['output', 'outcome', 'sasaran']);
        });
    }
};
