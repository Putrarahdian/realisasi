@extends('layout.weblab')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">

            <h4 class="fw-bold mb-4 text-center text-dark">
                 Laporan Kegiatan — Triwulan {{ $tw }}
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
                        — <strong>{{ auth()->user()->bidang->nama ?? 'Tidak Diketahui' }}</strong>
                    </span>
                @endif
            </h4>

            {{-- Baris atas: Tambah Data + Filter --}}
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">

                {{-- Tombol tambah data (data induk baru) --}}
                <a href="{{ route('realisasi-induk.create') }}"
                   class="btn btn-success px-4 shadow-sm">
                    <i class="bi bi-plus-circle"></i> Tambah Data Kegiatan
                </a>

                {{-- Filter tahun + (bidang untuk superuser) --}}
                <form method="GET"
                      action="{{ route('realisasi.triwulan.index', $no) }}"
                      class="d-flex align-items-center flex-wrap gap-2">

                    @if(auth()->user()->role === 'superuser')
                        <select name="bidang_id"
                                class="form-select shadow-sm border-0 fw-semibold"
                                style="width:260px; cursor:pointer; text-align:center;"
                                onchange="this.form.submit()">
                            <option value="">Semua Bidang</option>
                            @foreach($bidangs as $b)
                                <option value="{{ $b->id }}"
                                    {{ request('bidang_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->nama }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <select name="tahun"
                            class="form-select shadow-sm border-0 fw-semibold"
                            style="width:120px; cursor:pointer;"
                            onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </form>
            </div>

            {{-- Tabel data per triwulan --}}
            <div class="table-responsive rounded-3 shadow-sm">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th>No</th>
                            <th>Sasaran Strategis</th>
                            <th>Program/Kegiatan</th>
                            <th>Indikator</th>

                            <th colspan="3">Output (Triwulan {{ $tw }})</th>
                            <th colspan="3">Outcome (Triwulan {{ $tw }})</th>
                            <th colspan="3">Keuangan (Triwulan {{ $tw }})</th>

                            <th style="width:160px;">Aksi</th>
                        </tr>
                        <tr class="text-center small">
                            <th></th><th></th><th></th><th></th>
                            <th>Target</th><th>Realisasi</th><th>Capaian</th>
                            <th>Target</th><th>Realisasi</th><th>Capaian</th>
                            <th>Target</th><th>Realisasi</th><th>Capaian</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data_induk as $i => $row)
                            @php
                                $output  = $row->outputs->first();
                                $outcome = $row->outcomes->first();
                                $keu     = $row->keuangans->first();

                                $hasTriwulanData =
                                    ($output  && ($output->target  !== null || $output->realisasi  !== null)) ||
                                    ($outcome && ($outcome->target !== null || $outcome->realisasi !== null)) ||
                                    ($keu     && ($keu->target     !== null || $keu->realisasi     !== null));
                            @endphp
                            <tr>
                                <td class="text-center">
                                    {{ $data_induk->firstItem() + $i }}
                                </td>
                                <td>{{ $row->sasaran_strategis }}</td>
                                <td>{{ $row->program }}</td>
                                <td>{{ $row->indikator }}</td>

                                {{-- Output --}}
                                <td class="text-end">{{ $output->target ?? '-' }}</td>
                                <td class="text-end">{{ $output->realisasi ?? '-' }}</td>
                                <td class="text-end">
                                    {{ isset($output->capaian) ? $output->capaian.'%' : '-' }}
                                </td>

                                {{-- Outcome --}}
                                <td class="text-end">{{ $outcome->target ?? '-' }}</td>
                                <td class="text-end">{{ $outcome->realisasi ?? '-' }}</td>
                                <td class="text-end">
                                    {{ isset($outcome->capaian) ? $outcome->capaian.'%' : '-' }}
                                </td>

                                {{-- Keuangan --}}
                                <td class="text-end">{{ $keu->target ?? '-' }}</td>
                                <td class="text-end">{{ $keu->realisasi ?? '-' }}</td>
                                <td class="text-end">
                                    {{ isset($keu->capaian) ? $keu->capaian.'%' : '-' }}
                                </td>

                                <td class="text-center">
                                    <a href="{{ route('realisasi.show', $row->id) }}"
                                       class="btn btn-sm btn-primary mb-1">
                                        Detail
                                    </a>
                                    @if (!$hasTriwulanData) 
                                    <a href="{{ route('realisasi.triwulan.create', ['no' => $no, 'induk' => $row->id]) }}"
                                       class="btn btn-sm btn-danger text-white mb-1">
                                        Tambah Data
                                    </a>
                                    @else <button class="btn btn-sm btn-secondary mb-1" disabled> 
                                        Sudah diisi
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center text-muted py-3">
                                    Belum ada data untuk Triwulan {{ $tw }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $data_induk->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
