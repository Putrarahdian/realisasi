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
        Schema::create('keuangan', function (Blueprint $table) {
            $table->id();

            // tanggal transaksi
            $table->date('tanggal')->required();

            // 1 tabel untuk masuk/keluar
            $table->enum('jenis', ['masuk', 'keluar']);

            // nilai uang
            $table->decimal('jumlah', 16, 2)->default(0);

            // catatan/keterangan
            $table->text('keterangan')->nullable();

            $table->unsignedBigInteger('realisasi_induk_id')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // index biar cepat
            $table->index(['jenis', 'tanggal']);
            $table->index('realisasi_induk_id');
            $table->index('created_by');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan');
    }
};
