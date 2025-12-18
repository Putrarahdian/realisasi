@extends('layout.weblab')

@section('content')
@php
    $user = auth()->user();
    $role = $user->role;
    $isKadis = $role === 'admin' && empty($user->bidang_id);
    $isKabid = $role === 'admin' && !empty($user->bidang_id);
@endphp

<div class="container py-4">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">

            {{-- 🔹 Judul Halaman --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                <div>
                    <h4 class="fw-bold mb-1 text-dark">
                        📊 Rekap Laporan Kegiatan
                    </h4>
                    <div class="small text-muted">
                        Tahun:
                        <span class="fw-semibold text-primary">{{ $tahun }}</span>
                        —

                    Bidang:
                    <span class="fw-semibold">
                        @if ($role === 'superuser' || $isKadis)
                            @php
                                $bidangTerpilih = collect($bidangs ?? [])->firstWhere('id', request('bidang_id'));
                            @endphp
                            {{ $bidangTerpilih->nama ?? 'Semua Bidang' }}
                        @else 
                            {{ optional($user->bidang)->nama ?? '-' }}
                        @endif
                    </span>

                    —

                    Seksi:
                    <span class="fw-semibold">
                        @if ($role === 'superuser' || $isKadis)
                            @php
                                $seksiTerpilih = collect($seksis ?? [])->firstWhere('id', request('seksi_id'));
                            @endphp
                            {{ $seksiTerpilih->nama ?? 'Semua Seksi' }}
                        @elseif($isKabid)
                            @php
                                $seksiTerpilih = collect($seksis ?? [])->firstWhere('id', request('seksi_id'));
                            @endphp
                            {{ $seksiTerpilih->nama ?? 'Semua Seksi' }}
                        @else
                            {{ optional($user->seksi)->nama ?? '-' }}
                        @endif
                    </span>

                    </div>
                </div>

                {{-- Keterangan kecil total data --}}
                <div class="text-md-end mt-2 mt-md-0">
                    <div class="small text-muted">
                        Total data: <span class="fw-semibold">{{ $data_induk->total() }}</span>
                    </div>
                </div>
            </div>

            {{-- 🔹 Baris Atas: Tombol Export + Filter --}}
            <div class="row align-items-center mb-4 g-2">

                {{-- Kiri: tombol download --}}
                <div class="col-12 col-md-4">
                    <div class="d-flex gap-2">
                        <a href="{{ route('realisasi.rekap.excel', ['tahun' => $tahun, 'bidang_id' => request('bidang_id')]) }}"
                        class="btn btn-success w-100 w-md-auto">
                            <i class="bi bi-file-earmark-excel"></i> Download Excel
                        </a>
                    </div>
                </div>

                {{-- Kanan: filter bidang + seksi + tahun --}}
                <div class="col-12 col-md-8">
                    <form method="GET"
                        action="{{ route('realisasi.rekap') }}"
                        class="row g-2 justify-content-md-end">

                        {{-- SUPERUSER & KADIS: bisa pilih BIDANG & SEKSI --}}
                        @if($role === 'superuser' || $isKadis)
                            <div class="col-12 col-md-4">
                                <select name="bidang_id"
                                        class="form-select form-select-sm shadow-sm"
                                        onchange="this.form.submit()">
                                    <option value="">Semua Bidang</option>
                                    @foreach($bidangs as $b)
                                        <option value="{{ $b->id }}"
                                                {{ request('bidang_id') == $b->id ? 'selected' : '' }}>
                                            {{ $b->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <select name="seksi_id"
                                        class="form-select form-select-sm shadow-sm"
                                        onchange="this.form.submit()">
                                    <option value="">Semua Seksi</option>
                                    @foreach($seksis as $s)
                                        <option value="{{ $s->id }}"
                                                {{ request('seksi_id') == $s->id ? 'selected' : '' }}>
                                            {{ $s->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        {{-- KABID: bidang dikunci, hanya bisa pilih SEKSI --}}
                        @elseif($isKabid)
                            <div class="col-12 col-md-4">
                                <select class="form-select form-select-sm shadow-sm" disabled>
                                    <option>
                                        {{ optional($user->bidang)->nama ?? 'Bidang Tidak Diketahui' }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <select name="seksi_id"
                                        class="form-select form-select-sm shadow-sm"
                                        onchange="this.form.submit()">
                                    <option value="">Semua Seksi</option>
                                    @foreach($seksis as $s)
                                        <option value="{{ $s->id }}"
                                                {{ request('seksi_id') == $s->id ? 'selected' : '' }}>
                                            {{ $s->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- TAHUN (semua role punya) --}}
                        <div class="col-12 col-md-3 col-lg-2">
                            <select name="tahun"
                                    class="form-select form-select-sm shadow-sm"
                                    onchange="this.form.submit()">
                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </form>
                </div>
            </div>


            {{-- 🔹 Tabel Rekap (lebih ringkas) --}}
            <div class="table-responsive rounded-3 shadow-sm">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Tahun</th>
                            <th>Bidang</th>
                            <th>Seksi</th>
                            <th>Sasaran Strategis</th>
                            <th>Program / Kegiatan</th>
                            <th>Indikator</th>
                            <th>Hambatan / Keberhasilan</th>
                            <th>Rekomendasi</th>
                            <th style="width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data_induk as $i => $row)
                            <tr>
                                <td class="text-center fw-semibold">
                                    {{ $data_induk->firstItem() + $i }}
                                </td>
                                <td class="text-center fw-semibold text-primary">
                                    {{ $row->tahun }}
                                </td>
                                <td>
                                    {{ optional($row->bidang)->nama ?? '-' }}
                                </td>
                                <td>
                                    {{ optional($row->seksi)->nama ?? '-' }}
                                </td>
                                <td>{{ Str::limit($row->sasaran_strategis, 60) }}</td>
                                <td>{{ Str::limit($row->program, 60) }}</td>
                                <td>{{ Str::limit($row->indikator, 60) }}</td>
                                <td>{{ Str::limit($row->hambatan, 60) }}</td>
                                <td>{{ Str::limit($row->rekomendasi, 60) }}</td>
                                <td class="text-center">
                                    {{-- Detail: ke halaman laporan realisasi kegiatan (show) --}}
                                    <a href="{{ route('realisasi.rekap.anak', $row->id) }}"
                                       class="btn btn-sm btn-primary shadow-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-3">
                                    Belum ada data yang dapat direkap.
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
