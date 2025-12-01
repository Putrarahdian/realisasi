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
        
Schema::create('realisasi_sebelumnya', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('user_id');
    $table->year('tahun_1');
    $table->year('tahun_2');
    $table->double('target_t1')->nullable();
    $table->double('realisasi_t1')->nullable();
    $table->double('capaian_t1')->nullable();
    $table->double('target_t2')->nullable();
    $table->double('realisasi_t2')->nullable();
    $table->double('capaian_t2')->nullable();
    $table->timestamps();

    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_sebelumnya');
    }
};
