@extends('layout.weblab')

@section('content')
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-10">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h4 class="mb-1">Data Induk</h4>
          <small class="text-muted">Pilih target (judul utama) lalu isi Output, Outcome, dan Sasaran.</small>
        </div>
        <a href="{{ route('realisasi.index') }}" class="btn btn-secondary btn-sm">&larr; Kembali</a>
      </div>

      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <div class="card shadow-sm border-0">
        <div class="card-body p-4">

          <form action="{{ route('realisasi-induk.store') }}" method="POST" id="form-induk">
            @csrf

            <h6 class="text-uppercase text-muted mb-3">Informasi Umum</h6>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal <span class="text-danger">*</span></label>
                <input type="date"
                       name="induk[tanggal]"
                       class="form-control @error('induk.tanggal') is-invalid @enderror"
                       value="{{ old('induk.tanggal', $defaultTanggal ?? '') }}"
                       required>
                @error('induk.tanggal')
                  <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>

              {{-- SUPERUSER: pilih bidang & seksi --}}
              @if(auth()->user()->role === 'superuser')
                <div class="col-md-6">
                  <label class="form-label">Bidang</label>
                  <select name="induk[bidang_id]" id="bidangSelect"
                          class="form-select @error('induk.bidang_id') is-invalid @enderror"
                          required>
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

                <div class="col-md-6">
                  <label class="form-label">Seksi</label>
                  <select name="induk[seksi_id]" id="seksiSelect"
                          class="form-select @error('induk.seksi_id') is-invalid @enderror"
                          required>
                    <option value="">-- Pilih Seksi --</option>
                    @foreach($seksis as $seksi)
                      <option value="{{ $seksi->id }}"
                              data-bidang="{{ $seksi->bidang_id }}"
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

            <h6 class="text-uppercase text-muted mb-3">Judul Utama (dari Target)</h6>

            <div class="mb-3">
              <label class="form-label fw-semibold">Pilih Target <span class="text-danger">*</span></label>
              <select id="targetSelect"
                      name="induk[target_id]"
                      class="form-select @error('induk.target_id') is-invalid @enderror"
                      required>
                <option value="">-- Pilih Target --</option>
                @foreach($targets as $t)
                  <option value="{{ $t->id }}"
                          data-bidang="{{ $t->bidang_id }}"
                          data-seksi="{{ $t->seksi_id }}"
                    {{ old('induk.target_id') == $t->id ? 'selected' : '' }}>
                    {{ $t->judul }} ({{ $t->tahun }})
                  </option>
                @endforeach
              </select>

              @error('induk.target_id')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror

              <small class="text-muted">
                Judul di PDF akan otomatis memakai judul target yang kamu pilih.
              </small>
            </div>

            <hr class="my-4">

            <h6 class="text-uppercase text-muted mb-3">Sub Judul (diisi manual di Data Induk)</h6>

            <div class="mb-3">
              <label class="form-label fw-semibold">1. Output <span class="text-danger">*</span></label>
              <textarea name="induk[output]"
                        class="form-control @error('induk.output') is-invalid @enderror"
                        rows="3" required>{{ old('induk.output') }}</textarea>
              @error('induk.output')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">2. Outcome <span class="text-danger">*</span></label>
              <textarea name="induk[outcome]"
                        class="form-control @error('induk.outcome') is-invalid @enderror"
                        rows="3" required>{{ old('induk.outcome') }}</textarea>
              @error('induk.outcome')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold">3. Sasaran <span class="text-danger">*</span></label>
              <textarea name="induk[sasaran]"
                        class="form-control @error('induk.sasaran') is-invalid @enderror"
                        rows="3" required>{{ old('induk.sasaran') }}</textarea>
              @error('induk.sasaran')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
              <a href="{{ route('realisasi.index') }}" class="btn btn-secondary">Batal</a>
              <button type="submit" class="btn btn-success">Simpan</button>
            </div>

          </form>

        </div>
      </div>

    </div>
  </div>
</div>

{{-- FILTER: khusus superuser --}}
@if(auth()->user()->role === 'superuser')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const bidangSelect = document.getElementById('bidangSelect');
  const seksiSelect  = document.getElementById('seksiSelect');
  const targetSelect = document.getElementById('targetSelect');

  if (!bidangSelect || !seksiSelect || !targetSelect) return;

  const allSeksiOptions  = Array.from(seksiSelect.options);
  const allTargetOptions = Array.from(targetSelect.options);

  const oldBidangId  = "{{ old('induk.bidang_id') }}";
  const oldSeksiId   = "{{ old('induk.seksi_id') }}";
  const oldTargetId  = "{{ old('induk.target_id') }}";

  function resetSeksi() { seksiSelect.value = ''; }
  function resetTarget() { targetSelect.value = ''; }

  function lockSeksi() { seksiSelect.disabled = true; }
  function unlockSeksi() { seksiSelect.disabled = false; }

  function lockTarget() { targetSelect.disabled = true; }
  function unlockTarget() { targetSelect.disabled = false; }

  function hideAllTargetsExceptPlaceholder() {
    allTargetOptions.forEach((opt, idx) => {
      if (idx === 0) { opt.hidden = false; opt.disabled = false; return; }
      opt.hidden = true;
      opt.disabled = true;
    });
  }

  function filterSeksi(reset = true) {
    const bidangId = bidangSelect.value;

    if (!bidangId) {
      lockSeksi();
      lockTarget();
      if (reset) { resetSeksi(); resetTarget(); }

      allSeksiOptions.forEach((opt, idx) => {
        if (idx === 0) { opt.hidden = false; opt.disabled = false; return; }
        opt.hidden = true;
        opt.disabled = true;
      });

      hideAllTargetsExceptPlaceholder();
      return;
    }

    unlockSeksi();
    lockTarget();
    if (reset) { resetSeksi(); resetTarget(); }

    allSeksiOptions.forEach((opt, idx) => {
      if (idx === 0) { opt.hidden = false; opt.disabled = false; return; }
      const cocok = opt.getAttribute('data-bidang') === bidangId;
      opt.hidden = !cocok;
      opt.disabled = !cocok;
    });

    // setelah seksi difilter, target belum boleh dipilih sebelum seksi dipilih
    hideAllTargetsExceptPlaceholder();
  }

  function filterTarget(reset = true) {
    const bidangId = bidangSelect.value;
    const seksiId  = seksiSelect.value;

    if (!bidangId || !seksiId) {
      lockTarget();
      if (reset) resetTarget();
      hideAllTargetsExceptPlaceholder();
      return;
    }

    unlockTarget();
    if (reset) resetTarget();

    let ada = false;

    allTargetOptions.forEach((opt, idx) => {
      if (idx === 0) { opt.hidden = false; opt.disabled = false; return; }

      const cocok = (opt.dataset.bidang === bidangId && opt.dataset.seksi === seksiId);
      opt.hidden = !cocok;
      opt.disabled = !cocok;

      if (cocok) ada = true;
    });

    if (!ada) resetTarget();
  }

  // ===== INIT =====
  if (oldBidangId) bidangSelect.value = oldBidangId;

  // init filter seksi tanpa reset (biar old() bisa dipasang)
  filterSeksi(false);

  if (oldBidangId && oldSeksiId) {
    const opt = allSeksiOptions.find(o => o.value === oldSeksiId && o.getAttribute('data-bidang') === oldBidangId);
    if (opt) {
      seksiSelect.value = oldSeksiId;
      filterTarget(false);
    } else {
      filterTarget(true);
    }
  } else {
    filterTarget(true);
  }

  if (oldTargetId) {
    const opt = allTargetOptions.find(o =>
      o.value === oldTargetId &&
      o.dataset.bidang === bidangSelect.value &&
      o.dataset.seksi === seksiSelect.value
    );
    if (opt) {
      unlockTarget();
      targetSelect.value = oldTargetId;
    } else {
      resetTarget();
    }
  }

  // ===== EVENTS =====
  bidangSelect.addEventListener('change', function () {
    filterSeksi(true);   // reset seksi + target
  });

  seksiSelect.addEventListener('change', function () {
    filterTarget(true);  // reset target & filter ulang
  });
});
</script>
@endif
@endsection
