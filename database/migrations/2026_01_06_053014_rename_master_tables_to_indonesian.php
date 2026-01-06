<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    private function dropFkIfExists(string $table, string $column): void
    {
        $dbName = DB::getDatabaseName();

        $row = DB::selectOne("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ", [$dbName, $table, $column]);

        if ($row && isset($row->CONSTRAINT_NAME)) {
            DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `{$row->CONSTRAINT_NAME}`");
        }
    }

    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1) Drop FK yang mengarah ke users/bidangs/seksis/jabatans (nama constraint bisa custom)
        $this->dropFkIfExists('informasi_umum', 'user_id');

        $this->dropFkIfExists('realisasi_keberhasilan', 'user_id');
        $this->dropFkIfExists('realisasi_keuangans', 'user_id');
        $this->dropFkIfExists('realisasi_outcomes', 'user_id');
        $this->dropFkIfExists('realisasi_outputs', 'user_id');
        $this->dropFkIfExists('realisasi_sasarans', 'user_id');

        $this->dropFkIfExists('realisasi_induks', 'seksi_id');
        $this->dropFkIfExists('realisasi_induks', 'bidang_id');

        $this->dropFkIfExists('seksis', 'bidang_id');

        $this->dropFkIfExists('users', 'jabatan_id');
        $this->dropFkIfExists('users', 'bidang_id');
        $this->dropFkIfExists('users', 'seksi_id');

        // 2) Rename tabel
        Schema::rename('users', 'pengguna');
        Schema::rename('bidangs', 'bidang');
        Schema::rename('seksis', 'seksi');
        Schema::rename('jabatans', 'jabatan');

        // 3) Add FK kembali, sekarang refer ke tabel baru
        Schema::table('informasi_umum', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('pengguna')->onDelete('cascade');
        });

        Schema::table('realisasi_keberhasilan', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('pengguna')->onDelete('cascade');
        });

        Schema::table('realisasi_keuangans', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('pengguna')->onDelete('cascade');
        });

        Schema::table('realisasi_outcomes', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('pengguna')->onDelete('cascade');
        });

        Schema::table('realisasi_outputs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('pengguna')->onDelete('cascade');
        });

        Schema::table('realisasi_sasarans', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('pengguna')->onDelete('cascade');
        });

        Schema::table('realisasi_induks', function (Blueprint $table) {
            $table->foreign('bidang_id')->references('id')->on('bidang')
                ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('seksi_id')->references('id')->on('seksi')
                ->onDelete('set null');
        });

        Schema::table('seksi', function (Blueprint $table) {
            $table->foreign('bidang_id')->references('id')->on('bidang')
                ->onDelete('set null');
        });

        Schema::table('pengguna', function (Blueprint $table) {
            $table->foreign('jabatan_id')->references('id')->on('jabatan')->onDelete('set null');
            $table->foreign('bidang_id')->references('id')->on('bidang')->onDelete('set null');
            $table->foreign('seksi_id')->references('id')->on('seksi')->onDelete('set null');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // Drop FK versi tabel baru (pakai helper yang sama)
        $this->dropFkIfExists('informasi_umum', 'user_id');

        $this->dropFkIfExists('realisasi_keberhasilan', 'user_id');
        $this->dropFkIfExists('realisasi_keuangans', 'user_id');
        $this->dropFkIfExists('realisasi_outcomes', 'user_id');
        $this->dropFkIfExists('realisasi_outputs', 'user_id');
        $this->dropFkIfExists('realisasi_sasarans', 'user_id');

        $this->dropFkIfExists('realisasi_induks', 'bidang_id');
        $this->dropFkIfExists('realisasi_induks', 'seksi_id');

        $this->dropFkIfExists('seksi', 'bidang_id');

        $this->dropFkIfExists('pengguna', 'jabatan_id');
        $this->dropFkIfExists('pengguna', 'bidang_id');
        $this->dropFkIfExists('pengguna', 'seksi_id');

        // Rename balik
        Schema::rename('pengguna', 'users');
        Schema::rename('bidang', 'bidangs');
        Schema::rename('seksi', 'seksis');
        Schema::rename('jabatan', 'jabatans');

        // Pasang FK balik supaya rollback benar-benar bersih (simetris)
        Schema::table('informasi_umum', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('realisasi_keberhasilan', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('realisasi_keuangans', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('realisasi_outcomes', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('realisasi_outputs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('realisasi_sasarans', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('realisasi_induks', function (Blueprint $table) {
            $table->foreign('bidang_id')->references('id')->on('bidangs')
                ->onDelete('set null')->onUpdate('cascade');

            $table->foreign('seksi_id')->references('id')->on('seksis')
                ->onDelete('set null');
        });

        Schema::table('seksis', function (Blueprint $table) {
            $table->foreign('bidang_id')->references('id')->on('bidangs')
                ->onDelete('set null');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('jabatan_id')->references('id')->on('jabatans')->onDelete('set null');
            $table->foreign('bidang_id')->references('id')->on('bidangs')->onDelete('set null');
            $table->foreign('seksi_id')->references('id')->on('seksis')->onDelete('set null');
        });

        Schema::enableForeignKeyConstraints();
    }
};
