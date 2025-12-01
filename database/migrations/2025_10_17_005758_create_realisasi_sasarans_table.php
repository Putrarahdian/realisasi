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
        Schema::create('realisasi_sasarans', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('triwulan'); // I, II, III, IV
        $table->text('uraian')->nullable(); // Sasaran
        $table->string('target')->nullable();
        $table->string('realisasi')->nullable();
        $table->string('capaian')->nullable(); // otomatis dihitung %
        $table->timestamps();
            
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_sasarans');
    }
};
