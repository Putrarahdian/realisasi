@extends('layout.weblab')
<title>DATA KEGIATAN</title>

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">

            {{-- ================= HEADER ================= --}}
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">
                        📄 Laporan Kegiatan
                    </h4>

                    {{-- Subjudul Bidang/Seksi --}}
                    <div class="text-muted">
                        @if(auth()->user()->role === 'superuser')
                            <span class="me-2">Bidang:</span>
                            <span class="badge bg-light text-dark border">
                                {{ request('bidang_id')
                                    ? (optional(\App\Models\Bidang::find(request('bidang_id')))->nama ?? 'Semua Bidang')
                                    : 'Semua Bidang'
                                }}
                            </span>

                            <span class="ms-3 me-2">Seksi:</span>
                            <span class="badge bg-light text-dark border">
                                {{ request('seksi_id')
                                    ? (optional(\App\Models\Seksi::find(request('seksi_id')))->nama ?? 'Semua Seksi')
                                    : 'Semua Seksi'
                                }}
                            </span>
                        @else
                            <span class="me-2">Bidang:</span>
                            <span class="badge bg-light text-dark border">
                                {{ auth()->user()->bidang->nama ?? 'Tidak Diketahui' }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Aksi kanan --}}
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('realisasi-induk.create') }}" class="btn btn-success shadow-sm">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Data
                    </a>

                    @if(request('search') || request('tanggal_dari') || request('tanggal_sampai') || request('bidang_id') || request('seksi_id'))
                        <a href="{{ route('realisasi.index') }}" class="btn btn-secondary shadow-sm">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </div>

            {{-- ================= FILTER BAR ================= --}}
            <form method="GET" action="{{ route('realisasi.index') }}" class="mb-4">
                <div class="p-3 rounded-3 mb-4">

                    {{-- ROW 1: Bidang, Seksi, Range Tanggal --}}
                    <div class="row g-3 align-items-end mb-3">

                        @if(auth()->user()->role === 'superuser')
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold mb-1">Bidang</label>
                                <select name="bidang_id" class="form-select shadow-sm" onchange="this.form.submit()">
                                    <option value="">Semua Bidang</option>
                                    @foreach($bidangs as $b)
                                        <option value="{{ $b->id }}" {{ request('bidang_id') == $b->id ? 'selected' : '' }}>
                                            {{ $b->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold mb-1">Seksi</label>
                                <select name="seksi_id" class="form-select shadow-sm" onchange="this.form.submit()">
                                    <option value="">Semua Seksi</option>
                                    @foreach($seksis as $s)
                                        <option value="{{ $s->id }}" {{ request('seksi_id') == $s->id ? 'selected' : '' }}>
                                            {{ $s->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold mb-1">Range Tanggal</label>
                            <div class="d-flex gap-2">
                                <input type="date"
                                    name="tanggal_dari"
                                    value="{{ request('tanggal_dari') }}"
                                    class="form-control shadow-sm"
                                    style="height:40px;">

                                <input type="date"
                                    name="tanggal_sampai"
                                    value="{{ request('tanggal_sampai') }}"
                                    class="form-control shadow-sm"
                                    style="height:40px;">
                            </div>
                        </div>
                    </div>

                    {{-- ROW 2: Search + Terapkan (sejajar) --}}
                    <div class="row g-3 align-items-end">

                        {{-- Search (panjang) --}}
                        <div class="col-12 col-md-10">
                            <label class="form-label fw-semibold mb-1">Pencarian</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text"
                                    name="search"
                                    value="{{ request('search') }}"
                                    class="form-control border-0"
                                    placeholder="Cari data..."
                                    style="height:40px;">
                            </div>
                        </div>

                        {{-- Tombol Terapkan --}}
                        <div class="col-12 col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary shadow-sm" style="height:40px;">
                                <i class="bi bi-funnel me-1"></i> Terapkan
                            </button>
                        </div>
                    </div>

                </div>
            </form>

            {{-- ================= TABLE ================= --}}
            <div class="table-responsive rounded-4 border shadow-sm">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-center align-middle">
                    <tr>
                        <th style="width:60px;">No</th>
                        <th style="width:120px;">Tanggal</th>
                        <th style="width:180px;">Seksi</th>
                        <th style="min-width:320px;">Judul (Target)</th>
                        <th style="min-width:320px;">Output</th>
                        <th style="min-width:320px;">Outcome</th>
                        <th style="min-width:320px;">Sasaran</th>
                        <th style="width:170px;">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                        @forelse ($data_induk as $i => $row)
                            <tr>
                            <td class="text-center fw-semibold">
                                {{ $data_induk->firstItem() + $i }}
                            </td>

                            <td class="text-center fw-semibold text-primary">
                                {{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') : '-' }}
                            </td>

                            <td>
                                <span class="fw-semibold">{{ $row->seksi->nama ?? '-' }}</span>
                            </td>

                            {{-- Judul utama dari Target --}}
                            <td title="{{ optional($row->targetHeader)->judul }}" class="text-truncate" style="max-width: 360px;">
                                {{ optional($row->targetHeader)->judul ?? '-' }}
                            </td>

                            {{-- 3 subjudul baru dari realisasi_induks --}}
                            <td title="{{ $row->output }}" class="text-truncate" style="max-width: 360px;">
                                {{ $row->output ?? '-' }}
                            </td>

                            <td title="{{ $row->outcome }}" class="text-truncate" style="max-width: 360px;">
                                {{ $row->outcome ?? '-' }}
                            </td>

                            <td title="{{ $row->sasaran }}" class="text-truncate" style="max-width: 360px;">
                                {{ $row->sasaran ?? '-' }}
                            </td>

                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">

                                {{-- OPSI 1 (sementara): detail dimatikan dulu biar gak error --}}
                                {{-- <a href="#" class="btn btn-primary disabled">Detail</a> --}}

                                {{-- OPSI 2 (kalau kamu mau bikin detail induk): aktifkan setelah route show ada --}}
                                <a href="{{ route('realisasi.show', $row->id) }}" class="btn btn-primary">Detail</a>

                                <a href="{{ route('realisasi-induk.edit', $row->id) }}" class="btn btn-warning text-white">
                                    Edit
                                </a>

                                <button type="submit" form="delete-form-{{ $row->id }}" class="btn btn-danger">
                                    Hapus
                                </button>
                                </div>

                                <form id="delete-form-{{ $row->id }}"
                                    action="{{ route('realisasi-induk.destroy', $row->id) }}"
                                    method="POST"
                                    class="d-none">
                                @csrf
                                @method('DELETE')
                                </form>
                            </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center text-muted py-4">
                                    <div class="fw-semibold">Belum ada data induk.</div>
                                    <div class="small">Silakan klik <b>Tambah Data</b> untuk mulai input.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ================= PAGINATION ================= --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $data_induk->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>
@endsection
