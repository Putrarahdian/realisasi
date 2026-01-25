@extends('layout.weblab')

@section('content')
<div class="container py-4">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-4">

      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <div>
          <h4 class="fw-bold mb-1">Keuangan</h4>
          <div class="text-muted small">
            Ringkasan uang masuk & keluar (tahun {{ $tahun }})
          </div>
        </div>

        {{-- Filter Tahun --}}
        <form method="GET" action="{{ route('keuangan.index') }}" class="d-flex gap-2">
          <select name="tahun" class="form-select" style="max-width: 140px" onchange="this.form.submit()">
            @for($y = date('Y'); $y >= date('Y')-5; $y--)
              <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
          </select>

          {{-- kalau lagi fokus masuk/keluar, param jenis ikut kebawa --}}
          @if(!empty($jenis))
            <input type="hidden" name="jenis" value="{{ $jenis }}">
          @endif
        </form>
      </div>

      {{-- Ringkasan --}}
      <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
          <div class="p-3 rounded-4 border bg-light">
            <div class="text-muted small">Total Masuk</div>
            <div class="fw-bold fs-5">
              Rp {{ number_format($totalMasuk ?? 0, 0, ',', '.') }}
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="p-3 rounded-4 border bg-light">
            <div class="text-muted small">Total Keluar</div>
            <div class="fw-bold fs-5">
              Rp {{ number_format($totalKeluar ?? 0, 0, ',', '.') }}
            </div>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="p-3 rounded-4 border bg-light">
            <div class="text-muted small">Saldo</div>
            <div class="fw-bold fs-5">
              Rp {{ number_format($saldo ?? 0, 0, ',', '.') }}
            </div>
          </div>
        </div>
      </div>

      {{-- Form Uang Masuk (khusus Kepala Dinas / Superuser) --}}
      @php
        $isSuperuser = (auth()->user()->role ?? null) === 'superuser';
      @endphp

      @if(($isSuperuser || (!empty($isKepalaDinas) && $isKepalaDinas)) && ($jenis !== 'keluar'))
        <div class="mb-4">
          <div class="p-3 rounded-4 border">
            <div class="fw-bold mb-2">Tambah Uang Masuk</div>

            @if(session('success'))
              <div class="alert alert-success py-2">{{ session('success') }}</div>
            @endif

            @if($errors->any())
              <div class="alert alert-danger py-2">
                <ul class="mb-0">
                  @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form action="{{ route('keuangan.masuk.store') }}" method="POST" class="row g-2">
              @csrf
              <div class="col-12 col-md-3">
                <input type="date" name="tanggal" class="form-control" required value="{{ old('tanggal') }}">
              </div>
              <div class="col-12 col-md-3">
                <input type="number" name="jumlah" class="form-control" required min="0" step="0.01" placeholder="Jumlah" value="{{ old('jumlah') }}">
              </div>
              <div class="col-12 col-md-4">
                <input type="text" name="keterangan" class="form-control" placeholder="Keterangan (opsional)" value="{{ old('keterangan') }}">
              </div>
              <div class="col-12 col-md-2 d-grid">
                <button class="btn btn-success">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      @endif

      {{-- ===================== UANG MASUK ===================== --}}
      @if($jenis !== 'keluar')
      <div id="section-masuk" class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="fw-bold mb-0">Uang Masuk ({{ $tahun }})</h5>

          @if(empty($jenis))
            <a href="{{ route('keuangan.index', ['tahun' => $tahun, 'jenis' => 'masuk']) }}"
              class="btn btn-sm btn-outline-secondary">
              Fokus Masuk
            </a>
          @endif
        </div>

        <div class="table-responsive rounded-4 border">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="text-center">
                <th style="width:160px;">Tanggal</th>
                <th>Keterangan</th>
                <th style="width:200px;">Jumlah</th>
              </tr>
            </thead>
            <tbody>
              @forelse($masuk as $row)
                <tr>
                  <td class="text-center">
                    {{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') : '-' }}
                  </td>
                  <td>{{ $row->keterangan ?? '-' }}</td>
                  <td class="text-end fw-semibold">
                    Rp {{ number_format($row->jumlah ?? 0, 0, ',', '.') }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted py-3">Belum ada uang masuk.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
          {{ $masuk->appends(request()->except('masuk_page'))->links('pagination::bootstrap-5') }}
        </div>
      </div>
      @endif

      {{-- ===================== UANG KELUAR ===================== --}}
      @if($jenis !== 'masuk')
      <div id="section-keluar">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 class="fw-bold mb-0">Uang Keluar ({{ $tahun }})</h5>

          @if(empty($jenis))
            <a href="{{ route('keuangan.index', ['tahun' => $tahun, 'jenis' => 'keluar']) }}"
              class="btn btn-sm btn-outline-secondary">
              Fokus Keluar
            </a>
          @endif
        </div>

        <div class="table-responsive rounded-4 border">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="text-center">
                <th style="width:160px;">Tanggal</th>
                <th>Keterangan</th>
                <th style="width:200px;">Jumlah</th>
              </tr>
            </thead>
            <tbody>
              @forelse($keluar as $row)
                <tr>
                  <td class="text-center">
                    {{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') : '-' }}
                  </td>
                  <td>{{ $row->keterangan ?? '-' }}</td>
                  <td class="text-end fw-semibold">
                    Rp {{ number_format($row->jumlah ?? 0, 0, ',', '.') }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted py-3">Belum ada uang keluar.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
          {{ $keluar->appends(request()->except('keluar_page'))->links('pagination::bootstrap-5') }}
        </div>
      </div>
      @endif

    </div>
  </div>
</div>
@endsection
