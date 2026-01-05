@extends('layout.weblab')

@section('content')
<div class="container-fluid mt-4">
  <div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h5 class="mb-0 fw-semibold">
          Rekap Detail Kegiatan – {{ $induk->bidang->nama ?? 'Bidang Tidak Diketahui' }}
        </h5>
        <small class="text-muted">
          Tahun {{ $induk->tahun }} |
          Sasaran: {{ $induk->sasaran_strategis }}
        </small>
      </div>

      <div class="d-flex gap-2">
        <a href="{{ route('realisasi.rekap') }}" class="btn btn-secondary btn-sm">
          ⬅ Kembali ke Rekap Induk
        </a>

        @if($bolehDownload)
          <a href="{{ route('rekap.anak.download', $induk->id) }}"
             class="btn btn-success btn-sm">
            ⬇ Download Detail
          </a>
        @else
          <button class="btn btn-secondary btn-sm" disabled>
            <i class="bi bi-ban"></i> Menunggu Disposisi Kabid & Kadis
          </button>
        @endif
      </div>
    </div>

    <div class="card-body">

      {{-- INFO RINGKAS --}}
      <div class="mb-3">
        <p class="mb-1"><strong>Program / Kegiatan:</strong> {{ $induk->program }}</p>
        <p class="mb-1"><strong>Indikator:</strong> {{ $induk->indikator }}</p>
        <p class="mb-1"><strong>Target:</strong> {{ $induk->target }}</p>
      </div>

    {{-- ===========================
    OUTPUT (Sub Koordinator / Ess IV)
    =========================== --}}
    <h6 class="fw-bold mt-4">a. Output <small class="text-muted">(Sub Koordinator / Ess IV)</small></h6>
    <div class="table-responsive mb-4">
      <table class="table table-bordered align-middle">
        <thead class="table-light text-center">
          <tr>
            <th style="width: 40px;">No</th>
            <th style="width: 15%;">Waktu Pelaksanaan</th>
            <th>Uraian</th>
            <th style="width: 10%;">Target</th>
            <th style="width: 10%;">Realisasi</th>
            <th style="width: 10%;">Capaian (%)</th>
          </tr>
        </thead>
        <tbody>
        @php
          $totalTargetO = 0;
          $totalRealisasiO = 0;
        @endphp

        @foreach(['I','II','III','IV'] as $tw)
            @php
              $o = optional($outputs[$tw] ?? collect())->first();

              if($o){
                  $totalTargetO += (float)($o->target ?? 0);
                  $totalRealisasiO += (float)($o->realisasi ?? 0);
              }
            @endphp
            <tr>
              <td class="text-center">{{ $loop->iteration }}</td>
              <td>Triwulan {{ $tw }}</td>
              <td>{{ $o->uraian ?? '-' }}</td>
              <td class="text-center">{{ $o->target ?? '-' }}</td>
              <td class="text-center">{{ $o->realisasi ?? '-' }}</td>
              <td class="text-center">{{ optional($o)->capaian !== null ? number_format(optional($o)->capaian, 0) . '%' : '-' }}</td>
            </tr>
        @endforeach

        @php
            $capaianTotalO = ($totalTargetO > 0) ? round($totalRealisasiO / $totalTargetO * 100, 2) : null;
        @endphp

        <tr class="fw-bold text-center table-light">
            <td colspan="3">Jumlah</td>
            <td>{{ $totalTargetO ?: '-' }}</td>
            <td>{{ $totalRealisasiO ?: '-' }}</td>
            <td>{{ !is_null($capaianTotalO) ? number_format($capaianTotalO, 0) . '%' : '-' }}</td>
        </tr>

        </tbody>
      </table>
    </div>

    {{-- ===========================
        OUTCOME (Eselon III)
    =========================== --}}
    <h6 class="fw-bold mt-4">b. Outcome <small class="text-muted">(Eselon III)</small></h6>
    <div class="table-responsive mb-4">
      <table class="table table-bordered align-middle">
        <thead class="table-light text-center">
          <tr>
            <th style="width: 40px;">No</th>
            <th style="width: 15%;">Waktu Pelaksanaan</th>
            <th>Uraian</th>
            <th style="width: 10%;">Target</th>
            <th style="width: 10%;">Realisasi</th>
            <th style="width: 10%;">Capaian (%)</th>
          </tr>
        </thead>
        <tbody>
        @php
          $totalTargetOc = 0;
          $totalRealisasiOc = 0;
        @endphp

        @foreach(['I','II','III','IV'] as $tw)
            @php
              $oc = optional($outcomes[$tw] ?? collect())->first();

              if($oc){
                  $totalTargetOc += (float)($oc->target ?? 0);
                  $totalRealisasiOc += (float)($oc->realisasi ?? 0);
                }
            @endphp
            <tr>
              <td class="text-center">{{ $loop->iteration }}</td>
              <td>Triwulan {{ $tw }}</td>
              <td>{{ $oc->uraian ?? '-' }}</td>
              <td class="text-center">{{ $oc->target ?? '-' }}</td>
              <td class="text-center">{{ $oc->realisasi ?? '-' }}</td>
              <td class="text-center">{{ optional($oc)->capaian !== null ? number_format(optional($oc)->capaian, 0) . '%' : '-' }}</td>
            </tr>
        @endforeach

        @php
            $capaianTotalOc = ($totalTargetOc > 0) ? round($totalRealisasiOc / $totalTargetOc * 100, 2) : null;
        @endphp

        <tr class="fw-bold text-center table-light">
            <td colspan="3">Jumlah</td>
            <td>{{ $totalTargetOc ?: '-' }}</td>
            <td>{{ $totalRealisasiOc ?: '-' }}</td>
            <td>{{ !is_null($capaianTotalOc) ? number_format($capaianTotalOc, 0) . '%' : '-' }}</td>
        </tr>
        </tbody>
      </table>
    </div>

{{-- ===========================
    SASARAN (Eselon II) 
=========================== --}}
<h6 class="fw-bold mt-4">c. Sasaran <small class="text-muted">(Eselon II)</small></h6>
<div class="table-responsive mb-4">
  <table class="table table-bordered align-middle">
    <thead class="table-light text-center">
      <tr>
        <th style="width: 40px;">No</th>
        <th>Uraian / Indikator</th>
        <th style="width: 10%;">Target</th>
        <th style="width: 10%;">Realisasi</th>
        <th style="width: 10%;">Capaian (%)</th>
      </tr>
    </thead>

    <tbody>
      @php
        $no = 1;

        // ✅ pastikan sasaran bukan collection
        $sas = $sasaran instanceof \Illuminate\Support\Collection ? $sasaran->first() : $sasaran;

        $sasaranTarget   = (float) ($sas->target ?? 0);
        $sasaranRealisasi= (float) ($sas->realisasi ?? 0);
        $sasaranCapaian  = $sasaranTarget > 0
            ? round(($sasaranRealisasi / $sasaranTarget) * 100, 2)
            : null;
      @endphp

      @if($sas)
        <tr>
          <td class="text-center">{{ $no++ }}</td>
          <td>{{ $sas->uraian ?? '-' }}</td>
          <td class="text-center">{{ $sasaranTarget > 0 ? $sasaranTarget : '-' }}</td>
          <td class="text-center">{{ $sasaranRealisasi > 0 ? $sasaranRealisasi : '-' }}</td>
          <td class="text-center">
            @if(!is_null($sasaranCapaian))
              {{ rtrim(rtrim(number_format($sasaranCapaian, 2, ',', '.'), '0'), ',') }}%
            @else
              -
            @endif
          </td>
        </tr>
      @else
        <tr>
          <td colspan="5" class="text-center text-muted">
            Belum ada data sasaran.
          </td>
        </tr>
      @endif
    </tbody>

  </table>
</div>

    {{-- ===========================
        KEUANGAN
    =========================== --}}
    <h6 class="fw-bold mt-4"> Keuangan</h6>
    <div class="table-responsive mb-4">
      <table class="table table-bordered align-middle">
        <thead class="table-light text-center">
          <tr>
            <th style="width: 40px;">No</th>
            <th style="width: 15%;">Uraian</th>
            <th style="width: 30%;">Target</th>
            <th style="width: 30%;">Realisasi</th>
            <th style="width: 25%;">Capaian (%)</th>
          </tr>
        </thead>
        <tbody>
          @php
            $no = 1;
            $totalTargetK     = 0;
            $totalRealisasiK  = 0;

            $totalTargetAll = $induk->keuangans->sum('target');
          @endphp

          @foreach (['I','II','III','IV'] as $tw)
            @php
              $target     = null;
              $realisasi  = null;
              $rowCapaian = null;

              $k = optional($keuangans[$tw] ?? collect())->first();
              if ($k) {
                $target   = $k->target ?? 0;
                $realisasi = $k->realisasi ?? 0;

                $totalTargetK += $target;
                $totalRealisasiK += $realisasi;

                if ($totalTargetAll > 0 && $realisasi > 0) {
                  $rowCapaian = round($realisasi / $totalTargetAll * 100, 2);
                }
              }
            @endphp
            <tr>
              <td class="text-center">{{ $no++ }}</td>
              <td>Triwulan {{ $tw }}</td>
              <td class="text-center">{{ $target ?? '-' }}</td>
              <td class="text-center">{{ $realisasi ?? '-' }}</td>
              <td class="text-center">
                @if (!is_null($rowCapaian))
                  {{ rtrim(rtrim(number_format($rowCapaian, 2, ',', '.'), '0'), ',') }}%
                @else
                  -
                @endif
              </td>
            </tr>
          @endforeach

          @php
            $totalCapaianK = ($totalTargetAll > 0 && $totalRealisasiK > 0)
              ? round($totalRealisasiK / $totalTargetAll * 100, 2)
              : null;
          @endphp
          <tr class="fw-bold text-center table-light">
            <td colspan="2">Jumlah</td>
            <td>{{ $totalTargetK ?: '-' }}</td>
            <td>{{ $totalRealisasiK ?: '-' }}</td>
            <td>
              @if(!is_null($totalCapaianK))
                {{ rtrim(rtrim(number_format($totalCapaianK, 2, ',', '.'), '0'), ',') }}%
              @else
                -
              @endif
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    {{-- 7. Hasil pelaksanaan kegiatan 2 tahun sebelumnya --}}
@php
    $tahunSekarang = (int) $induk->tahun;
    $thA = $tahunSekarang - 2;
    $thB = $tahunSekarang - 1;

    $rowA = $riwayat2Tahun[$thA] ?? ['target' => 0, 'realisasi' => 0, 'capaian' => 0];
    $rowB = $riwayat2Tahun[$thB] ?? ['target' => 0, 'realisasi' => 0, 'capaian' => 0];
@endphp

<br>
<p><strong>7. Hasil pelaksanaan kegiatan 2 tahun sebelumnya:</strong></p>

<table border="1" cellspacing="0" cellpadding="4" style="width:100%; border-collapse: collapse; font-size: 11px;">
    <tr>
        <th style="width:40px; text-align:center;">No</th>
        <th style="width:120px; text-align:center;">Uraian</th>
        <th style="text-align:center;">{{ $thA }}</th>
        <th style="text-align:center;">{{ $thB }}</th>
    </tr>
    <tr>
        <td style="text-align:center;">1.</td>
        <td>Target</td>
        <td style="text-align:center;">{{ $rowA['target'] ?: '-' }}</td>
        <td style="text-align:center;">{{ $rowB['target'] ?: '-' }}</td>
    </tr>
    <tr>
        <td style="text-align:center;">2.</td>
        <td>Realisasi</td>
        <td style="text-align:center;">{{ $rowA['realisasi'] ?: '-' }}</td>
        <td style="text-align:center;">{{ $rowB['realisasi'] ?: '-' }}</td>
    </tr>
    <tr>
        <td style="text-align:center;">3.</td>
        <td>Capaian</td>
        <td style="text-align:center;">
            {{ $rowA['capaian'] > 0 ? number_format($rowA['capaian'], 0) . '%' : '-' }}
        </td>
        <td style="text-align:center;">
            {{ $rowB['capaian'] > 0 ? number_format($rowB['capaian'], 0) . '%' : '-' }}
        </td>
    </tr>
</table>

      {{-- STATUS DISPOSISI KABID & KADIS --}}
      @php
          $jabatan = optional(auth()->user()->jabatan)->nama;
      @endphp

      <hr class="my-4">

      <h6 class="fw-bold mb-3">Status Disposisi</h6>

      <div class="row g-3 mb-3">
        {{-- Disposisi Kabid --}}
        <div class="col-md-6">
          <div class="border rounded-3 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold">Disposisi Kepala Bidang</span>
              @if($induk->disposisi_kabid)
                <span class="badge bg-success">Sudah diisi</span>
              @else
                <span class="badge bg-secondary">Belum diisi</span>
              @endif
            </div>
            <div class="small mb-0 text-muted">
              {!! $induk->disposisi_kabid ?? 'Belum ada catatan dari Kepala Bidang.' !!}
            </div>
          </div>
        </div>

        {{-- Disposisi Kadis --}}
        <div class="col-md-6">
          <div class="border rounded-3 p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-semibold">Disposisi Kepala Dinas</span>
              @if($induk->disposisi_kadis)
                <span class="badge bg-success">Sudah diisi</span>
              @else
                <span class="badge bg-secondary">Belum diisi</span>
              @endif
            </div>
            <div class="small mb-0 text-muted">
              {!! $induk->disposisi_kadis ?? 'Belum ada catatan dari Kepala Dinas.' !!}
            </div>
          </div>
        </div>
      </div>

      {{-- FORM DISPOSISI (HANYA UNTUK KABID / KADIS) --}}
      @if(in_array($jabatan, ['Kepala Bidang', 'Kepala Dinas']))
        <hr class="my-3">

        <h6 class="fw-bold mb-2">
          Form Disposisi {{ $jabatan }}
        </h6>
        <p class="text-muted small mb-3">
          Isi catatan disposisi sesuai kewenangan Anda. Catatan akan muncul pada kotak di atas
          dan menjadi syarat untuk membuka akses download dokumen Word.
        </p>

        <form action="{{ route('realisasi.rekap.anak.disposisi', $induk->id) }}" method="POST">
          @csrf

          <div class="mb-2">
            <label class="form-label">Disposisi</label>
            <textarea
              name="disposisi"
              id="disposisiEditor"
              class="form-control @error('disposisi') is-invalid @enderror"
              rows="3"
              >{!! old('disposisi', $jabatan === 'Kepala Bidang' ? $induk->disposisi_kabid : $induk->disposisi_kadis) !!}</textarea>

            @error('disposisi')
              <div class="invalid-feedback">
                {{ $message }}
              </div>
            @enderror

            <small class="text-muted d-block mt-1">
              Contoh: "Setuju, lanjutkan pelaksanaan sesuai jadwal." / "Mohon dilengkapi dokumen pendukung."
            </small>
          </div>

          <button type="submit" class="btn btn-primary btn-sm mt-1">
            Simpan Disposisi
          </button>
        </form>
      @endif

    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const makeEditor = (selector) => {
    const el = document.querySelector(selector);
    if (!el) return;
    ClassicEditor.create(el).catch(console.error);
  };

  makeEditor('#disposisiEditor');
});
</script>
@endsection
