@extends('layout.weblab')

@section('content')
@php
  $isKasubagKeu = auth()->user()?->jabatan?->jenis_jabatan === 'kasubag_keuangan';
@endphp

<div class="edit-realisasi-wrapper mt-4 px-3">
  <div class="row">
    <div class="col-12">

      <div class="form-card shadow-sm p-4 rounded-4 border-0">
        <div class="form-card-header d-flex justify-content-between align-items-center mb-3">
          <div>
            <h2 class="form-title mb-1">Edit Data Realisasi Kegiatan</h2>
          </div>
          <span class="badge bg-primary-subtle text-primary fw-semibold small px-3 py-2">
            Tahun {{ $induk->tahun ?? date('Y') }}
          </span>
        </div>

        <form action="{{ route('realisasi.update', $induk->id) }}" method="POST">
          @csrf
          @method('PUT')

          {{-- 🔹 Output --}}
          <div class="form-section mb-4">
            <div class="form-section-header d-flex align-items-center mb-3">
              <h5 class="mb-0">
                a. Output <span class="text-muted fw-normal small">(Sub Koordinator / Ess IV)</span>
              </h5>
            </div>

            @php
              $targetOutputTw1 = isset($outputs['I'])
                  ? optional($outputs['I']->first())->target
                  : null;
            @endphp

            <div class="table-responsive table-section">
              <table class="table table-bordered align-middle table-laporan">
                <thead>
                  <tr>
                    <th style="width:60px;">No</th>
                    <th style="width:120px;">Triwulan</th>
                    <th>Uraian</th>
                    <th style="width:130px;">Target</th>
                    <th style="width:130px;">Realisasi</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach(['I','II','III','IV'] as $i => $tw)
                    @php
                      $o = isset($outputs[$tw]) ? $outputs[$tw]->first() : null;
                    @endphp
                    <tr>
                      <td class="text-center fw-semibold">{{ $i+1 }}</td>
                      <td class="text-center">Triwulan {{ $tw }}</td>

                      <td>
                        <input type="text"
                               name="output[{{ $tw }}][uraian]"
                               value="{{ $o->uraian ?? '' }}"
                               class="form-control form-control-soft"
                               @if($isKasubagKeu) readonly @endif>
                      </td>

                      <td>
                        @if($tw === 'I')
                          <input type="number"
                                 id="target-output-tw1"
                                 name="output[{{ $tw }}][target]"
                                 value="{{ $o->target ?? '' }}"
                                 class="form-control text-end form-control-soft"
                                 @if($isKasubagKeu) readonly @endif>
                        @else
                          {{-- HANYA TAMPIL: tidak dikirim ke backend supaya tidak membuat / mengubah record TW II–IV --}}
                          <input type="number"
                                 class="form-control text-end form-control-soft js-output-target-display"
                                 value="{{ $targetOutputTw1 }}"
                                 readonly>
                        @endif
                      </td>

                      <td>
                        <input type="number"
                               name="output[{{ $tw }}][realisasi]"
                               value="{{ $o->realisasi ?? '' }}"
                               class="form-control text-end form-control-soft"
                               @if($isKasubagKeu) readonly @endif>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          {{-- 🔹 Outcome --}}
          <div class="form-section mb-4">
            <div class="form-section-header d-flex align-items-center mb-3">
              <h5 class="mb-0">
                b. Outcome <span class="text-muted fw-normal small">(Eselon III)</span>
              </h5>
            </div>

            @php
              $targetOutcomeTw1 = isset($outcomes['I'])
                  ? optional($outcomes['I']->first())->target
                  : null;
            @endphp

            <div class="table-responsive table-section">
              <table class="table table-bordered align-middle table-laporan">
                <thead>
                  <tr>
                    <th style="width:60px;">No</th>
                    <th style="width:120px;">Triwulan</th>
                    <th>Uraian</th>
                    <th style="width:130px;">Target</th>
                    <th style="width:130px;">Realisasi</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach(['I','II','III','IV'] as $i => $tw)
                    @php
                      $oc = isset($outcomes[$tw]) ? $outcomes[$tw]->first() : null;
                    @endphp
                    <tr>
                      <td class="text-center fw-semibold">{{ $i+1 }}</td>
                      <td class="text-center">Triwulan {{ $tw }}</td>

                      <td>
                        <input type="text"
                               name="outcome[{{ $tw }}][uraian]"
                               value="{{ $oc->uraian ?? '' }}"
                               class="form-control form-control-soft"
                               @if($isKasubagKeu) readonly @endif>
                      </td>

                      <td>
                        {{-- HANYA TAMPIL: tidak dikirim ke backend supaya tidak membuat / mengubah record TW I–IV --}}
                        <input type="number"
                               class="form-control text-end form-control-soft js-outcome-target-display"
                               value="{{ $targetOutcomeTw1 }}"
                               readonly>
                      </td>

                      <td>
                        <input type="number"
                               name="outcome[{{ $tw }}][realisasi]"
                               value="{{ $oc->realisasi ?? '' }}"
                               class="form-control text-end form-control-soft"
                               @if($isKasubagKeu) readonly @endif>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          {{-- 🔹 Sasaran (Eselon II) --}}
          <div class="form-section mb-4">
            <div class="form-section-header d-flex align-items-center mb-3">
              <h5 class="mb-0">
                c. Sasaran <span class="text-muted fw-normal small">(Eselon II)</span>
              </h5>
            </div>

            @php
              $sasaran = $induk->sasaran;
              // target sasaran = total target output (semua TW), sesuai yang diminta
              $targetSasaran = collect($outputs)->flatten()->sum('target');
            @endphp

            <div class="table-responsive table-section">
              <table class="table table-bordered align-middle table-laporan">
                <thead>
                  <tr>
                    <th style="width:60px;">No</th>
                    <th>Uraian / Indikator</th>
                    <th style="width:130px;">Target</th>
                    <th style="width:130px;">Realisasi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="text-center fw-semibold">1</td>

                    <td>
                      <input type="text"
                             name="sasaran[uraian]"
                             value="{{ old('sasaran.uraian', optional($sasaran)->uraian) }}"
                             class="form-control form-control-soft"
                             @if($isKasubagKeu) readonly @endif>
                    </td>

                    <td>
                      {{-- tampil readonly --}}
                      <input type="number"
                             value="{{ $targetSasaran }}"
                             class="form-control text-end form-control-soft"
                             readonly>

                      {{-- tetap dikirim ke backend --}}
                      <input type="hidden"
                             name="sasaran[target]"
                             value="{{ $targetSasaran }}">
                    </td>

                    <td>
                      <input type="number"
                             name="sasaran[realisasi]"
                             value="{{ old('sasaran.realisasi', optional($sasaran)->realisasi) }}"
                             class="form-control text-end form-control-soft"
                             @if($isKasubagKeu) readonly @endif>
                    </td>

                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          {{-- 🔹 Keuangan --}}
          <div class="form-section mb-4">
            <div class="form-section-header d-flex align-items-center mb-3">
              <h5 class="mb-0">5. Pelaksanaan Keuangan</h5>
              @if($isKasubagKeu)
                <span class="ms-2 badge bg-warning-subtle text-warning fw-semibold">Kasubag Keuangan: bisa edit</span>
              @endif
            </div>

            <div class="table-responsive table-section">
              <table class="table table-bordered align-middle table-laporan">
                <thead>
                  <tr>
                    <th style="width:60px;">No</th>
                    <th style="width:120px;">Triwulan</th>
                    <th style="width:160px;">Target</th>
                    <th style="width:160px;">Realisasi</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach(['I','II','III','IV'] as $i => $tw)
                    @php $k = isset($keuangans[$tw]) ? $keuangans[$tw]->first() : null; @endphp
                    <tr>
                      <td class="text-center fw-semibold">{{ $i+1 }}</td>
                      <td class="text-center">Triwulan {{ $tw }}</td>
                      <td>
                        <input type="number"
                               name="keuangan[{{ $tw }}][target]"
                               value="{{ $k->target ?? '' }}"
                               class="form-control text-end form-control-soft"
                               @if($isKasubagKeu) readonly @endif>
                      </td>
                      <td>
                        <input type="number"
                               name="keuangan[{{ $tw }}][realisasi]"
                               value="{{ $k->realisasi ?? '' }}"
                               class="form-control text-end form-control-soft"
                               @if(!$isKasubagKeu) readonly @endif>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          {{-- 🔹 Keberhasilan / Hambatan --}}
          <div class="form-section mb-2">
            <div class="form-section-header d-flex align-items-center mb-3">
              <h5 class="mb-0">6. Keterangan Keberhasilan / Hambatan</h5>
            </div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">a. Keberhasilan</label>
                @for($i = 1; $i <= 4; $i++)
                  @php $field = "keberhasilan_tw{$i}"; @endphp
                  <div class="mb-2">
                    <small class="text-muted d-block mb-1">TW {{ $i }}</small>
                    <textarea
                      name="keberhasilan[{{ $field }}]"
                      class="form-control form-control-soft"
                      rows="2"
                      @if($isKasubagKeu) readonly @endif>{{ old("keberhasilan.$field", optional($keberhasilan)->$field) }}</textarea>
                  </div>
                @endfor
              </div>

              <div class="col-md-6">
                <label class="form-label small fw-semibold text-muted">b. Hambatan</label>
                @for($i = 1; $i <= 4; $i++)
                  @php $field = "hambatan_tw{$i}"; @endphp
                  <div class="mb-2">
                    <small class="text-muted d-block mb-1">TW {{ $i }}</small>
                    <textarea
                      name="keberhasilan[{{ $field }}]"
                      class="form-control form-control-soft"
                      rows="2"
                      @if($isKasubagKeu) readonly @endif>{{ old("keberhasilan.$field", optional($keberhasilan)->$field) }}</textarea>
                  </div>
                @endfor
              </div>
            </div>
          </div>

          {{-- 🔹 Tombol Aksi --}}
          <div class="form-actions mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
              Pastikan data sudah benar sebelum menyimpan perubahan.
              @if($isKasubagKeu)
                <span class="d-block">Catatan: Kasubag Keuangan hanya menyimpan perubahan pada bagian Keuangan.</span>
              @endif
            </div>
            <div class="btn-group-actions">
              <a href="{{ route('realisasi.show', $induk->id) }}" class="btn btn-secondary px-4">
                Batal
              </a>
              <button type="submit" class="btn btn-primary px-4">
                Simpan Perubahan
              </button>
            </div>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const inputTw1 = document.getElementById('target-output-tw1');
  if (!inputTw1) return;

  const syncTargets = () => {
    const val = inputTw1.value ?? '';

    // Output TW II–IV (display only)
    document.querySelectorAll('.js-output-target-display').forEach((el) => {
      el.value = val;
    });
  };

  inputTw1.addEventListener('input', syncTargets);
  inputTw1.addEventListener('change', syncTargets);

  // sync awal saat halaman pertama kali dibuka
  syncTargets();
});
</script>

@endsection
