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

    public function __construct(Collection $data, string $judulTriwulan = 'TRIWULAN I')
    {
        $this->data          = $data;
        $this->judulTriwulan = $judulTriwulan;
    }

    public function collection()
    {
        return $this->data;
    }

    // Data tabel mulai dari A5
    public function startCell(): string
    {
        return 'A5';
    }

    // Header tabel (baris 5)
    public function headings(): array
    {
        return [
            'NO.',
            'JUDUL (TARGET)',
            'OUTPUT',
            'OUTCOME',
            'SASARAN',
        ];
    }

    // Mapping data sesuai header
    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            optional($row->targetHeader)->judul ?? '-', // judul utama dari target
            $row->output ?? '-',
            $row->outcome ?? '-',
            $row->sasaran ?? '-',
        ];
    }

    // Lebar kolom
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No.
            'B' => 45,  // Judul target
            'C' => 35,  // Output
            'D' => 35,  // Outcome
            'E' => 35,  // Sasaran
        ];
    }

    // Style tabel
    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->data->count() + 5; // data mulai row 6, header di row 5

        // Header bold + center
        $sheet->getStyle('A5:E5')->getFont()->setBold(true);
        $sheet->getStyle('A5:E5')->getAlignment()
            ->setHorizontal('center')
            ->setVertical('center');

        // Kolom No center
        $sheet->getStyle("A6:A{$lastRow}")->getAlignment()
            ->setHorizontal('center');

        // Wrap text kolom teks
        $sheet->getStyle("B5:E{$lastRow}")
            ->getAlignment()->setWrapText(true);

        // Border area tabel (mulai row4 nomor kolom sampai data terakhir)
        $sheet->getStyle("A4:E{$lastRow}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        return [];
    }

    // Judul sheet + baris nomor kolom (row 4)
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $tahun = optional($this->data->first())->tahun ?? '';

                // merge judul A1:E1, A2:E2, A3:E3
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A3:E3');

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

                // Baris nomor kolom di row 4: 1..5 (A..E)
                $numbers = range(1, 5);
                $col     = 'A';
                foreach ($numbers as $num) {
                    $sheet->setCellValue($col.'4', $num);
                    $col++;
                }

                $sheet->getStyle('A4:E4')->getFont()->setBold(true);
                $sheet->getStyle('A4:E4')->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center');
            },
        ];
    }
}
