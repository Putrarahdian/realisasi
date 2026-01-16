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
        Schema::create('target_rincian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('target_id')->constrained('target')->cascadeOnDelete();

            $table->enum('jenis', ['output','outcome','sasaran','keuangan']);
            $table->text('uraian');
            $table->string('target'); // kalau yakin angka, bisa decimal

            $table->timestamps();

            $table->unique(['target_id','jenis'], 'unik_target_jenis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_rincian');
    }
};
