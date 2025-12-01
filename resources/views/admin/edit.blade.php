@extends('layout.weblab')

@section('content')
<div class="container-fluid mt-4">
  <div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
      <h5 class="mb-0 fw-semibold">Edit Pengguna</h5>
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

        $target            = $user ?? null;
        $targetRole        = old('role', $target->role ?? 'user');
        $targetJabatan     = optional($target->jabatan);
        $targetJabatanNama = $targetJabatan->nama ?? null;

        // Superuser utama (id = 1 + role superuser)
        $isMasterSuperuser = ($target->id === 1 && $target->role === 'superuser');

        // 3 jabatan tetap yang harus dikunci kanan-nya
        $lockedTitles = ['Kepala Dinas', 'Sekretaris', 'Kepala Bidang'];
        $isLockedByTitle = in_array($targetJabatanNama, $lockedTitles);

        // 6 akun spesial = 3 jabatan di atas + superuser utama
        $lockRightSide = $isLockedByTitle || $isMasterSuperuser;

        // ➜ Role terkunci untuk:
        //    - 6 akun spesial
        //    - siapapun kalau yang login adalah admin
        $lockRole    = $lockRightSide || $isAdminLogin;

        // ➜ Jabatan / Bidang / Seksi terkunci untuk 6 akun spesial
        $lockJabatan = $lockRightSide;
        $lockBidang  = $lockRightSide;
        $lockSeksi   = $lockRightSide;

        // ➜ NIP:
        //    - Kepala Dinas / Sekretaris / Kabid: BOLEH diubah
        //    - Superuser utama: dikunci
        $lockNip     = $isMasterSuperuser;

        $valueRole   = $targetRole;
    @endphp

      <form action="{{ route('admin.users.update', $target->id) }}" method="POST" class="row g-3 needs-validation" novalidate>
        @csrf
        @method('PUT')

        {{-- ================= KOLOM KIRI ================= --}}
        <div class="col-md-6">
          {{-- Nama --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="name" id="nameInput" class="form-control shadow-sm"
                   value="{{ old('name', $target->name ?? '') }}" required placeholder="Nama Lengkap">
          </div>

          {{-- NIP --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">NIP</label>
            <input type="text" name="nip" id="nipInput" class="form-control shadow-sm"
                   value="{{ old('nip', $target->nip ?? '') }}" placeholder="Masukkan NIP" inputmode="numeric" pattern="[0-9]*"@if($lockNip) disabled @endif>
            @if($lockNip)
              <input type="hidden" name="nip" value="{{ $target->nip }}">
            @endif
          </div>

          {{-- Email --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control shadow-sm"
                   value="{{ old('email', $target->email ?? '') }}" required>
          </div>

          {{-- Password + --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Password (Kosongkan jika tidak diubah)</label>
            <div class="input-group">
              <input type="password" name="password" id="passwordInput" class="form-control shadow-sm"
                     placeholder="Masukkan password baru jika ingin mengganti...">
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
            <select name="role" id="roleSelect" class="form-select shadow-sm"
                    @if($lockRole) disabled @endif
                    data-locked="{{ $lockRole ? '1' : '0' }}">
              @php $roleOptions = ['user','admin','superuser']; @endphp
              @foreach($roleOptions as $r)
                <option value="{{ $r }}" {{ $valueRole == $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
              @endforeach
            </select>
            @if($lockRole)
              <input type="hidden" name="role" value="{{ $valueRole }}">
            @endif
          </div>

          {{-- Jabatan --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Jabatan <span class="text-danger">*</span></label>
            <select name="jabatan_id" id="jabatanSelect" class="form-select shadow-sm"
                    @if($lockJabatan) disabled @endif required>
              <option value="">-- Pilih Jabatan --</option>
              @foreach($jabatans as $jab)
                <option value="{{ $jab->id }}" data-jenis="{{ $jab->jenis_jabatan }}"
                        {{ (string)old('jabatan_id', $target->jabatan_id ?? '') === (string)$jab->id ? 'selected' : '' }}>
                  {{ $jab->nama }}
                </option>
              @endforeach
            </select>
            @if($lockJabatan)
              <input type="hidden" name="jabatan_id" value="{{ $target->jabatan_id }}">
            @endif
          </div>

          {{-- Bidang --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Bidang</label>
            <select name="bidang_id" id="bidangSelect" class="form-select shadow-sm" @if($lockBidang) disabled @endif>
              <option value="">-- Pilih Bidang --</option>
              @foreach($bidangs as $b)
                <option value="{{ $b->id }}"
                        {{ (string)old('bidang_id', $target->bidang_id ?? '') === (string)$b->id ? 'selected' : '' }}>
                  {{ $b->nama }}
                </option>
              @endforeach
            </select>
            @if($lockBidang)
              <input type="hidden" name="bidang_id" value="{{ $target->bidang_id }}">
            @endif
          </div>

          {{-- Seksi --}}
          <div class="mb-3">
            <label class="form-label fw-semibold">Seksi</label>
            <select name="seksi_id" id="seksiSelect" class="form-select shadow-sm" @if($lockSeksi) disabled @endif>
              <option value="">-- Pilih Seksi --</option>
              @foreach($seksis as $s)
                <option value="{{ $s->id }}"
                        {{ (string)old('seksi_id', $target->seksi_id ?? '') === (string)$s->id ? 'selected' : '' }}>
                  {{ $s->nama }}
                </option>
              @endforeach
            </select>
            @if($lockSeksi)
              <input type="hidden" name="seksi_id" value="{{ $target->seksi_id }}">
            @endif
          </div>
        </div>

        {{-- Tombol --}}
        <div class="col-12 d-flex justify-content-end border-top pt-3">
          <button type="submit" class="btn btn-primary px-4 fw-semibold">
            <i class="bi bi-save"></i> Perbarui
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
  const initialSeksiId = "{{ old('seksi_id', $target->seksi_id ?? '') }}";

  const initiallyLocked = {
    bidang:  !!bidangSelect?.hasAttribute('disabled'),
    seksi :  !!seksiSelect?.hasAttribute('disabled'),
    jabatan: !!jabatanSelect?.hasAttribute('disabled'),
    role:    !!roleSelect?.hasAttribute('disabled'),
  };

  // role terkunci dari server? (misal akun spesial atau admin yang login)
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
    if (jabatanSelect && !initiallyLocked.jabatan) jabatanSelect.value = '';
    if (bidangSelect  && !initiallyLocked.bidang)  bidangSelect.value  = '';
    if (seksiSelect   && !initiallyLocked.seksi)   seksiSelect.value   = '';
  });

  // === Sinkron Role dengan Jabatan (kepala_dinas & kepala_bidang -> admin) ===
  function syncRoleWithJabatan() {
    if (!roleSelect || !jabatanSelect) return;
    // kalau dari PHP sudah dikunci (akun spesial / admin login) jangan diutak-atik
    if (roleLockedInitially) return;

    const jenis = jabatanSelect.options[jabatanSelect.selectedIndex]?.dataset?.jenis || '';
    const isKepala = (jenis === 'kepala_dinas' || jenis === 'kepala_bidang');

    if (isKepala) {
      roleSelect.value = 'admin';
      roleSelect.dataset.lockedByJabatan = '1';
      roleSelect.style.pointerEvents = 'none';
      roleSelect.classList.add('bg-light');
    } else {
      // kalau sebelumnya dikunci karena jabatan, balikan ke role awal (user/admin sesuai data)
      if (roleSelect.dataset.lockedByJabatan === '1') {
        roleSelect.value = '{{ $valueRole }}';
        roleSelect.dataset.lockedByJabatan = '0';
      }
      roleSelect.style.pointerEvents = 'auto';
      roleSelect.classList.remove('bg-light');
    }
  }

  // Load seksi berdasarkan bidang
  function loadSeksi(bidangId, selectedId = null) {
    if (!seksiSelect) return;

    if (!bidangId) {
      seksiSelect.innerHTML = '<option value="">-- Pilih Seksi --</option>';
      return;
    }

    fetch(urlTemplate.replace('BIDANG_ID', bidangId))
      .then(r => r.json())
      .then(list => {
        seksiSelect.innerHTML = '<option value="">-- Pilih Seksi --</option>';

        const currentSelected = selectedId !== null
          ? String(selectedId)
          : String(initialSeksiId);

        list.forEach(s => {
          const isSelected = currentSelected && String(s.id) === currentSelected ? 'selected' : '';
          seksiSelect.insertAdjacentHTML(
            'beforeend',
            `<option value="${s.id}" ${isSelected}>${s.nama}</option>`
          );
        });
      })
      .catch(() => {
        seksiSelect.innerHTML = '<option value="">Gagal memuat data</option>';
      });
  }

  // Kunci dinamis & kosongkan saat JABATAN diubah
  function updateLock({ clear = false } = {}) {
    if (!jabatanSelect) return;

    // kalau dari awal memang sudah dikunci (akun spesial), jangan diubah2
    if (initiallyLocked.bidang || initiallyLocked.seksi) {
      return;
    }
    
    const jenis = jabatanSelect.options[jabatanSelect.selectedIndex]?.dataset?.jenis || '';

    // Hanya kosongkan kalau dipanggil dengan clear = true (saat user ganti jabatan)
    if (clear) {
      if (!initiallyLocked.bidang) bidangSelect.value = '';
      if (!initiallyLocked.seksi)  seksiSelect.value  = '';
    }

    if (jenis === 'kepala_dinas' || jenis === 'sekretaris') {
      bidangSelect.disabled = true;
      seksiSelect.disabled  = true;
    }
    else if (jenis === 'kepala_bidang') {
      bidangSelect.disabled = initiallyLocked.bidang;
      seksiSelect.disabled  = true;
    }
    else if (jenis === 'kepala_seksi') {
      bidangSelect.disabled = false;
      seksiSelect.disabled  = false;
      loadSeksi(bidangSelect.value || null);
    } else {
      bidangSelect.disabled = false;
      seksiSelect.disabled  = false;
    }
  }

  // Saat jabatan diubah oleh user → clear bidang & seksi + sync role
  jabatanSelect?.addEventListener('change', () => {
    updateLock({ clear: true });
    syncRoleWithJabatan();
  });

  // Saat bidang diubah → reload list seksi
  bidangSelect?.addEventListener('change', () => loadSeksi(bidangSelect.value, null));

  // Saat pertama kali load halaman → JANGAN clear nilai yg sudah ada
  updateLock();
  syncRoleWithJabatan();

  // NIP hanya angka
  nipInput?.addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
  });
});
</script>

@endsection
