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
    Schema::create('informasi_umum', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->enum('triwulan', ['I', 'II', 'III', 'IV']);
        $table->string('tahun', 4);
        $table->string('instansi');
        $table->string('penanggung_jawab');
        $table->timestamps();

        // Foreign key (jika kamu ingin validasi user id)
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informasi_umum');
    }
};
