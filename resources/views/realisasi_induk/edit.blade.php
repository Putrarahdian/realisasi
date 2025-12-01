@extends('layout.weblab')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="card shadow-sm border-0">
                {{-- Header --}}
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">Edit Data</h5>
                    <a href="{{ route('realisasi.index') }}" class="btn btn-outline-light btn-sm fw-semibold">
                        <i class="bi bi-arrow-left-circle"></i> Kembali
                    </a>
                </div>

                {{-- Body --}}
                <div class="card-body bg-light">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Terjadi kesalahan:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @php
                        $rawHambatan = old('induk.hambatan', $induk->hambatan);

                        $hambatanText = $rawHambatan;
                        $keberhasilanText = '';

                        if (Str::contains($rawHambatan, 'Keberhasilan:')) {
                            [$hPart, $kPart] = explode('Keberhasilan:', $rawHambatan, 2);
                            $hambatanText = trim(preg_replace('/^Hambatan:\s*/i', '', $hPart));
                            $keberhasilanText = trim($kPart);
                        }
                    @endphp

                    <form id="formEditInduk"
                          action="{{ route('realisasi-induk.update', $induk->id) }}"
                          method="POST"
                          class="row g-3">
                        @csrf
                        @method('PUT')

                        {{-- ========== BAGIAN 1: INFORMASI KEGIATAN ========== --}}
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted mb-2">Informasi Kegiatan</h6>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body row g-3">

                                    {{-- Sasaran Strategis --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Sasaran Strategis</label>
                                        <input type="text"
                                               name="induk[sasaran_strategis]"
                                               class="form-control"
                                               value="{{ old('induk.sasaran_strategis', $induk->sasaran_strategis) }}"
                                               required>
                                    </div>

                                    {{-- Program / Kegiatan / Sub Kegiatan --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Program / Kegiatan / Sub Kegiatan</label>
                                        <input type="text"
                                               name="induk[program]"
                                               class="form-control"
                                               value="{{ old('induk.program', $induk->program) }}"
                                               required>
                                    </div>

                                    {{-- Indikator --}}
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Indikator Prog/Keg/Sub Keg</label>
                                        <input type="text"
                                               name="induk[indikator]"
                                               class="form-control"
                                               value="{{ old('induk.indikator', $induk->indikator) }}"
                                               required>
                                    </div>

                                    {{-- Target --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Target</label>
                                        <input type="text"
                                               name="induk[target]"
                                               class="form-control"
                                               value="{{ old('induk.target', $induk->target) }}"
                                               required>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- ========== BAGIAN 2: ANALISIS & TINDAK LANJUT ========== --}}
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted mt-3 mb-2">Analisis & Tindak Lanjut</h6>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body row g-3">

                                    {{-- Hambatan (input terpisah) --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Hambatan</label>
                                        <textarea id="hambatanInput"
                                                  class="form-control"
                                                  rows="3"
                                                  required>{{ old('hambatan_only', $hambatanText) }}</textarea>
                                    </div>

                                    {{-- Keberhasilan (input terpisah) --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Keberhasilan</label>
                                        <textarea id="keberhasilanInput"
                                                  class="form-control"
                                                  rows="3"
                                                  required>{{ old('keberhasilan_only', $keberhasilanText) }}</textarea>
                                    </div>

                                    {{-- Field asli yang dikirim ke server (tetap 1 kolom) --}}
                                    <textarea name="induk[hambatan]"
                                              id="hambatanHidden"
                                              class="d-none"
                                              required>{{ $rawHambatan }}</textarea>

                                    {{-- Rekomendasi --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Rekomendasi</label>
                                        <textarea name="induk[rekomendasi]"
                                                  class="form-control"
                                                  rows="3"
                                                  required>{{ old('induk.rekomendasi', $induk->rekomendasi) }}</textarea>
                                    </div>

                                    {{-- TL Rekomendasi Sebelumnya --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">TL Rekomendasi Sebelumnya</label>
                                        <textarea name="induk[tindak_lanjut]"
                                                  class="form-control"
                                                  rows="3"
                                                  required>{{ old('induk.tindak_lanjut', $induk->tindak_lanjut) }}</textarea>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- ========== BAGIAN 3: DOKUMEN & STRATEGI ========== --}}
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted mt-3 mb-2">Dokumen & Strategi</h6>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body row g-3">

                                    {{-- Nama Dokumen --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Nama Dokumen / Data Kinerja</label>
                                        <input type="text"
                                               name="induk[dokumen]"
                                               class="form-control"
                                               value="{{ old('induk.dokumen', $induk->dokumen) }}"
                                               required>
                                    </div>

                                    {{-- Strategi TW berikutnya --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Strategi yang Akan Dilakukan Triwulan Berikutnya</label>
                                        <textarea name="induk[strategi]"
                                                  class="form-control"
                                                  rows="3"
                                                  required>{{ old('induk.strategi', $induk->strategi) }}</textarea>
                                    </div>

                                    {{-- Alasan tidak tercapai --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Alasan Tidak Tercapai</label>
                                        <textarea name="induk[alasan]"
                                                  class="form-control"
                                                  rows="3"
                                                  required>{{ old('induk.alasan', $induk->alasan) }}</textarea>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- Tombol --}}
                        <div class="col-12 d-flex justify-content-end mt-3 border-top pt-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-save"></i> Perbarui
                            </button>
                            <a href="{{ route('realisasi.index') }}" class="btn btn-secondary">
                                Batal
                            </a>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Script gabung hambatan + keberhasilan ke 1 kolom sebelum submit --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form  = document.getElementById('formEditInduk');
    const ham   = document.getElementById('hambatanInput');
    const keb   = document.getElementById('keberhasilanInput');
    const hidden = document.getElementById('hambatanHidden');

    if (!form || !ham || !keb || !hidden) return;

    form.addEventListener('submit', function () {
        const hVal = ham.value.trim();
        const kVal = keb.value.trim();

        let combined = '';
        if (hVal || kVal) {
            combined = 'Hambatan:\n' + hVal + '\n\nKeberhasilan:\n' + kVal;
        }

        hidden.value = combined;
    });
});
</script>
@endsection
