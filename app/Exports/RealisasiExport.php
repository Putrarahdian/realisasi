<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class RealisasiExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
{
    protected Collection $data;
    protected string $judulTriwulan;

    // 🔹 Bisa kirim teks judul triwulan dari controller (opsional)
    public function __construct(Collection $data, string $judulTriwulan = 'TRIWULAN I')
    {
        $this->data          = $data;
        $this->judulTriwulan = $judulTriwulan;
    }

    public function collection()
    {
        return $this->data;
    }

    /**
     * 🔹 Data tabel mulai dari sel A5
     */
    public function startCell(): string
    {
        return 'A5';
    }

    /**
     * 🔹 Header tabel (baris ke-5)
     */
    public function headings(): array
    {
        return [
            'NO.',
            'SASARAN STRATEGIS',
            'PROGRAM/KEGIATAN/SUB KEGIATAN',
            'INDIKATOR PROG/KEG/SUB KEG',
            'Target',
            'HAMBATAN/KEBERHASILAN',
            'REKOMENDASI',
            'TL REKOMENDASI SEBELUMNYA',
            'NAMA DOKUMEN/DATA KINERJA',
            'STRATEGI YANG AKAN DILAKUKAN TRIWULAN BERIKUTNYA',
            'ALASAN TIDAK TERCAPAI',
        ];
    }

    /**
     * 🔹 Urutan data mengikuti headings() di atas
     */
    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->sasaran_strategis,
            $row->program,
            $row->indikator,
            $row->target,
            $row->hambatan,
            $row->rekomendasi,
            $row->tindak_lanjut,
            $row->dokumen,
            $row->strategi,
            $row->alasan,
        ];
    }

    /**
     * 🔹 Atur lebar kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No.
            'B' => 30,  // Sasaran
            'C' => 28,  // Program
            'D' => 25,  // Indikator
            'E' => 12,  // Target
            'F' => 25,  // Hambatan/Keberhasilan
            'G' => 25,  // Rekomendasi
            'H' => 25,  // TL Rekomendasi
            'I' => 25,  // Dokumen
            'J' => 28,  // Strategi
            'K' => 25,  // Alasan
        ];
    }

    /**
     * 🔹 Style tabel (border, align, wrap)
     */
    public function styles(Worksheet $sheet)
    {
        // baris data terakhir (data mulai baris 6)
        $lastRow = $this->data->count() + 5;

        // Header tabel (baris 5) bold + center
        $sheet->getStyle('A5:K5')->getFont()->setBold(true);
        $sheet->getStyle('A5:K5')->getAlignment()
            ->setHorizontal('center')
            ->setVertical('center');

        // Kolom No rata tengah
        $sheet->getStyle("A6:A{$lastRow}")->getAlignment()
            ->setHorizontal('center');

        // Wrap text untuk kolom teks panjang
        $sheet->getStyle("B5:K{$lastRow}")
            ->getAlignment()->setWrapText(true);

        // Border semua area mulai row4 (nomor 1-11) sampai data terakhir
        $sheet->getStyle("A4:K{$lastRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        return [];
    }

    /**
     * 🔹 Set judul di baris 1-3 + baris nomor 1-11 di baris 4
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $tahun = optional($this->data->first())->tahun ?? '';

                // Judul baris 1–3 (merge A1:K1, A2:K2, A3:K3)
                $sheet->mergeCells('A1:K1');
                $sheet->mergeCells('A2:K2');
                $sheet->mergeCells('A3:K3');

                $sheet->setCellValue('A1', "KERTAS KERJA MONITORING KINERJA {$this->judulTriwulan}");
                $sheet->setCellValue('A2', 'DINAS KOMUNIKASI DAN INFORMATIKA KOTA BANJARBARU');
                $sheet->setCellValue('A3', 'TAHUN ' . $tahun);

                $sheet->getStyle('A1:A3')->getFont()->setBold(true);
                $sheet->getStyle('A1:A3')->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');

                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(18);

                // Baris nomor 1–11 di row 4
                $numbers = range(1, 11); // 1..11
                $col     = 'A';
                foreach ($numbers as $num) {
                    $sheet->setCellValue($col.'4', $num);
                    $col++;
                }

                $sheet->getStyle('A4:K4')->getFont()->setBold(true);
                $sheet->getStyle('A4:K4')->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');
            },
        ];
    }
}
