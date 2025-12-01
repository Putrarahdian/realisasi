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
        Schema::table('realisasi_keberhasilan', function (Blueprint $table) {
            $table->text('keberhasilan_tw1')->nullable()->after('keberhasilan');
            $table->text('keberhasilan_tw2')->nullable();
            $table->text('keberhasilan_tw3')->nullable();
            $table->text('keberhasilan_tw4')->nullable();

            $table->text('hambatan_tw1')->nullable()->after('hambatan');
            $table->text('hambatan_tw2')->nullable();
            $table->text('hambatan_tw3')->nullable();
            $table->text('hambatan_tw4')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_keberhasilan', function (Blueprint $table) {
            $table->dropColumn([
                'keberhasilan_tw1',
                'keberhasilan_tw2',
                'keberhasilan_tw3',
                'keberhasilan_tw4',
                'hambatan_tw1',
                'hambatan_tw2',
                'hambatan_tw3',
                'hambatan_tw4',
            ]);
        });
    }
};
