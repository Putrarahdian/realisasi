@extends('layout.weblab')
<title>DATA KEGIATAN</title>
@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">

            <h4 class="fw-bold mb-4 text-center text-dark">
                📄 Laporan Kegiatan 
                @if(auth()->user()->role === 'superuser')
                    @if(request('bidang_id'))
                        <span class="text">
                            — <strong>{{ \App\Models\Bidang::find(request('bidang_id'))->nama ?? 'Semua Bidang' }}</strong>
                        </span>
                    @else
                        <span class="text">— Semua Bidang</span>
                    @endif
                @else
                    <span class="text">
                        —  <strong>{{ auth()->user()->bidang->nama ?? 'Tidak Diketahui' }}</strong>
                    </span>
                @endif
            </h4>

            {{-- 🔹 Baris 1: Tombol Tambah Data --}}
            <div class="d-flex justify-content-start mb-3">
                <a href="{{ route('realisasi-induk.create') }}" class="btn btn-success px-4 shadow-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Data
                </a>
            </div>

            {{-- 🔹 Baris 2: Filter + Search --}}
            <form method="GET" action="{{ route('realisasi.index') }}"
                  class="d-flex align-items-center flex-wrap gap-2 mb-4 w-100">

                {{-- 🔸 Hanya superuser yang punya filter Bidang & Seksi --}}
                @if(auth()->user()->role === 'superuser')
                    <select name="bidang_id"
                            class="form-select shadow-sm border-0 fw-semibold"
                            style="width: 260px; cursor:pointer; text-align:center;"
                            onchange="this.form.submit()">
                        <option value="">Semua Bidang</option>
                        @foreach($bidangs as $b)
                            <option value="{{ $b->id }}"
                                {{ request('bidang_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->nama }}
                            </option>
                        @endforeach
                    </select>

                    <select name="seksi_id"
                            class="form-select shadow-sm border-0 fw-semibold"
                            style="width: 220px; cursor:pointer; text-align:center;"
                            onchange="this.form.submit()">
                        <option value="">Semua Seksi</option>
                        @foreach($seksis as $s)
                            <option value="{{ $s->id }}"
                                {{ request('seksi_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->nama }}
                            </option>
                        @endforeach
                    </select>
                @endif

                {{-- Dropdown Tahun --}}
                <select name="tahun"
                        class="form-select shadow-sm border-0 fw-semibold"
                        style="width: 120px; cursor:pointer;"
                        onchange="this.form.submit()">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>

                {{-- 🔸 Bagian kanan: Search + Reset --}}
                <div class="ms-auto d-flex align-items-center gap-2">
                    <div class="input-group shadow-sm border-0" style="width: 220px;">
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control form-control-sm border-0"
                               placeholder="Cari data...">
                        <button class="btn btn-primary btn-sm" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>

                    @if(request('search'))
                        <a href="{{ route('realisasi.index') }}"
                           class="btn btn-secondary btn-sm shadow-sm">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            {{-- 🔹 Tabel Data --}}
            <div class="table-responsive rounded-3 shadow-sm">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Tahun</th>
                            <th>Seksi</th>
                            <th>Sasaran Strategis</th>
                            <th>Program/Kegiatan/Sub Kegiatan</th>
                            <th>Indikator Prog/Keg/Sub Keg</th>
                            <th>Target</th>
                            <th>Hambatan/Keberhasilan</th>
                            <th>Rekomendasi</th>
                            <th>TL Rekomendasi Sebelumnya</th>
                            <th>Nama Dokumen/Data Kinerja</th>
                            <th>Strategi Triwulan Berikutnya</th>
                            <th>Alasan Tidak Tercapai</th>
                            <th style="width: 150px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data_induk as $i => $row)
                            <tr>
                                <td class="text-center fw-semibold">
                                    {{ $data_induk->firstItem() + $i }}
                                </td>
                                <td class="text-center text-primary fw-semibold">{{ $row->tahun }}</td>
                                <td>{{ $row->seksi->nama ?? '-' }}</td>
                                <td>{{ $row->sasaran_strategis }}</td>
                                <td>{{ $row->program }}</td>
                                <td>{{ $row->indikator }}</td>
                                <td>{{ $row->target }}</td>
                                <td>{{ $row->hambatan }}</td>
                                <td>{{ $row->rekomendasi }}</td>
                                <td>{{ $row->tindak_lanjut }}</td>
                                <td>{{ $row->dokumen }}</td>
                                <td>{{ $row->strategi }}</td>
                                <td>{{ $row->alasan }}</td>
                                <td class="text-center">
                                    <a href="{{ route('realisasi.show', $row->id) }}"
                                       class="btn btn-sm btn-primary mb-1 shadow-sm">
                                        Detail
                                    </a>
                                    <a href="{{ route('realisasi-induk.edit', $row->id) }}"
                                       class="btn btn-sm btn-warning mb-1 shadow-sm text-white">
                                        Edit
                                    </a>
                                    <form action="{{ route('realisasi-induk.destroy', $row->id) }}"
                                          method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-danger shadow-sm btn-delete">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="text-center text-muted py-3">
                                    Belum ada data induk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 🔹 Pagination --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $data_induk->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>
@endsection
