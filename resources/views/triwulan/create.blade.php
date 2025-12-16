@extends('layout.weblab')

@section('content')
@php
  $isKasubagKeuangan = auth()->user()?->jabatan?->jenis_jabatan === 'kasubag_keuangan';

  // target dari DB (kalau seksi sudah isi)
  $targetKeuExisting = $keuangan->target ?? null;
@endphp

<div class="container py-4">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
      <h4 class="fw-bold mb-3">
        Tambah Realisasi Triwulan {{ $kodeTriwulan }}
      </h4>

      <p class="text-muted mb-1">
        Tahun: <strong>{{ $induk->tahun }}</strong><br>
        Program/Kegiatan: <strong>{{ $induk->program }}</strong><br>
        Indikator: <strong>{{ $induk->indikator }}</strong>
      </p>

      <p class="text-danger small mb-4">
        <span class="fw-bold">*</span> Wajib diisi
      </p>

      <form action="{{ route('realisasi.triwulan.store', [$no, $induk->id]) }}" method="POST">
        @csrf
        <input type="hidden" name="induk_id" value="{{ $induk->id }}">

        {{-- =========================================================
             BAGIAN SEKSI (NON-KASUBAG)
           ========================================================= --}}
        @if(!$isKasubagKeuangan)

          {{-- ================= OUTPUT ================= --}}
          <h5 class="fw-bold mt-3">a. Output (Sub Koordinator / Ess IV)</h5>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Uraian <span class="text-danger">*</span></label>
              <input type="text"
                     name="output[uraian]"
                     class="form-control @error('output.uraian') is-invalid @enderror"
                     value="{{ old('output.uraian') }}"
                     required>
              @error('output.uraian') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">
                Target
                @if($no == 1) <span class="text-danger">*</span> @endif
              </label>

              @if($no == 1)
                <input type="number"
                       name="output[target]"
                       class="form-control @error('output.target') is-invalid @enderror"
                       value="{{ old('output.target') }}"
                       required>
              @else
                <input type="number"
                       class="form-control"
                       value="{{ optional($twt['output'])->target }}"
                       readonly>
                <input type="hidden" name="output[target]" value="{{ optional($twt['output'])->target }}">
              @endif

              @error('output.target') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Realisasi <span class="text-danger">*</span></label>
              <input type="number"
                     name="output[realisasi]"
                     class="form-control @error('output.realisasi') is-invalid @enderror"
                     value="{{ old('output.realisasi') }}"
                     required>
              @error('output.realisasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <hr>

          {{-- ================= OUTCOME ================= --}}
          <h5 class="fw-bold mt-3">b. Outcome (Eselon III)</h5>
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Uraian <span class="text-danger">*</span></label>
              <input type="text"
                     name="outcome[uraian]"
                     class="form-control @error('outcome.uraian') is-invalid @enderror"
                     value="{{ old('outcome.uraian') }}"
                     required>
              @error('outcome.uraian') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">
                Target
                @if($no == 1) <span class="text-danger">*</span> @endif
              </label>

              @if($no == 1)
                <input type="number"
                       name="outcome[target]"
                       class="form-control @error('outcome.target') is-invalid @enderror"
                       value="{{ old('outcome.target') }}"
                       required>
              @else
                <input type="number"
                       class="form-control"
                       value="{{ optional($twt['outcome'])->target }}"
                       readonly>
                <input type="hidden" name="outcome[target]" value="{{ optional($twt['outcome'])->target }}">
              @endif

              @error('outcome.target') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-md-3">
              <label class="form-label">Realisasi <span class="text-danger">*</span></label>
              <input type="number"
                     name="outcome[realisasi]"
                     class="form-control @error('outcome.realisasi') is-invalid @enderror"
                     value="{{ old('outcome.realisasi') }}"
                     required>
              @error('outcome.realisasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>

          <hr>

          {{-- ================= SASARAN ================= --}}
          <div class="card mb-3">
            <div class="card-header bg-light">
              <strong>c. Sasaran</strong>
            </div>

            <div class="card-body">
              <div class="mb-3">
                <label class="form-label">
                  Uraian Sasaran
                  @if($no == 1) <span class="text-danger">*</span> @endif
                </label>

                <textarea name="sasaran[uraian]"
                          class="form-control @error('sasaran.uraian') is-invalid @enderror"
                          rows="2"
                          @if($no != 1) readonly @else required @endif>{{ old('sasaran.uraian', $sasaran->uraian ?? '') }}</textarea>
                @error('sasaran.uraian') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">
                    Target
                    @if($no == 1) <span class="text-danger">*</span> @endif
                  </label>
                  <input type="number" step="0.01"
                         name="sasaran[target]"
                         class="form-control @error('sasaran.target') is-invalid @enderror"
                         value="{{ old('sasaran.target', $sasaran->target ?? '') }}"
                         @if($no != 1) readonly @else required @endif>
                  @error('sasaran.target') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6 mb-3">
                  <label class="form-label">
                    Realisasi (isi di TW IV)
                    @if($no == 4) <span class="text-danger">*</span> @endif
                  </label>
                  <input type="number" step="0.01"
                         name="sasaran[realisasi]"
                         class="form-control @error('sasaran.realisasi') is-invalid @enderror"
                         value="{{ old('sasaran.realisasi', $sasaran->realisasi ?? '') }}"
                         @if($no != 4) readonly @else required @endif>
                  @error('sasaran.realisasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
              </div>
            </div>
          </div>

          <hr>
        @endif

        {{-- =========================================================
             KEUANGAN (ROLE-BASED)
           ========================================================= --}}
        <h5 class="fw-bold mt-3">Pelaksanaan Keuangan</h5>
        <div class="row mb-4">

          {{-- TARGET --}}
          <div class="col-md-6">
            <label class="form-label">Target <span class="text-danger">*</span></label>

            @if($isKasubagKeuangan)
              <input type="number" class="form-control"
                     value="{{ $targetKeuExisting ?? '' }}" readonly>

              <input type="hidden" name="keuangan[target]"
                     value="{{ $targetKeuExisting ?? 0 }}">
            @else
              <input type="number" min="0"
                     name="keuangan[target]"
                     class="form-control @error('keuangan.target') is-invalid @enderror"
                     value="{{ old('keuangan.target') }}"
                     required>
              @error('keuangan.target') <div class="invalid-feedback">{{ $message }}</div> @enderror
            @endif
          </div>

          {{-- REALISASI --}}
          <div class="col-md-6">
            <label class="form-label">Realisasi <span class="text-danger">*</span></label>

            @if($isKasubagKeuangan)
              <input type="number" min="0"
                     name="keuangan[realisasi]"
                     class="form-control @error('keuangan.realisasi') is-invalid @enderror"
                     value="{{ old('keuangan.realisasi') }}"
                     required>
              @error('keuangan.realisasi') <div class="invalid-feedback">{{ $message }}</div> @enderror
            @else
              <input type="number" class="form-control" value="0" readonly>
              <input type="hidden" name="keuangan[realisasi]" value="0">
            @endif
          </div>

        </div>

        {{-- KEBERHASILAN/HAMBATAN hanya untuk seksi --}}
        @if(!$isKasubagKeuangan)
          <div class="card mb-4">
            <div class="card-header bg-light">
              <strong>d. Keterangan Keberhasilan / Hambatan</strong>
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label">Keberhasilan (Triwulan {{ $kodeTriwulan }})</label>
                <textarea name="keberhasilan_triwulan"
                          class="form-control"
                          rows="3">{{ old('keberhasilan_triwulan') }}</textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Hambatan (Triwulan {{ $kodeTriwulan }})</label>
                <textarea name="hambatan_triwulan"
                          class="form-control"
                          rows="3">{{ old('hambatan_triwulan') }}</textarea>
              </div>
            </div>
          </div>
        @endif

        <div class="text-center mt-4">
          <button type="submit" class="btn btn-primary px-4">
            Simpan Triwulan {{ $kodeTriwulan }}
          </button>
          <a href="{{ route('realisasi.triwulan.index', $no) }}" class="btn btn-secondary px-4">
            Kembali
          </a>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection
