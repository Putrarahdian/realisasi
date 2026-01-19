@extends('layout.weblab')

@section('content')
<div class="container">
    <h3>Tambah Target (Rencana)</h3>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('target.store') }}" method="POST">
        @csrf

        @if(auth()->user()->role === 'superuser')
        <div class="row">
            <div class="col-md-6 mb-3">
            <label class="form-label">Bidang</label>
            <select id="bidangSelect"
                    name="bidang_id"
                    class="form-select"
                    required>
                <option value="">-- Pilih Bidang --</option>
                    @foreach($bidangs as $bidang)
                    <option value="{{ $bidang->id }}"
                        {{ old('bidang_id') == $bidang->id ? 'selected' : '' }}>
                        {{ $bidang->nama }}
                    </option>
                    @endforeach
            </select>
            </div>

            <div class="col-md-6 mb-3">
            <label class="form-label">Seksi</label>
            <select id="seksiSelect"
                    name="seksi_id"
                    class="form-select"
                    disabled
                    required>
                <option value="">-- Pilih Seksi --</option>
                    @foreach($seksis as $seksi)
                    <option value="{{ $seksi->id }}"
                            data-bidang="{{ $seksi->bidang_id }}"
                            {{ old('seksi_id') == $seksi->id ? 'selected' : '' }}>
                        {{ $seksi->nama }}
                    </option>
                    @endforeach
            </select>
            </div>
        </div>
        @endif

        <div class="mb-3">
            <label class="form-label">Tahun</label>
            <input type="number" name="tahun" class="form-control"
                   value="{{ old('tahun', date('Y')) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Judul Target (contoh: SASARAN 2026)</label>
            <input type="text" name="judul" class="form-control"
                   value="{{ old('judul') }}" required>
        </div>

        <hr>

        <h5>Output</h5>
        <div class="mb-3">
            <label class="form-label">Uraian Output</label>
            <textarea name="output_uraian" class="form-control" rows="3" required>{{ old('output_uraian') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Target Output</label>
            <input type="text" name="output_target" class="form-control"
                   value="{{ old('output_target') }}" required>
        </div>

        <h5>Outcome</h5>
        <div class="mb-3">
            <label class="form-label">Uraian Outcome</label>
            <textarea name="outcome_uraian" class="form-control" rows="3" required>{{ old('outcome_uraian') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Target Outcome</label>
            <input type="text" name="outcome_target" class="form-control"
                   value="{{ old('outcome_target') }}" required>
        </div>

        <h5>Sasaran</h5>
        <div class="mb-3">
            <label class="form-label">Uraian Sasaran</label>
            <textarea name="sasaran_uraian" class="form-control" rows="3" required>{{ old('sasaran_uraian') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Target Sasaran</label>
            <input type="text" name="sasaran_target" class="form-control"
                   value="{{ old('sasaran_target') }}" required>
        </div>

        <h5>Keuangan</h5>
        <div class="mb-3">
            <label class="form-label">Uraian Keuangan</label>
            <textarea name="keuangan_uraian" class="form-control" rows="3" required>{{ old('keuangan_uraian') }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Target Keuangan</label>
            <input type="text" name="keuangan_target" class="form-control"
                   value="{{ old('keuangan_target') }}" required>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-success">Simpan</button>
            <a href="{{ route('target.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>

@if(auth()->user()->role === 'superuser')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bidangSelect = document.getElementById('bidangSelect');
    const seksiSelect  = document.getElementById('seksiSelect');
    if (!bidangSelect || !seksiSelect) return;

    const allSeksiOptions = Array.from(seksiSelect.options);
    const oldBidangId = "{{ old('bidang_id') }}";
    const oldSeksiId  = "{{ old('seksi_id') }}";

    function applyFilter() {
        const bidangId = bidangSelect.value;

        if (!bidangId) {
            seksiSelect.value = '';
            seksiSelect.disabled = true;

            allSeksiOptions.forEach((opt, index) => {
                if (index === 0) {
                    opt.hidden = false;
                    opt.disabled = false;
                } else {
                    opt.hidden = true;
                    opt.disabled = true;
                }
            });
            return;
        }

        seksiSelect.disabled = false;

        allSeksiOptions.forEach((opt, index) => {
            if (index === 0) {
                opt.hidden = false;
                opt.disabled = false;
                return;
            }

            const cocok = opt.dataset.bidang === bidangId;
            opt.hidden = !cocok;
            opt.disabled = !cocok;
        });
    }

    // init load
    if (oldBidangId) bidangSelect.value = oldBidangId;
    applyFilter();

    // set old seksi kalau masih cocok
    if (oldBidangId && oldSeksiId) {
        const opt = allSeksiOptions.find(o => o.value === oldSeksiId && o.dataset.bidang === oldBidangId);
        seksiSelect.value = opt ? oldSeksiId : '';
    }

    // bidang berubah → reset seksi + filter ulang
    bidangSelect.addEventListener('change', function () {
        seksiSelect.value = '';
        applyFilter();
    });
});
</script>
@endif

@endsection
