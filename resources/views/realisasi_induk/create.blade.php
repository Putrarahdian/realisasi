@extends('layout.weblab')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            {{-- Title + Breadcrumb mini --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-1">Data Induk</h4>
                    <small class="text-muted">Isi informasi umum kinerja untuk satu sasaran/program.</small>
                </div>
                <a href="{{ route('realisasi.index') }}" class="btn btn-secondary btn-sm">
                    &larr; Kembali
                </a>
            </div>

            {{-- Alert success --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <form action="{{ route('realisasi-induk.store') }}" method="POST" id="form-induk">
                        @csrf

                        {{-- SECTION: Informasi Umum --}}
                        <h6 class="text-uppercase text-muted mb-3">Informasi Umum</h6>
                        <div class="row g-3">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                                <input type="date"
                                    name="induk[tanggal]"
                                    class="form-control"
                                    value="{{ old('tanggal') }}"
                                    required>
                            </div>

                            @if(auth()->user()->role === 'superuser')
                            <div class="col-md-4">
                                <label class="form-label">Bidang</label>
                                <select name="induk[bidang_id]" id="bidangSelect" class="form-select @error('induk.bidang_id') is-invalid @enderror">
                                    <option value="">-- Pilih Bidang --</option>
                                    @foreach($bidangs as $bidang)
                                        <option value="{{ $bidang->id }}"
                                            {{ old('induk.bidang_id') == $bidang->id ? 'selected' : '' }}>
                                            {{ $bidang->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('induk.bidang_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                                <div class="col-md-4">
                                    <label class="form-label">Seksi</label>
                                    <select name="induk[seksi_id]" id="seksiSelect" class="form-select @error('induk.seksi_id') is-invalid @enderror">
                                        <option value="">-- Pilih Seksi --</option>
                                        @foreach($seksis as $seksi)
                                            <option value="{{ $seksi->id }}"  data-bidang="{{ $seksi->bidang_id }}"
                                                    {{ old('induk.seksi_id') == $seksi->id ? 'selected' : '' }}>
                                                {{ $seksi->nama }} ({{ $seksi->bidang->nama ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('induk.seksi_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <hr class="my-4">

                        {{-- SECTION: Sasaran & Program --}}
                        <h6 class="text-uppercase text-muted mb-3">Sasaran & Program</h6>
                        <div class="mb-3">
                            <label class="form-label">Sasaran Strategis</label>
                            <input
                                type="text"
                                name="induk[sasaran_strategis]"
                                class="form-control @error('induk.sasaran_strategis') is-invalid @enderror"
                                value="{{ old('induk.sasaran_strategis') }}"
                                required
                            >
                            @error('induk.sasaran_strategis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Program/Kegiatan/Sub Kegiatan</label>
                            <input
                                type="text"
                                name="induk[program]"
                                class="form-control @error('induk.program') is-invalid @enderror"
                                value="{{ old('induk.program') }}"
                                required
                            >
                            @error('induk.program')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Indikator Prog/Keg/Sub Keg</label>
                            <input
                                type="text"
                                name="induk[indikator]"
                                class="form-control @error('induk.indikator') is-invalid @enderror"
                                value="{{ old('induk.indikator') }}"
                                required
                            >
                            @error('induk.indikator')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Target</label>
                            <input
                                type="text"
                                name="induk[target]"
                                class="form-control @error('induk.target') is-invalid @enderror"
                                value="{{ old('induk.target') }}"
                                required
                            >
                            @error('induk.target')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        {{-- SECTION: Hambatan & Keberhasilan (2 kolom, 1 kolom di DB) --}}
                        <h6 class="text-uppercase text-muted mb-2">Hambatan & Keberhasilan</h6>
                        {{-- hidden field yang tetap dikirim ke controller (satu kolom saja) --}}
                        <input
                            type="hidden"
                            name="induk[hambatan]"
                            id="hambatanCombined"
                            value="{{ old('induk.hambatan') }}"
                        >

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Hambatan</label>
                                <textarea
                                    id="hambatanInput"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Tuliskan hambatan pelaksanaan kegiatan..."
                                ></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Keberhasilan</label>
                                <textarea
                                    id="keberhasilanInput"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Tuliskan keberhasilan yang dicapai..."
                                ></textarea>
                            </div>
                        </div>
                        @error('induk.hambatan')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        <hr class="my-4">

                        {{-- SECTION: Rekomendasi & Tindak Lanjut --}}
                        <h6 class="text-uppercase text-muted mb-3">Rekomendasi & Tindak Lanjut</h6>

                        <div class="mb-3">
                            <label class="form-label">Rekomendasi</label>
                            <textarea
                                name="induk[rekomendasi]"
                                class="form-control @error('induk.rekomendasi') is-invalid @enderror"
                                rows="3"
                                required
                            >{{ old('induk.rekomendasi') }}</textarea>
                            @error('induk.rekomendasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">TL Rekomendasi Sebelumnya</label>
                            <textarea
                                name="induk[tindak_lanjut]"
                                class="form-control @error('induk.tindak_lanjut') is-invalid @enderror"
                                rows="3"
                                required
                            >{{ old('induk.tindak_lanjut') }}</textarea>
                            @error('induk.tindak_lanjut')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- SECTION: Dokumen & Strategi --}}
                        <hr class="my-4">
                        <h6 class="text-uppercase text-muted mb-3">Dokumen & Strategi</h6>

                        <div class="mb-3">
                            <label class="form-label">Nama Dokumen/Data Kinerja</label>
                            <input
                                type="text"
                                name="induk[dokumen]"
                                class="form-control @error('induk.dokumen') is-invalid @enderror"
                                value="{{ old('induk.dokumen') }}"
                                required
                            >
                            @error('induk.dokumen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Strategi yang Akan Dilakukan Triwulan Berikutnya</label>
                            <textarea
                                name="induk[strategi]"
                                class="form-control @error('induk.strategi') is-invalid @enderror"
                                rows="3"
                                required
                            >{{ old('induk.strategi') }}</textarea>
                            @error('induk.strategi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alasan Tidak Tercapai</label>
                            <textarea
                                name="induk[alasan]"
                                class="form-control @error('induk.alasan') is-invalid @enderror"
                                rows="3"
                                required
                            >{{ old('induk.alasan') }}</textarea>
                            @error('induk.alasan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="{{ route('realisasi.index') }}" class="btn btn-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-success">
                                Simpan
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- Script untuk menggabungkan hambatan + keberhasilan ke satu field sebelum submit --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form              = document.getElementById('form-induk');
        const hiddenCombined    = document.getElementById('hambatanCombined');
        const hambatanInput     = document.getElementById('hambatanInput');
        const keberhasilanInput = document.getElementById('keberhasilanInput');

        // Kalau ada nilai lama (old('induk.hambatan')) dan kedua kolom kosong,
        // taruh dulu ke kolom Hambatan supaya tidak hilang.
        if (hiddenCombined.value && !hambatanInput.value && !keberhasilanInput.value) {
            hambatanInput.value = hiddenCombined.value;
        }

        form.addEventListener('submit', function () {
            const hambatan     = hambatanInput.value.trim();
            const keberhasilan = keberhasilanInput.value.trim();

            let parts = [];

            if (hambatan) {
                parts.push('Hambatan:\n' + hambatan);
            }
            if (keberhasilan) {
                parts.push('Keberhasilan:\n' + keberhasilan);
            }

            hiddenCombined.value = parts.join('\n\n');
        });
        const bidangSelect = document.getElementById('bidangSelect');
    const seksiSelect  = document.getElementById('seksiSelect');

    if (bidangSelect && seksiSelect) {
        const oldSeksiId  = "{{ old('induk.seksi_id') }}";
        const oldBidangId = "{{ old('induk.bidang_id') }}";

        const allOptions = Array.from(seksiSelect.options);

        function filterSeksi() {
            const selectedBidangId = bidangSelect.value;

            // setiap ganti bidang, reset pilihan seksi
            seksiSelect.value = '';

            allOptions.forEach((opt, index) => {
                if (index === 0) {
                    // opsi pertama: "-- Pilih Seksi --"
                    opt.hidden   = false;
                    opt.disabled = false;
                    return;
                }

                const bidangIdOpt = opt.getAttribute('data-bidang');

                if (!selectedBidangId || bidangIdOpt === selectedBidangId) {
                    opt.hidden   = false;
                    opt.disabled = false;
                } else {
                    opt.hidden   = true;
                    opt.disabled = true;
                }
            });
        }

        // Jalankan pertama kali saat halaman dibuka
        filterSeksi();

        // Kalau ada old() dari validasi sebelumnya dan bidangnya masih cocok
        if (oldBidangId) {
            bidangSelect.value = oldBidangId;
            filterSeksi();

            if (oldSeksiId) {
                const opt = allOptions.find(
                    o => o.value === oldSeksiId && o.getAttribute('data-bidang') === oldBidangId
                );
                if (opt) {
                    seksiSelect.value = oldSeksiId;
                }
            }
        }

        // Setiap kali bidang diganti → filter ulang
        bidangSelect.addEventListener('change', filterSeksi);
    }
    });
</script>
@endsection
