<?php

namespace App\Services;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;

class RealisasiWordExportService
{
    public function generate(array $data): string
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);
        $section = $phpWord->addSection();

        // Judul
        foreach ([
            'LAPORAN TRIWULAN I',
            'TAHUN 2025',
            'CAPAIAN KINERJA',
            'KOORDINASI PEMANFAATAN APLIKASI UMUM SPBE'
        ] as $judul) {
            $section->addText($judul, ['bold' => true], ['alignment' => Jc::CENTER]);
        }
        $section->addTextBreak();

        // Poin 1–4
        $section->addText('1. Output            : Jumlah Aplikasi Umum yang telah dimanfaatkan');
        $section->addText('2. Outcome           : Peningkatan penggunaan domain dan sub domain dan Meningkatnya Pelaksanaan');
        $section->addText('                      Pengelolaan E-Government di Lingkup Pemerintah Daerah');
        $section->addText('3. Sasaran           : Nilai Aspek SPBE');
        $section->addText('4. Pelaksanaan Kegiatan :');
        $section->addTextBreak();

        // a. Output (Sub Koordinator / Ess IV)
        $section->addText('a. Output (Sub Koordinator / Ess IV)', ['bold' => true]);
        $tableA = $section->addTable(['borderSize' => 6, 'alignment' => Jc::CENTER]);

        // Header table
        $headers = ['No', 'Waktu Pelaksanaan', 'Uraian', 'Target*', 'Realisasi', 'Capaian'];
        $tableA->addRow();
        foreach ($headers as $head) {
            $tableA->addCell()->addText($head, ['bold' => true], ['alignment' => Jc::CENTER]);
        }

        // Loop data
        $no = 1;
        $totalTarget = 0;
        $totalRealisasi = 0;
        foreach ($data as $item) {
            $tableA->addRow();
            $tableA->addCell()->addText($no++);
            $tableA->addCell()->addText($item['waktu_pelaksanaan'] ?? '-');
            $tableA->addCell()->addText($item['nama_kegiatan'] ?? '-');
            $tableA->addCell()->addText($item['target'] ?? '-', [], ['alignment' => Jc::CENTER]);
            $tableA->addCell()->addText($item['realisasi'] ?? '-', [], ['alignment' => Jc::CENTER]);
            $tableA->addCell()->addText($item['capaian'] ?? '-', [], ['alignment' => Jc::CENTER]);

            $totalTarget += is_numeric($item['target']) ? $item['target'] : 0;
            $totalRealisasi += is_numeric($item['realisasi']) ? $item['realisasi'] : 0;
        }

        // Row total
        $tableA->addRow();
        $tableA->addCell()->addText('');
        $tableA->addCell()->addText('');
        $tableA->addCell()->addText('Jumlah', ['bold' => true]);
        $tableA->addCell()->addText($totalTarget, ['bold' => true], ['alignment' => Jc::CENTER]);
        $tableA->addCell()->addText($totalRealisasi, ['bold' => true], ['alignment' => Jc::CENTER]);
        $persen = $totalTarget ? round(($totalRealisasi / $totalTarget) * 100) . '%' : '-';
        $tableA->addCell()->addText($persen, ['bold' => true], ['alignment' => Jc::CENTER]);

        $section->addTextBreak();

        // b. Outcome
        $section->addText('b. Outcome (Eselon III)', ['bold' => true]);
        $tableB = $section->addTable(['borderSize' => 6, 'alignment' => Jc::CENTER]);
        $tableB->addRow();
        foreach ($headers as $head) {
            $tableB->addCell()->addText($head, ['bold' => true], ['alignment' => Jc::CENTER]);
        }

        // Manual data dummy → user bisa input ini di web juga nanti
        $rowsOutcome = [
            ['Triwulan I', 'Peningkatan penggunaan domain dan sub domain dan Meningkatnya Pelaksanaan Pengelolaan E-Gov', '-', '-', '-'],
            ['Triwulan II', 'Peningkatan penggunaan domain dan sub domain dan Meningkatnya Pelaksanaan Pengelolaan E-Gov', '100%', '50%', '50%'],
            ['Triwulan III', 'Peningkatan penggunaan domain dan sub domain dan Meningkatnya Pelaksanaan Pengelolaan E-Gov', '100%', '100%', '100%'],
            ['Triwulan IV', 'Peningkatan penggunaan domain dan sub domain dan Meningkatnya Pelaksanaan Pengelolaan E-Gov', '100%', '150%', '150%'],
        ];
        foreach ($rowsOutcome as $i => $row) {
            $tableB->addRow();
            $tableB->addCell()->addText($i + 1);
            foreach ($row as $val) {
                $tableB->addCell()->addText($val);
            }
        }
        $tableB->addRow();
        $tableB->addCell()->addText('');
        $tableB->addCell()->addText('');
        $tableB->addCell()->addText('Jumlah', ['bold' => true]);
        $tableB->addCell()->addText('100%', ['bold' => true]);
        $tableB->addCell()->addText('100%', ['bold' => true]);
        $tableB->addCell()->addText('100%', ['bold' => true]);

        $section->addTextBreak();

        // 5. Pelaksanaan Keuangan
        $section->addText('5. Pelaksanaan Keuangan :', ['bold' => true]);
        $table5 = $section->addTable(['borderSize' => 6]);
        $table5->addRow();
        foreach (['No', 'Waktu Pelaksanaan', 'Target', 'Realisasi', 'Capaian'] as $h) {
            $table5->addCell()->addText($h, ['bold' => true], ['alignment' => Jc::CENTER]);
        }

        foreach (['I', 'II', 'III', 'IV'] as $i => $tw) {
            $table5->addRow();
            $table5->addCell()->addText($i + 1);
            $table5->addCell()->addText("Triwulan $tw");
            $table5->addCell()->addText('');
            $table5->addCell()->addText('');
            $table5->addCell()->addText('');
        }
        $table5->addRow();
        $table5->addCell()->addText('');
        $table5->addCell()->addText('Total Pagu');
        $table5->addCell()->addText('');
        $table5->addCell()->addText('');
        $table5->addCell()->addText('');

        $section->addTextBreak();

        // 6. Keberhasilan / Hambatan (otomatis jika nanti ada input, untuk sekarang manual)
        $section->addText('6. Keterangan keberhasilan / hambatan :', ['bold' => true]);
        $section->addText('a. Keberhasilan :');
        $section->addText('-', [], ['indentation' => ['left' => 400]]);
        $section->addText('b. Hambatannya :');
        $section->addText('-', [], ['indentation' => ['left' => 400]]);
        $section->addTextBreak();

        // 7. Manual input
        $section->addText('7. Hasil pelaksanaan kegiatan 2 tahun sebelumnya :', ['bold' => true]);
        $table7 = $section->addTable(['borderSize' => 6]);
        $table7->addRow();
        foreach (['No', 'Uraian', '2023', '2024'] as $h) {
            $table7->addCell()->addText($h, ['bold' => true], ['alignment' => Jc::CENTER]);
        }
        foreach (['Target', 'Realisasi', 'Capaian'] as $i => $row) {
            $table7->addRow();
            $table7->addCell()->addText($i + 1);
            $table7->addCell()->addText($row);
            $table7->addCell()->addText('');
            $table7->addCell()->addText('');
        }

        $section->addTextBreak();
        $section->addText('Banjarbaru, Maret 2025', [], ['alignment' => Jc::RIGHT]);
        $section->addTextBreak(2);

        // Tanda tangan
        $ttd = $section->addTable(['alignment' => Jc::CENTER, 'borderSize' => 0]);
        $ttd->addRow();

        $left = $ttd->addCell(5000);
        $left->addText('Kepala Bidang Informatika', [], ['alignment' => Jc::CENTER]);
        $left->addTextBreak(3);
        $left->addText('KHAIRURRIJAAL, SSTP', ['bold' => true, 'underline' => 'single'], ['alignment' => Jc::CENTER]);
        $left->addText('NIP. 19811010 200012 1 003', [], ['alignment' => Jc::CENTER]);

        $right = $ttd->addCell(5000);
        $right->addText('Kepala Seksi', [], ['alignment' => Jc::CENTER]);
        $right->addText('Pengembangan Sistem Informasi dan Website Pemerintahan', [], ['alignment' => Jc::CENTER]);
        $right->addTextBreak(2);
        $right->addText('WIJAYA KESUMA, S.Kom', ['bold' => true, 'underline' => 'single'], ['alignment' => Jc::CENTER]);
        $right->addText('NIP. 19771017 200903 1 001', [], ['alignment' => Jc::CENTER]);

        $section->addTextBreak(3);
        $section->addText('Mengetahui,', [], ['alignment' => Jc::CENTER]);
        $section->addText('Kepala Dinas Komunikasi dan Informatika', [], ['alignment' => Jc::CENTER]);
        $section->addText('Kota Banjarbaru', [], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(2);
        $section->addText('ASEP SAPUTRA, S.Kom, MM', ['bold' => true, 'underline' => 'single'], ['alignment' => Jc::CENTER]);
        $section->addText('NIP. 19770909 200604 1 006', [], ['alignment' => Jc::CENTER]);

        // Simpan file Word
        $filename = 'Laporan-SPBE-' . now()->format('YmdHis') . '.docx';
        $path = storage_path("app/public/{$filename}");
        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }
}
