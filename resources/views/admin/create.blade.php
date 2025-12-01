@extends('layout.weblab')

@section('content')
<div class="container-fluid mt-4">
  <div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-semibold">Tambah Pengguna</h5>
      <a href="{{ route('admin.menu') }}" class="btn btn-outline-light btn-sm fw-semibold">
        <i class="bi bi-arrow-left-circle"></i> Kembali
      </a>
    </div>

    <div class="card-body bg-light">
      @php
        // ===== Context login =====
        $me               = auth()->user();
        $loginRole        = $me->role ?? 'user';
        $isAdminLogin     = $loginRole === 'admin';
        $isSuperuserLogin = $loginRole === 'superuser';

        // Default role awal: selalu user
        $defaultRole = 'user';

        // Admin tidak bisa ubah role (hanya buat user biasa)
        $lockRole    = $isAdminLogin;
      @endphp

      <form action="{{ route('user.store') }}" method="POST" class="row g-3 needs-validation" novalidate>
        @csrf

        {{-- ================= KOLOM KIRI ================= --}}
        <div class="col-md-6">
          {{-- Nama --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control shadow-sm"
                   value="{{ old('name') }}" required placeholder="Nama Lengkap">
          </div>

          {{-- NIP --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">NIP</label>
            <input type="text" id="nipInput" name="nip" class="form-control shadow-sm"
                   value="{{ old('nip') }}" placeholder="Masukkan NIP"
                   inputmode="numeric" pattern="[0-9]*">
          </div>

          {{-- Email --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control shadow-sm"
                   value="{{ old('email') }}" required placeholder="Masukkan email">
          </div>

          {{-- Password --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="password" name="password" id="passwordInput"
                     class="form-control shadow-sm" required
                     placeholder="Masukkan password...">
              <button type="button" class="btn btn-secondary" id="togglePassword" aria-label="Tampilkan/Sembunyikan password">
                <i class="bi bi-eye-fill" aria-hidden="true"></i>
              </button>
            </div>
            <small class="text-muted d-block mt-1">
              Password harus mengandung minimal 6 karakter, termasuk huruf besar, huruf kecil, angka, dan simbol.
            </small>
          </div>
        </div>

        {{-- ================= KOLOM KANAN ================= --}}
        <div class="col-md-6">

          {{-- Role --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
            <select name="role" id="roleSelect"
                    class="form-select shadow-sm"
                    @if($lockRole) disabled @endif
                    data-locked="{{ $lockRole ? '1' : '0' }}">
              @php $roles = ['user','admin','superuser']; @endphp
              @foreach($roles as $r)
                @if($r === 'superuser' && !$isSuperuserLogin) @continue @endif
                <option value="{{ $r }}" {{ old('role', $defaultRole) == $r ? 'selected' : '' }}>
                  {{ ucfirst($r) }}
                </option>
              @endforeach
            </select>
            @if($lockRole)
              {{-- untuk admin, tetap kirim role default (user) --}}
              <input type="hidden" name="role" value="{{ $defaultRole }}">
            @endif
          </div>

          {{-- Jabatan --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
            <select name="jabatan_id" id="jabatanSelect" class="form-select shadow-sm" required>
              <option value="">-- Pilih Jabatan --</option>
              @foreach($jabatans as $jab)
                <option value="{{ $jab->id }}"
                        data-jenis="{{ $jab->jenis_jabatan }}"
                        {{ old('jabatan_id') == $jab->id ? 'selected' : '' }}>
                  {{ $jab->nama }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Bidang --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Bidang</label>
            <select name="bidang_id" id="bidangSelect" class="form-select shadow-sm">
              <option value="">-- Pilih Bidang --</option>
              @foreach($bidangs as $b)
                <option value="{{ $b->id }}"
                        {{ old('bidang_id') == $b->id ? 'selected' : '' }}>
                  {{ $b->nama }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Seksi --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Seksi</label>
            <select name="seksi_id" id="seksiSelect" class="form-select shadow-sm">
              <option value="">-- Pilih Seksi --</option>
              @foreach($seksis as $s)
                <option value="{{ $s->id }}"
                        {{ old('seksi_id') == $s->id ? 'selected' : '' }}>
                  {{ $s->nama }}
                </option>
              @endforeach
            </select>
          </div>
        </div>

        {{-- Tombol --}}
        <div class="col-12 d-flex justify-content-end border-top pt-3">
          <button type="submit" class="btn btn-primary px-4 fw-semibold">
            <i class="bi bi-save"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ==================== SCRIPT ==================== --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  const roleSelect     = document.getElementById('roleSelect');
  const jabatanSelect  = document.getElementById('jabatanSelect');
  const bidangSelect   = document.getElementById('bidangSelect');
  const seksiSelect    = document.getElementById('seksiSelect');
  const passwordInput  = document.getElementById('passwordInput');
  const togglePassword = document.getElementById('togglePassword');
  const eyeIcon        = togglePassword?.querySelector('i');
  const urlTemplate    = "{{ route('get.seksi', ['bidang_id' => 'BIDANG_ID']) }}";
  const nipInput       = document.getElementById('nipInput');

  const roleLockedInitially = roleSelect?.dataset.locked === '1';

  // 👁️ Toggle password visibility
  if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', () => {
      const isPw = passwordInput.type === 'password';
      passwordInput.type = isPw ? 'text' : 'password';
      if (eyeIcon) {
        eyeIcon.classList.toggle('bi-eye-fill', !isPw);
        eyeIcon.classList.toggle('bi-eye-slash-fill', isPw);
      }
    });
  }

  // Kosongkan bawah saat ROLE diubah
  roleSelect?.addEventListener('change', () => {
    if (jabatanSelect) jabatanSelect.value = '';
    if (bidangSelect)  bidangSelect.value  = '';
    if (seksiSelect)   seksiSelect.value   = '';

    if (bidangSelect) bidangSelect.disabled = false;
    if (seksiSelect)  seksiSelect.disabled  = false;
  });

  // === Sinkron Role dengan Jabatan (kepala_dinas & kepala_bidang -> admin) ===
  function syncRoleWithJabatan() {
    if (!roleSelect || !jabatanSelect) return;
    // kalau dari PHP sudah dikunci (admin), jangan diutak-atik
    if (roleLockedInitially) return;

    const jenis = jabatanSelect.options[jabatanSelect.selectedIndex]?.dataset?.jenis || '';

    const isKepala = (jenis === 'kepala_dinas' || jenis === 'kepala_bidang');

    if (isKepala) {
      roleSelect.value = 'admin';
      roleSelect.dataset.lockedByJabatan = '1';
      roleSelect.style.pointerEvents = 'none';
      roleSelect.classList.add('bg-light');
    } else {
      if (roleSelect.dataset.lockedByJabatan === '1') {
        roleSelect.value = '{{ $defaultRole }}';
        roleSelect.dataset.lockedByJabatan = '0';
      }
      roleSelect.style.pointerEvents = 'auto';
      roleSelect.classList.remove('bg-light');
    }
  }

  // Load seksi berdasarkan bidang (AJAX)
  function loadSeksi(bidangId) {
    if (!seksiSelect) return;
    if (!bidangId) {
      seksiSelect.innerHTML = '<option value="">-- Pilih Seksi --</option>';
      return;
    }
    fetch(urlTemplate.replace('BIDANG_ID', bidangId))
      .then(r => r.json())
      .then(list => {
        seksiSelect.innerHTML = '<option value="">-- Pilih Seksi --</option>';
        list.forEach(s => {
          seksiSelect.insertAdjacentHTML('beforeend',
            `<option value="${s.id}">${s.nama}</option>`);
        });
        // kalau ada old('seksi_id'), coba pilih lagi
        const oldSeksi = '{{ old('seksi_id') }}';
        if (oldSeksi) {
          seksiSelect.value = oldSeksi;
        }
      })
      .catch(() => {
        seksiSelect.innerHTML = '<option>Gagal memuat data</option>';
      });
  }

  // Kunci/enable Bidang & Seksi sesuai jenis jabatan
  function updateLock() {
    if (!jabatanSelect || !bidangSelect || !seksiSelect) return;

    const jenis = jabatanSelect.options[jabatanSelect.selectedIndex]?.dataset?.jenis || '';

    // Kosongkan dulu
    // (kalau kamu tidak mau dikosongkan setiap ganti jabatan, ini bisa dihapus)
    //bidangSelect.value = '';
    //seksiSelect.value  = '';

    if (jenis === 'kepala_dinas' || jenis === 'sekretaris') {
      bidangSelect.disabled = true;
      seksiSelect.disabled  = true;
    }
    else if (jenis === 'kepala_bidang') {
      bidangSelect.disabled = false;
      seksiSelect.disabled  = true;
    }
    else if (jenis === 'kepala_seksi') {
      bidangSelect.disabled = false;
      seksiSelect.disabled  = false;
      if (bidangSelect.value) {
        loadSeksi(bidangSelect.value);
      }
    } else {
      bidangSelect.disabled = false;
      seksiSelect.disabled  = false;
    }
  }

  // Event: jabatan diubah
  jabatanSelect?.addEventListener('change', function () {
    updateLock();
    syncRoleWithJabatan();
  });

  // Event: bidang diubah -> reload list seksi
  bidangSelect?.addEventListener('change', function () {
    if (!seksiSelect) return;
    // hanya khusus jabatan kepala_seksi yang butuh filter, tapi aman kalau dipakai semua
    loadSeksi(bidangSelect.value || null);
  });

  // Inisialisasi saat halaman pertama kali dibuka (misal setelah validasi gagal)
  if (jabatanSelect && jabatanSelect.value) {
    updateLock();
    syncRoleWithJabatan();
    if (bidangSelect && bidangSelect.value && jabatanSelect.options[jabatanSelect.selectedIndex]?.dataset?.jenis === 'kepala_seksi') {
      loadSeksi(bidangSelect.value);
    }
  }

  // NIP hanya angka
  nipInput?.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
  });
});
</script>
@endsection
