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
        Schema::table('realisasis', function (Blueprint $table) {
        
        $table->string('waktu_pelaksanaan')->nullable()->after('tanggal');
        $table->string('target')->nullable()->after('waktu_pelaksanaan');
        $table->string('capaian')->nullable()->after('realisasi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasis', function (Blueprint $table) {
            //
        });
    }
};
