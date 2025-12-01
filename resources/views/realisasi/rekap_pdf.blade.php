<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Triwulan {{ $induk->tahun }}</title>
    <style>
        * {
            font-family: "Times New Roman", DejaVu Serif, serif;
            font-size: 11pt;
            line-height: 1.15;
        }

        body {
            margin: 2cm 2cm 2cm 2cm;
        }

        p {
            margin: 2px 0;
        }

        .text-center { text-align: center; }
        .text-right  { text-align: right; }

        .judul {
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            vertical-align: top;
            padding: 2px 4px;
        }

        .tbl-border th,
        .tbl-border td {
            border: 1px solid #000;
        }

        .info-utama td {
            border: none;
            padding: 1px 0;
        }

        .info-no    { width: 1cm; }
        .info-label { width: 3.5cm; }
        .info-titik { width: 0.5cm; }

        .mt-5  { margin-top: 5px; }
        .mt-10 { margin-top: 10px; }
        .mt-15 { margin-top: 15px; }
        .mt-20 { margin-top: 20px; }

        .underline { text-decoration: underline; }

        .no-border th,
        .no-border td {
            border: none !important;
            padding: 0;
        }

        .dispo-table td {
            border: 1px solid #000;
            height: 80px;
            padding: 4px;
            vertical-align: top;
        }
    </style>
</head>
<body>

@php
    // cari uraian output pertama (untuk poin 1)
    $firstOutput = null;
    foreach ($triwulans as $tw) {
        if (isset($outputs[$tw]) && $outputs[$tw]->first()) {
            $firstOutput = $outputs[$tw]->first();
            break;
        }
    }

    // cari uraian outcome pertama (untuk poin 2)
    $firstOutcome = null;
    foreach ($triwulans as $tw) {
        if (isset($outcomes[$tw]) && $outcomes[$tw]->first()) {
            $firstOutcome = $outcomes[$tw]->first();
            break;
        }
    }

    // Tanggal sekarang (untuk tanda tangan)
    $bulanMap = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $now            = now();
    $tanggalLengkap = $now->day.' '.$bulanMap[$now->month].' '.$now->year;
@endphp

{{-- ===================== JUDUL ===================== --}}
<p class="judul">LAPORAN TRIWULAN</p>
<p class="judul">TAHUN {{ $induk->tahun }}</p>
<p class="judul">CAPAIAN KINERJA</p>
<br>
<p class="judul">{{ strtoupper($induk->program) }}</p>
<br>

{{-- ===================== 1–4 OUTPUT / OUTCOME / SASARAN ===================== --}}
<table class="info-utama">
    <tr>
        <td class="info-no">1.</td>
        <td class="info-label">Output</td>
        <td class="info-titik">:</td>
        <td>{{ $firstOutput->uraian ?? '' }}</td>
    </tr>
    <tr>
        <td class="info-no">2.</td>
        <td class="info-label">Outcome</td>
        <td class="info-titik">:</td>
        <td>{{ $firstOutcome->uraian ?? '' }}</td>
    </tr>
    <tr>
        <td class="info-no">3.</td>
        <td class="info-label">Sasaran</td>
        <td class="info-titik">:</td>
        <td>{{ $sasaran->uraian ?? '' }}</td>
    </tr>
    <tr>
        <td class="info-no">4.</td>
        <td class="info-label">Pelaksanaan Kegiatan</td>
        <td class="info-titik">:</td>
        <td></td>
    </tr>
</table>

{{-- ===================== a. OUTPUT ===================== --}}
<p class="mt-10"><b>a. Output (Sub Koordinator / Ess IV)</b></p>

<table class="tbl-border">
    <tr>
        <th>No</th>
        <th>Waktu Pelaksanaan</th>
        <th>Uraian</th>
        <th>Target*</th>
        <th>Realisasi</th>
        <th>Capaian</th>
    </tr>
    @php
        $no              = 1;
        $targetO         = null;
        $totalRealisasiO = 0;
        $totalCapaianO   = 0;
    @endphp
    @foreach($triwulans as $tw)
        @php
            $row = optional($outputs[$tw] ?? collect())->first();

            $cap = null;
            if ($row) {
                if ($targetO === null && $row->target > 0) {
                    $targetO = $row->target;
                }

                $totalRealisasiO += $row->realisasi ?? 0;

                if ($row->capaian !== null && $row->capaian !== '') {
                    $cap = (float) $row->capaian;
                } elseif ($row->target > 0 && $row->realisasi !== null) {
                    $cap = round(($row->realisasi / $row->target) * 100, 2);
                }

                if ($cap !== null) {
                    $totalCapaianO += $cap;
                }
            }
        @endphp
        <tr>
            <td class="text-center">{{ $no++ }}</td>
            <td>Triwulan {{ $tw }}</td>
            <td>{{ $row->uraian ?? '-' }}</td>
            <td class="text-center">{{ $row->target ?? '-' }}</td>
            <td class="text-center">{{ $row->realisasi ?? '-' }}</td>
            <td class="text-center">
                {{ $cap !== null ? number_format($cap, 0) . '%' : '-' }}
            </td>
        </tr>
    @endforeach
    <tr>
        <td></td>
        <td>Jumlah</td>
        <td></td>
        <td class="text-center">{{ $targetO !== null ? $targetO : '-' }}</td>
        <td class="text-center">{{ $totalRealisasiO ?: '-' }}</td>
        <td class="text-center">
            {{ $totalCapaianO > 0 ? number_format($totalCapaianO, 0) . '%' : '-' }}
        </td>
    </tr>
</table>

{{-- ===================== b. OUTCOME ===================== --}}
<p class="mt-10"><b>b. Outcome (Eselon III)</b></p>

<table class="tbl-border">
    <tr>
        <th>No</th>
        <th>Waktu Pelaksanaan</th>
        <th>Uraian</th>
        <th>Target*</th>
        <th>Realisasi</th>
        <th>Capaian</th>
    </tr>
    @php
        $no               = 1;
        $targetOc         = null;
        $totalRealisasiOc = 0;
        $totalCapaianOc   = 0;
    @endphp
    @foreach($triwulans as $tw)
        @php
            $row = optional($outcomes[$tw] ?? collect())->first();

            $cap = null;
            if ($row) {
                if ($targetOc === null && $row->target > 0) {
                    $targetOc = $row->target;
                }

                $totalRealisasiOc += $row->realisasi ?? 0;

                if ($row->capaian !== null && $row->capaian !== '') {
                    $cap = (float) $row->capaian;
                } elseif ($row->target > 0 && $row->realisasi !== null) {
                    $cap = round(($row->realisasi / $row->target) * 100, 2);
                }

                if ($cap !== null) {
                    $totalCapaianOc += $cap;
                }
            }
        @endphp
        <tr>
            <td class="text-center">{{ $no++ }}</td>
            <td>Triwulan {{ $tw }}</td>
            <td>{{ $row->uraian ?? '-' }}</td>
            <td class="text-center">{{ $row->target ?? '-' }}</td>
            <td class="text-center">{{ $row->realisasi ?? '-' }}</td>
            <td class="text-center">
                {{ $cap !== null ? number_format($cap, 0) . '%' : '-' }}
            </td>
        </tr>
    @endforeach
    <tr>
        <td></td>
        <td>Jumlah</td>
        <td></td>
        <td class="text-center">{{ $targetOc !== null ? $targetOc : '-' }}</td>
        <td class="text-center">{{ $totalRealisasiOc ?: '-' }}</td>
        <td class="text-center">
            {{ $totalCapaianOc > 0 ? number_format($totalCapaianOc, 0) . '%' : '-' }}
        </td>
    </tr>
</table>

{{-- ===================== c. SASARAN ===================== --}}
<p class="mt-10"><b>c. Sasaran (Eselon II)</b></p>

<table class="tbl-border">
    <tr>
        <th>No</th>
        <th>Uraian / Indikator</th>
        <th>Target*</th>
        <th>Realisasi</th>
        <th>Capaian</th>
    </tr>
    <tr>
        <td class="text-center">1</td>
        <td>{{ $sasaran->uraian ?? '' }}</td>
        <td class="text-center">{{ $sasaran->target ?? '' }}</td>
        <td class="text-center">{{ $sasaran->realisasi ?? '' }}</td>
        <td class="text-center">
            {{ $sasaran && $sasaran->capaian !== null && $sasaran->capaian !== '' ? $sasaran->capaian.'%' : '-' }}
        </td>
    </tr>
</table>

{{-- ===================== 5. PELAKSANAAN KEUANGAN ===================== --}}
<p class="mt-10"><b>5. Pelaksanaan Keuangan :</b></p>

<table class="tbl-border">
    <tr>
        <th>No</th>
        <th>Waktu Pelaksanaan</th>
        <th>Target</th>
        <th>Realisasi</th>
        <th>Capaian</th>
    </tr>
    @php
        $no            = 1;
        $totalTargetK  = 0;
        $totalRealK    = 0;
        $totalCapK     = 0;
        $countK        = 0;
    @endphp
    @foreach($triwulans as $tw)
        @php
            $row = optional($keuangans[$tw] ?? collect())->first();

            $cap = null;
            if ($row) {
                $totalTargetK += $row->target ?? 0;
                $totalRealK   += $row->realisasi ?? 0;

                if ($row->capaian !== null && $row->capaian !== '') {
                    $cap = (float) $row->capaian;  
                    $totalCapK += $cap;
                    $countK++;
                }
            }
        @endphp
        <tr>
            <td class="text-center">{{ $no++ }}</td>
            <td>Triwulan {{ $tw }}</td>
            <td class="text-center">{{ $row->target ?? '-' }}</td>
            <td class="text-center">{{ $row->realisasi ?? '-' }}</td>
            <td class="text-center">
                {{ $cap !== null ? number_format($cap, 0) . '%' : '-' }}
            </td>
        </tr>
    @endforeach
    <tr>
        <td></td>
        <td>Jumlah</td>
        <td class="text-center">{{ $totalTargetK ?: '-' }}</td>
        <td class="text-center">{{ $totalRealK ?: '-' }}</td>
        <td class="text-center">
            {{ $countK > 0 ? number_format($totalCapK, 0) . '%' : '-' }}
        </td>
    </tr>
</table>

    {{-- ===================== 6. KETERANGAN ===================== --}}
    <p class="mt-10"><b>6. Keterangan Keberhasilan / Hambatan :</b></p>

    @php
        $gabungKeb = [];
        $gabungHam = [];
        if ($keberhasilan) {
            foreach ([1,2,3,4] as $twNo) {
                $kField = 'keberhasilan_tw'.$twNo;
                $hField = 'hambatan_tw'.$twNo;

                if (!empty($keberhasilan->$kField)) {
                    $gabungKeb[] = 'TW '.$twNo.' : '.$keberhasilan->$kField;
                }
                if (!empty($keberhasilan->$hField)) {
                    $gabungHam[] = 'TW '.$twNo.' : '.$keberhasilan->$hField;
                }
            }
        }
    @endphp

    <p>a. Keberhasilan :</p>
    @if($gabungKeb)
        <ul>
            @foreach($gabungKeb as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @else
        <p>-</p>
    @endif

    <p class="mt-5">b. Hambatan :</p>
    @if($gabungHam)
        <ul>
            @foreach($gabungHam as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @else
        <p>-</p>
    @endif

    {{-- ===================== 7. HASIL 2 TAHUN SEBELUMNYA ===================== --}}
    @php
        $tahunSekarang = (int) $induk->tahun;
        $thA = $tahunSekarang - 2;
        $thB = $tahunSekarang - 1;

        // data dari helper getRiwayat2Tahun()
        $rowA = $riwayat2Tahun[$thA] ?? ['target' => 0, 'realisasi' => 0, 'capaian' => 0];
        $rowB = $riwayat2Tahun[$thB] ?? ['target' => 0, 'realisasi' => 0, 'capaian' => 0];
    @endphp

    <p class="mt-10"><b>7. Hasil Pelaksanaan Kegiatan 2 Tahun Sebelumnya :</b></p>

    <table class="tbl-border">
        <tr>
            <th>No</th>
            <th>Uraian</th>
            <th>{{ $thA }}</th>
            <th>{{ $thB }}</th>
        </tr>
        <tr>
            <td class="text-center">1</td>
            <td>Target</td>
            <td class="text-center">{{ $rowA['target'] ?: '-' }}</td>
            <td class="text-center">{{ $rowB['target'] ?: '-' }}</td>
        </tr>
        <tr>
            <td class="text-center">2</td>
            <td>Realisasi</td>
            <td class="text-center">{{ $rowA['realisasi'] ?: '-' }}</td>
            <td class="text-center">{{ $rowB['realisasi'] ?: '-' }}</td>
        </tr>
        <tr>
            <td class="text-center">3</td>
            <td>Capaian</td>
            <td class="text-center">
                {{ $rowA['capaian'] > 0 ? number_format($rowA['capaian'], 0) . '%' : '-' }}
            </td>
            <td class="text-center">
                {{ $rowB['capaian'] > 0 ? number_format($rowB['capaian'], 0) . '%' : '-' }}
            </td>
        </tr>
    </table>

    {{-- ===================== TANDA TANGAN ===================== --}}
    <p class="text-right mt-20">Banjarbaru, {{ $tanggalLengkap }}</p>

    <table class="no-border mt-10">
        <tr>
            <td class="text-center" style="width:50%;">
                Kepala Bidang {{ $induk->bidang->nama ?? '' }}<br><br><br><br>
                @if($kepalaBidang)
                    <span class="underline">{{ $kepalaBidang->name }}</span><br>
                    NIP. {{ $kepalaBidang->nip ?? '' }}
                @else
                    <span class="underline"></span><br>
                    NIP.
                @endif
            </td>
            <td class="text-center" style="width:50%;">
                @if($kepalaSeksi)
                    Kepala Seksi {{ $kepalaSeksi->seksi->nama ?? '' }}<br><br><br><br>
                    <span class="underline">{{ $kepalaSeksi->name }}</span><br>
                    NIP. {{ $kepalaSeksi->nip ?? '' }}
                @else
                    Kepala Seksi<br><br><br><br>
                    <span class="underline"></span><br>
                    NIP.
                @endif
            </td>
        </tr>
    </table>

    <table class="no-border mt-10">
        <tr>
            <td class="text-center">
                Mengetahui,<br>
                Kepala Dinas Komunikasi dan Informatika<br>
                Kota Banjarbaru<br><br><br>
                @if($kepalaDinas)
                    <span class="underline">{{ $kepalaDinas->name }}</span><br>
                    NIP. {{ $kepalaDinas->nip ?? '' }}
                @else
                    <span class="underline"></span><br>
                    NIP.
                @endif
            </td>
        </tr>
    </table>

    {{-- ===================== DISPOSISI (KOTAK) ===================== --}}
    <table class="dispo-table mt-10">
        <tr>
            <td>
                <span class="underline">Disposisi Kepala Bidang:</span><br>
                {!! nl2br(e($induk->disposisi_kabid ?? '')) !!}
            </td>
        </tr>
        <tr>
            <td>
                <span class="underline">Disposisi Kepala Dinas:</span><br>
                {!! nl2br(e($induk->disposisi_kadis ?? '')) !!}
            </td>
        </tr>
    </table>

</body>
</html>
