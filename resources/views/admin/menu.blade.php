@extends('layout.weblab')

@section('content')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const deleteButtons = document.querySelectorAll('.btn-delete');

  deleteButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      const form = this.closest('form');

      Swal.fire({
        title: 'Hapus Data?',
        text: 'Data ini akan dihapus secara permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        background: '#1e1e2f',
        color: '#fff',
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
});
</script>

<div class="container mt-4">

  {{-- 🔹 Header atas --}}
  <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
    <h3 class="fw-bold text-primary mb-2">Daftar Users</h3>
  </div>

  <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
    {{-- 🔍 Search Bar --}}
    <form action="{{ route('admin.menu') }}" method="GET" class="d-flex align-items-center gap-2 flex-grow-1 me-3">
      <div class="input-group w-100" style="max-width: 400px;">
        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control shadow-sm"
               placeholder="Cari...">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search"></i> Cari
        </button>

        {{-- Tombol Reset --}}
        @if(!empty($search))
          <a href="{{ route('admin.menu') }}" class="btn btn-secondary ms-2">
            <i class="bi bi-x-circle"></i> Reset
          </a>
        @endif
      </div>
    </form>

    {{-- Tombol Tambah (hanya superuser) --}}
    @if(auth()->user()->role === 'superuser')
    <a href="{{ route('admin.users.export.excel', ['search' => $search ?? '']) }}" class="btn btn-success fw-semibold shadow-sm">
      <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel
    </a>

      <a href="{{ route('user.create') }}" class="btn btn-success fw-semibold shadow-sm">
        <i class="bi bi-person-plus-fill me-1"></i> Tambahkan Pengguna Baru
      </a>
    @endif
  </div>

  {{-- 🔹 Tabel --}}
  <div class="card shadow-sm border-0">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle mb-0 text-center">
          <thead class="table-primary text-nowrap">
            <tr>
              <th style="width: 5%">No</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Role</th>
              <th>NIP</th>
              <th>Jabatan</th>
              <th>Bidang</th>
              <th>Seksi</th>
              <th style="width: 18%">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($users as $u)
              @php
                $jabatanNama      = optional($u->jabatan)->nama;
                // 4 akun tetap: Kepala Dinas + Kepala Bidang (is_locked bisa juga kamu pakai)
                $isLockedJabatan  = in_array($jabatanNama, ['Kepala Dinas', 'Kepala Bidang']);
                // superuser admin paling atas
                $isMasterSuperuser = ($u->id === 1 && $u->role === 'superuser');
              @endphp
              <tr>
                {{-- nomor tetap berurutan meski ganti halaman --}}
                <td>{{ ($users->currentPage() - 1) * $users->perPage() + $loop->iteration }}</td>
                <td class="text-start fw-semibold">{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>
                  @if($u->role === 'superuser')
                    <span class="badge bg-danger px-3 py-2">Superuser</span>
                  @elseif($u->role === 'admin')
                    <span class="badge bg-primary px-3 py-2">Admin</span>
                  @else
                    <span class="badge bg-secondary px-3 py-2">User</span>
                  @endif
                </td>
                <td>{{ $u->nip ?? '-' }}</td>
                <td>{{ $jabatanNama ?? '-' }}</td>
                <td>{{ $u->bidang->nama ?? '-' }}</td>
                <td>{{ $u->seksi->nama ?? '-' }}</td>
                <td>
                  <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-warning btn-sm fw-semibold shadow-sm">
                      <i class="bi bi-pencil-square"></i> Edit
                    </a>

                    {{-- Tombol hapus: hanya superuser, tidak untuk master superuser & 4 admin tetap --}}
                    @if(auth()->user()->role === 'superuser' && !$isLockedJabatan && !$isMasterSuperuser)
                      <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm fw-semibold shadow-sm btn-delete">
                          <i class="bi bi-trash"></i> Hapus
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-muted py-3">Belum ada pengguna terdaftar.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="pagination-container p-3">
        <div class="row align-items-center w-100">
          {{-- Tengah: pagination --}}
          <div class="col-md-4 d-flex justify-content-center">
            {{ $users->links('pagination::bootstrap-5') }}
          </div>
          {{-- Kanan: biarkan kosong (untuk keseimbangan) --}}
          <div class="col-md-4"></div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection
