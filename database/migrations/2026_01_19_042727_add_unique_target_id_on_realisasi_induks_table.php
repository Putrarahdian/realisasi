<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('realisasi_induks', function (Blueprint $table) {
            // pastikan tidak ada duplikat target_id (satu target hanya boleh dipakai 1 induk)
            $table->unique('target_id', 'realisasi_induks_target_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('realisasi_induks', function (Blueprint $table) {
            $table->dropUnique('realisasi_induks_target_id_unique');
        });
    }
};
