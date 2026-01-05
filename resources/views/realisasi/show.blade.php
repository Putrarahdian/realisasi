@extends('layout.weblab')

@section('content')

@php
    $urutanTriwulan = ['I', 'II', 'III', 'IV'];

    // groupBy triwulan
    $outputs   = $induk->outputs?->groupBy('triwulan')   ?? collect();
    $outcomes  = $induk->outcomes?->groupBy('triwulan')  ?? collect();
    $keuangans = $induk->keuangans?->groupBy('triwulan') ?? collect();

    // ✅ sasaran bisa hasOne / bisa collection (biar aman)
    $sasaran = $induk->sasaran instanceof \Illuminate\Support\Collection
        ? $induk->sasaran->first()
        : $induk->sasaran;

    $keberhasilan = $induk->keberhasilan ?? null;

    /**
     * ✅ LOGIKA FIX: hitung total sasaran & capaian total DI VIEW
     * - Prioritas: pakai kolom total (target, realisasi) kalau sudah terisi
     * - Kalau belum terisi / masih 0, fallback ke penjumlahan target_tw1..4 & realisasi_tw1..4
     */
    $sasaranTargetTotal = 0;
    $sasaranRealisasiTotal = 0;

    if ($sasaran) {
        $sasaranTargetTotal   = (float) ($sasaran->target ?? 0);
        $sasaranRealisasiTotal = (float) ($sasaran->realisasi ?? 0);

        $sumT = (float)($sasaran->target_tw1 ?? 0)
              + (float)($sasaran->target_tw2 ?? 0)
              + (float)($sasaran->target_tw3 ?? 0)
              + (float)($sasaran->target_tw4 ?? 0);

        $sumR = (float)($sasaran->realisasi_tw1 ?? 0)
              + (float)($sasaran->realisasi_tw2 ?? 0)
              + (float)($sasaran->realisasi_tw3 ?? 0)
              + (float)($sasaran->realisasi_tw4 ?? 0);

        // fallback kalau total di DB belum keisi / masih 0 padahal ada TW
        if ($sasaranTargetTotal <= 0 && $sumT > 0) $sasaranTargetTotal = $sumT;
        if ($sasaranRealisasiTotal <= 0 && $sumR > 0) $sasaranRealisasiTotal = $sumR;
    }

    $sasaranCapaianTotal = ($sasaranTargetTotal > 0)
        ? round(($sasaranRealisasiTotal / $sasaranTargetTotal) * 100, 2)
        : 0;
@endphp

<div class="container mt-5 laporan-container">

    <h2 class="fw-bold text-center text-uppercase mb-4">📄 Laporan Realisasi Kegiatan</h2>

    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('realisasi.edit', $induk->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil-square"></i> Edit Data
        </a>
    </div>

    {{-- ============ TABEL OUTPUT ============ --}}
    <h5 class="fw-bold mt-4">a. Output (Sub Koordinator / Ess IV)</h5>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Triwulan</th>
                    <th>Uraian</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Capaian</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalTargetO    = 0;
                    $totalRealisasiO = 0;
                @endphp

                @foreach ($urutanTriwulan as $i => $tw)
                @php
                    $row = $outputs[$tw][0] ?? null;

                    $totalTargetO    += (float) ($row->target ?? 0);
                    $totalRealisasiO += (float) ($row->realisasi ?? 0);
                @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>Triwulan {{ $tw }}</td>
                        <td class="text-start">{{ $row?->uraian ?? '-' }}</td>
                        <td>{{ $row?->target ?? '-' }}</td>
                        <td>{{ $row?->realisasi ?? '-' }}</td>
                        <td>{{ is_numeric($row?->capaian) ? $row->capaian . '%' : '-' }}</td>
                    </tr>
                @endforeach

                <tr class="fw-bold table-light">
                    <td colspan="3" class="text-center">Jumlah</td>
                    <td>{{ $totalTargetO ?: '-' }}</td>
                    <td>{{ $totalRealisasiO ?: '-' }}</td>
                    <td>
                        @if($totalTargetO > 0)
                            {{ round($totalRealisasiO / $totalTargetO * 100, 2) }}%
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ============ TABEL OUTCOME ============ --}}
    <h5 class="fw-bold mt-5">b. Outcome (Eselon III)</h5>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Triwulan</th>
                    <th>Uraian</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Capaian</th>
                </tr>
            </thead>
            <tbody>
            @php
                $totalTargetOc    = 0;
                $totalRealisasiOc = 0;
            @endphp

            @foreach ($urutanTriwulan as $i => $tw)
            @php
                $row = $outcomes[$tw][0] ?? null;

                $totalTargetOc    += (float) ($row->target ?? 0);
                $totalRealisasiOc += (float) ($row->realisasi ?? 0);
            @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>Triwulan {{ $tw }}</td>
                        <td class="text-start">{{ $row?->uraian ?? '-' }}</td>
                        <td>{{ $row?->target ?? '-' }}</td>
                        <td>{{ $row?->realisasi ?? '-' }}</td>
                        <td>{{ is_numeric($row?->capaian) ? $row->capaian . '%' : '-' }}</td>
                    </tr>
            @endforeach

                <tr class="fw-bold table-light">
                    <td colspan="3" class="text-center">Jumlah</td>
                    <td>{{ $totalTargetOc ?: '-' }}</td>
                    <td>{{ $totalRealisasiOc ?: '-' }}</td>
                    <td>
                        @if($totalTargetOc > 0)
                            {{ round($totalRealisasiOc / $totalTargetOc * 100, 2) }}%
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ============ TABEL SASARAN (TOTAL) ============ --}}
    <h5 class="fw-bold mt-5">c. Sasaran (Eselon II)</h5>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Uraian Indikator</th>
                    <th>Target (Total)</th>
                    <th>Realisasi (Total)</th>
                    <th>Capaian (Total)</th>
                </tr>
            </thead>
            <tbody>
                @if($sasaran)
                    <tr>
                        <td>1</td>
                        <td class="text-start">{{ $sasaran->uraian ?? '-' }}</td>
                        <td>{{ $sasaranTargetTotal > 0 ? $sasaranTargetTotal : '-' }}</td>
                        <td>{{ $sasaranRealisasiTotal > 0 ? $sasaranRealisasiTotal : '-' }}</td>
                        <td>{{ $sasaranTargetTotal > 0 ? ($sasaranCapaianTotal . '%') : '-' }}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="5">Belum ada data sasaran</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    {{-- ✅ RINCIAN SASARAN PER TRIWULAN --}}
    @if($sasaran)
    <div class="table-responsive mt-2">
        <table class="table table-bordered text-center align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:60px;">No</th>
                    <th style="width:140px;">Triwulan</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Capaian</th>
                </tr>
            </thead>
            <tbody>
                @foreach([1,2,3,4] as $i)
                    @php
                        $t = (float)($sasaran->{"target_tw{$i}"} ?? 0);
                        $r = (float)($sasaran->{"realisasi_tw{$i}"} ?? 0);
                        $cap = $t > 0 ? round(($r / $t) * 100, 2) : 0;
                        $label = $urutanTriwulan[$i-1];
                    @endphp
                    <tr>
                        <td>{{ $i }}</td>
                        <td>Triwulan {{ $label }}</td>
                        <td>{{ $t > 0 ? $t : '-' }}</td>
                        <td>{{ $r > 0 ? $r : '-' }}</td>
                        <td>{{ $t > 0 ? ($cap . '%') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ============ TABEL PELAKSANAAN KEUANGAN ============ --}}
    <h5 class="fw-bold mt-5">d. Pelaksanaan Keuangan</h5>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Triwulan</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Capaian</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalTargetK    = 0;
                    $totalRealisasiK = 0;
                @endphp

                @foreach ($urutanTriwulan as $i => $tw)
                    @php
                        $row = $keuangans[$tw][0] ?? null;
                        $t   = (float) ($row->target ?? 0);
                        $r   = (float) ($row->realisasi ?? 0);

                        $totalTargetK    += $t;
                        $totalRealisasiK += $r;
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>Triwulan {{ $tw }}</td>
                        <td>{{ $row?->target ?? '-' }}</td>
                        <td>{{ $row?->realisasi ?? '-' }}</td>
                        <td>{{ is_numeric($row?->capaian) ? $row->capaian . '%' : '-' }}</td>
                    </tr>
                @endforeach

                <tr class="fw-bold table-light">
                    <td colspan="2" class="text-center">Jumlah</td>
                    <td>{{ $totalTargetK ?: '-' }}</td>
                    <td>{{ $totalRealisasiK ?: '-' }}</td>
                    <td>
                        @if($totalTargetK > 0)
                            {{ round($totalRealisasiK / $totalTargetK * 100, 2) }}%
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ===================== 6. KETERANGAN ===================== --}}
    <h5 class="fw-bold mt-5">6. Keterangan Keberhasilan / Hambatan</h5>

    @php
        $gabungKeb = [];
        $gabungHam = [];

        if ($keberhasilan) {
            foreach ([1, 2, 3, 4] as $twNo) {
                $kField = 'keberhasilan_tw' . $twNo;
                $hField = 'hambatan_tw' . $twNo;

                $kVal = $keberhasilan->$kField ?? '';
                $hVal = $keberhasilan->$hField ?? '';

                if (trim($kVal) !== '') {
                    $gabungKeb[] = ['label' => 'TW ' . $twNo, 'text' => $kVal];
                }

                if (trim($hVal) !== '') {
                    $gabungHam[] = ['label' => 'TW ' . $twNo, 'text' => $hVal];
                }
            }
        }
    @endphp

    @if ($keberhasilan)
        <div class="mb-3">
            <strong>a. Keberhasilan :</strong>

            @if($gabungKeb)
                <ul class="mt-2" style="list-style: none; padding-left: 0;">
                    @foreach($gabungKeb as $item)
                        <li class="mb-2">
                            <strong>- {{ $item['label'] }} :</strong><br>
                            {!! nl2br(e($item['text'])) !!}
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-2">- Tidak ada data keberhasilan.</p>
            @endif
        </div>

        <div class="mb-4">
            <strong>b. Hambatan :</strong>

            @if($gabungHam)
                <ul class="mt-2" style="list-style: none; padding-left: 0;">
                    @foreach($gabungHam as $item)
                        <li class="mb-2">
                            <strong>- {{ $item['label'] }} :</strong><br>
                            {!! nl2br(e($item['text'])) !!}
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="mt-2">- Tidak ada data hambatan.</p>
            @endif
        </div>
    @else
        <p>- Belum ada data keberhasilan atau hambatan</p>
    @endif

    {{-- ============ TABEL 7: 2 TAHUN SEBELUMNYA ============ --}}
    <h5 class="fw-bold mt-5">7. Hasil Pelaksanaan Kegiatan 2 Tahun Sebelumnya</h5>

    @php
        $tahunSekarang = (int) $induk->tahun;
        $thA = $tahunSekarang - 2;
        $thB = $tahunSekarang - 1;

        $rowA = $riwayat2Tahun[$thA] ?? ['target' => 0, 'realisasi' => 0, 'capaian' => 0];
        $rowB = $riwayat2Tahun[$thB] ?? ['target' => 0, 'realisasi' => 0, 'capaian' => 0];
    @endphp

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Uraian</th>
                <th>{{ $thA }}</th>
                <th>{{ $thB }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1.</td>
                <td>Target</td>
                <td>{{ $rowA['target'] ?: '-' }}</td>
                <td>{{ $rowB['target'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Realisasi</td>
                <td>{{ $rowA['realisasi'] ?: '-' }}</td>
                <td>{{ $rowB['realisasi'] ?: '-' }}</td>
            </tr>
            <tr>
                <td>3.</td>
                <td>Capaian</td>
                <td>{{ $rowA['capaian'] > 0 ? number_format($rowA['capaian'], 0) . '%' : '-' }}</td>
                <td>{{ $rowB['capaian'] > 0 ? number_format($rowB['capaian'], 0) . '%' : '-' }}</td>
            </tr>
        </tbody>
    </table>

    <a href="{{ route('realisasi.index') }}" class="btn btn-secondary">Kembali</a>
</div>
@endsection
