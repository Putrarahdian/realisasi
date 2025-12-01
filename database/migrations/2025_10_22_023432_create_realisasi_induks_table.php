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
        Schema::create('realisasi_induks', function (Blueprint $table) {
            $table->id();
            $table->integer('nomor')->nullable();
            $table->string('sasaran_strategis');
            $table->string('program');
            $table->string('indikator');
            $table->string('target');
            $table->text('hambatan')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('dokumen')->nullable();
            $table->text('strategi')->nullable();
            $table->text('alasan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_induks');
    }
};
