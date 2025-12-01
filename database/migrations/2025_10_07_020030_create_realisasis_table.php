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
    Schema::create('realisasis', function (Blueprint $table) {
        $table->id();
        $table->string('nama_kegiatan');
        $table->decimal('anggaran', 15, 2);
        $table->decimal('realisasi', 15, 2);
        $table->date('tanggal');
        $table->text('keterangan')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasis');
    }
};
