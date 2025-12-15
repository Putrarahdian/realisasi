<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE `jabatans`
            MODIFY COLUMN `jenis_jabatan`
            ENUM('kepala_dinas','sekretaris','kepala_bidang','kepala_seksi','kepala_bagian','staff','kasubag_keuangan')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci
            DEFAULT NULL
        ");

        // Insert jabatan baru
        DB::table('jabatans')->insert([
            'nama' => 'Kasubag Keuangan',
            'jenis_jabatan' => 'kasubag_keuangan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('jabatans')
            ->where('jenis_jabatan', 'kasubag_keuangan')
            ->delete();

        DB::statement("
            ALTER TABLE `jabatans`
            MODIFY COLUMN `jenis_jabatan`
            ENUM('kepala_dinas','sekretaris','kepala_bidang','kepala_seksi','kepala_bagian','staff')
            CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci
            DEFAULT NULL
        ");
    }
};
