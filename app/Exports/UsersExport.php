<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

// ✅ tambahan untuk styling
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Events\AfterSheet;

class UsersExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithColumnFormatting,
    WithEvents
{
    protected ?string $search;
    protected $authUser;

    public function __construct($authUser, ?string $search = null)
    {
        $this->authUser = $authUser;
        $this->search = $search;
    }

    public function collection(): Collection
    {
        $query = User::query()
            ->with(['jabatan', 'bidang', 'seksi']);

        if ($this->authUser->role === 'admin') {
            $query->where('bidang_id', $this->authUser->bidang_id);
        }

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nip', 'like', "%{$search}%")
                    ->orWhereHas('jabatan', fn($j) => $j->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('bidang', fn($b) => $b->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('seksi', fn($s) => $s->where('nama', 'like', "%{$search}%"));
            });
        }

        $query->orderByRaw("FIELD(role, 'superuser','admin','user')")
              ->orderBy('name');

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Email',
            'Role',
            'NIP',
            'Jabatan',
            'Bidang',
            'Seksi',
        ];
    }

    public function map($u): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $u->name,
            $u->email,
            $u->role,
            $u->nip ?? '',
            optional($u->jabatan)->nama ?? '',
            optional($u->bidang)->nama ?? '',
            optional($u->seksi)->nama ?? '',
        ];
    }

    /**
     * Format kolom (contoh: NIP dipaksa TEXT agar tidak jadi scientific)
     */
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_TEXT, // NIP
        ];
    }

    /**
     * Styling dasar (header)
     */
    public function styles(Worksheet $sheet)
    {
        // Freeze baris header
        $sheet->freezePane('A2');

        // Tinggi baris header
        $sheet->getRowDimension(1)->setRowHeight(22);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1D4ED8'], // biru
                ],
            ],
        ];
    }

    /**
     * Styling lanjutan setelah sheet jadi (border, autofilter, wrap, align)
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Tentukan range terakhir
                $lastRow = $sheet->getHighestRow();
                $lastCol = $sheet->getHighestColumn();
                $range   = "A1:{$lastCol}{$lastRow}";

                // AutoFilter
                $sheet->setAutoFilter("A1:{$lastCol}1");

                // Wrap text untuk seluruh range
                $sheet->getStyle($range)->getAlignment()->setWrapText(true);

                // Align default: vertical center
                $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                // Kolom No dan Role center
                $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D2:D{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Border untuk semua cell
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);

                // Alternating row background (zebra) mulai baris 2
                for ($r = 2; $r <= $lastRow; $r++) {
                    if ($r % 2 === 0) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F9FAFB'],
                            ],
                        ]);
                    }
                }

                // Lebar minimum biar rapi (walau sudah ShouldAutoSize)
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(26);
                $sheet->getColumnDimension('C')->setWidth(30);
                $sheet->getColumnDimension('D')->setWidth(12);
                $sheet->getColumnDimension('E')->setWidth(18);
                $sheet->getColumnDimension('F')->setWidth(22);
                $sheet->getColumnDimension('G')->setWidth(18);
                $sheet->getColumnDimension('H')->setWidth(18);

                // Print setup (opsional tapi bikin enak kalau diprint)
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }
}
