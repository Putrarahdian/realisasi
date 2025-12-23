@extends('layout.weblab')

@section('content')

@php
    $urutanTriwulan = ['I', 'II', 'III', 'IV'];

    // groupBy triwulan
    $outputs   = $induk->outputs?->groupBy('triwulan')   ?? collect();
    $outcomes  = $induk->outcomes?->groupBy('triwulan')  ?? collect();
    $keuangans = $induk->keuangans?->groupBy('triwulan') ?? collect();

    // relasi hasOne di model RealisasiInduk
    $sasaran      = $induk->sasaran ?? null;
    $keberhasilan = $induk->keberhasilan ?? null;

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
                // target tahunan diambil dari TW I (kalau ada)
                $totalTargetO   = 0;
                $totalRealisasiO  = 0;
            @endphp

            @foreach ($urutanTriwulan as $i => $tw)
                @php
                    $row = $outputs[$tw][0] ?? null;
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

                {{-- Baris TOTAL OUTPUT --}}
                <tr class="fw-bold table-light">
                    <td colspan="3" class="text-center">Jumlah</td>
                    <td>{{ $totalTargetO ?: '-' }}</td>
                    <td>{{ $totalRealisasiO ?: '-' }}</td>
                    <td>
                        @if($totalTargetO > 0)
                            {{ round($totalRealisasiO / $totalTargetO* 100, 2) }}%
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
                $totalTargetOc  = 0;
                $totalRealisasiOc = 0;
            @endphp

            @foreach ($urutanTriwulan as $i => $tw)
                @php
                    $row = $outcomes[$tw][0] ?? null;
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

                {{-- Baris TOTAL OUTCOME --}}
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

    {{-- ============ TABEL SASARAN ============ --}}
    <h5 class="fw-bold mt-5">c. Sasaran (Eselon II)</h5>
    <div class="table-responsive">
        <table class="table table-bordered text-center align-middle">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Uraian Indikator</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Capaian</th>
                </tr>
            </thead>
            <tbody>
                @if($sasaran)
                    <tr>
                        <td>1</td>
                        <td class="text-start">{{ $sasaran->uraian }}</td>
                        <td>{{ $sasaran->target ?? '-' }}</td>
                        <td>{{ $sasaran->realisasi ?? '-' }}</td>
                        <td>{{ $sasaran->capaian !== null ? $sasaran->capaian . '%' : '-' }}</td>
                    </tr>
                @else
                    <tr>
                        <td colspan="5">Belum ada data sasaran</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

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

                {{-- Baris TOTAL KEUANGAN --}}
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
                    $gabungKeb[] = [
                        'label' => 'TW ' . $twNo,
                        'text'  => $kVal,
                    ];
                }

                if (trim($hVal) !== '') {
                    $gabungHam[] = [
                        'label' => 'TW ' . $twNo,
                        'text'  => $hVal,
                    ];
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
