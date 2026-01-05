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
                <div class="p-3 rounded-3 p-3 mb-4">
                    <div class="row g-3 align-items-end">

                        {{-- Bidang & Seksi (Superuser) --}}
                        @if(auth()->user()->role === 'superuser')
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold mb-1">Bidang</label>
                                <select name="bidang_id"
                                        class="form-select shadow-sm"
                                        onchange="this.form.submit()">
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
                                <select name="seksi_id"
                                        class="form-select shadow-sm"
                                        onchange="this.form.submit()">
                                    <option value="">Semua Seksi</option>
                                    @foreach($seksis as $s)
                                        <option value="{{ $s->id }}" {{ request('seksi_id') == $s->id ? 'selected' : '' }}>
                                            {{ $s->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Tanggal dari - sampai --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold mb-1">Range Tanggal</label>

                            {{-- input sejajar --}}
                            <div class="d-flex gap-2 align-items-end">
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

                        {{-- Search --}}
                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold mb-1">Pencarian</label>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-0">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text"
                                       name="search"
                                       value="{{ request('search') }}"
                                       class="form-control border-0"
                                       placeholder="Cari data...">
                            </div>
                        </div>

                        {{-- Tombol Terapkan --}}
                        <div class="col-12 col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary shadow-sm">
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
                            <th style="min-width:240px;">Sasaran Strategis</th>
                            <th style="min-width:260px;">Program/Kegiatan/Sub Kegiatan</th>
                            <th style="min-width:260px;">Indikator Prog/Keg/Sub Keg</th>
                            <th style="min-width:180px;">Target</th>
                            <th style="min-width:220px;">Hambatan/Keberhasilan</th>
                            <th style="min-width:200px;">Rekomendasi</th>
                            <th style="min-width:240px;">TL Rekomendasi Sebelumnya</th>
                            <th style="min-width:220px;">Nama Dokumen/Data Kinerja</th>
                            <th style="min-width:240px;">Strategi Triwulan Berikutnya</th>
                            <th style="min-width:220px;">Alasan Tidak Tercapai</th>
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

                                {{-- Helper: truncate + tooltip --}}
                                <td title="{{ $row->sasaran_strategis }}" class="text-truncate" style="max-width: 260px;">
                                    {{ $row->sasaran_strategis }}
                                </td>

                                <td title="{{ $row->program }}" class="text-truncate" style="max-width: 280px;">
                                    {{ $row->program }}
                                </td>

                                <td title="{{ $row->indikator }}" class="text-truncate" style="max-width: 280px;">
                                    {{ $row->indikator }}
                                </td>

                                <td title="{{ $row->target }}" class="text-truncate" style="max-width: 220px;">
                                    {{ $row->target }}
                                </td>

                                <td title="{{ $row->hambatan }}" class="text-truncate" style="max-width: 260px;">
                                    {{ $row->hambatan }}
                                </td>

                                <td title="{{ $row->rekomendasi }}" class="text-truncate" style="max-width: 240px;">
                                    {{ $row->rekomendasi }}
                                </td>

                                <td title="{{ $row->tindak_lanjut }}" class="text-truncate" style="max-width: 280px;">
                                    {{ $row->tindak_lanjut }}
                                </td>

                                <td title="{{ $row->dokumen }}" class="text-truncate" style="max-width: 260px;">
                                    {{ $row->dokumen }}
                                </td>

                                <td title="{{ $row->strategi }}" class="text-truncate" style="max-width: 280px;">
                                    {{ $row->strategi }}
                                </td>

                                <td title="{{ $row->alasan }}" class="text-truncate" style="max-width: 260px;">
                                    {{ $row->alasan }}
                                </td>

                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('realisasi.show', $row->id) }}"
                                           class="btn btn-sm btn-primary">
                                            Detail
                                        </a>

                                        <a href="{{ route('realisasi-induk.edit', $row->id) }}"
                                           class="btn btn-sm btn-warning text-white">
                                            Edit
                                        </a>

                                        <form action="{{ route('realisasi-induk.destroy', $row->id) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-danger btn-delete">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
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
