<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $data = [
            ['nama' => 'Kepala Dinas', 'jenis' => 'jabatan', 'is_locked' => true],
            ['nama' => 'Sekretaris', 'jenis' => 'jabatan'],
            ['nama' => 'Kasubag', 'jenis' => 'jabatan'],

            ['nama' => 'Bidang Komunikasi', 'jenis' => 'bidang', 'is_locked' => true],
            ['nama' => 'Bidang Informatika', 'jenis' => 'bidang', 'is_locked' => true],
            ['nama' => 'Bidang Statistik & Persandian', 'jenis' => 'bidang', 'is_locked' => true],

            ['nama' => 'Seksi Infrastruktur TIK', 'jenis' => 'seksi'],
            ['nama' => 'Seksi Layanan Informasi Publik', 'jenis' => 'seksi'],
            ['nama' => 'Seksi Statistik Sektoral', 'jenis' => 'seksi'],
        ];

        foreach ($data as $jabatan) {
            Jabatan::create($jabatan);
        }
    }
}
