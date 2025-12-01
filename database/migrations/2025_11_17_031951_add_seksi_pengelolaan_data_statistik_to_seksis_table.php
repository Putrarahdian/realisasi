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
        DB::table('seksis')->insert([
            'nama'       => 'Seksi Pengelolaan Data dan Informasi Statistik',
            'bidang_id'  => 3,  
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seksis', function (Blueprint $table) {
            DB::table('seksis')
            ->where('nama', 'Seksi Pengelolaan Data dan Informasi Statistik')
            ->where('bidang_id', 3)
            ->delete();
        });
    }
};
