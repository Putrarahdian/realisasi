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
        Schema::table('realisasi_induks', function (Blueprint $table) {

            // 1) Tambah kolom user_id (bigint unsigned) - aman
            if (!Schema::hasColumn('realisasi_induks', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('seksi_id');
            }

            // 2) Pakai nama constraint yang eksplisit (biar dropForeign pasti bisa)
            $table->foreign('user_id', 'realisasi_induks_user_id_fk')
                ->references('id')
                ->on('pengguna')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_induks', function (Blueprint $table) {

            // drop FK dengan nama yang sama seperti up()
            $table->dropForeign('realisasi_induks_user_id_fk');

            // drop kolom
            if (Schema::hasColumn('realisasi_induks', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
