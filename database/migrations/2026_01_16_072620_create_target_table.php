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
        Schema::create('target', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');                 // contoh: 2026
            $table->string('judul');               // contoh: "SASARAN 2026"

            // opsional (kalau target beda per bidang/seksi)
            $table->foreignId('bidang_id')->nullable()->constrained('bidang')->nullOnDelete();
            $table->foreignId('seksi_id')->nullable()->constrained('seksi')->nullOnDelete();

            $table->timestamps();

            $table->unique(['tahun', 'judul', 'bidang_id', 'seksi_id'], 'target_unik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target');
    }
};
