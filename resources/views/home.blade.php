@extends('layout.weblab')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="row mb-3 align-items-center">
        <div class="col-md-8">
            <h3 class="fw-bold mb-1">Dashboard Realisasi Kegiatan 
            @if($user->bidang)
                — {{ $user->bidang->nama }}
            @elseif(!empty($selectedBidangId) && !empty($bidangs))
                {{-- kalau superuser / Kadis memilih satu bidang di dropdown --}}
                @php
                    $bidangTerpilih = collect($bidangs)->firstWhere('id', $selectedBidangId);
                @endphp
                @if($bidangTerpilih)
                    — {{ $bidangTerpilih->nama }}
                @else
                    — Semua Bidang
                @endif
            @else
                — Semua Bidang
            @endif
            </h3>
            <p class="text-muted mb-0">
                Selamat datang, <strong>{{ $user->name }}</strong>.
                Monitoring pengisian realisasi per triwulan tahun {{ $tahunDashboard }}.
            </p>
        </div>
        <div class="col-md-4">
            {{-- Filter tahun dashboard --}}
            <form method="GET" action="{{ route('home') }}" class="d-flex justify-content-md-end gap-2 mt-3 mt-md-0">
                {{-- Dropdown bidang: hanya tampil untuk superuser & Kadis --}}
                @if(in_array($user->role, ['superuser']) && !empty($bidangs) && empty($user->bidang_id))
                    <select name="bidang_id" class="form-select form-select-sm">
                        <option value="">Semua Bidang</option>
                        @foreach($bidangs as $bidang)
                            <option value="{{ $bidang->id }}" {{ ($selectedBidangId == $bidang->id) ? 'selected' : '' }}>
                                {{ $bidang->nama }}
                            </option>
                       @endforeach
                    </select>
                @endif
                <select name="tahun_dashboard" class="form-select form-select-sm">
                    @for($t = date('Y')-2; $t <= date('Y')+1; $t++)
                        <option value="{{ $t }}" {{ $t == $tahunDashboard ? 'selected' : '' }}>
                            {{ $t }}
                        </option>
                    @endfor
                </select>
                <button class="btn btn-sm btn-primary">
                    <i class="bi bi-arrow-repeat me-1"></i> Tampilkan
                </button>
            </form>
        </div>
    </div>

    @php
        $mapNo = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4];
    @endphp

    {{-- Ringkasan per Triwulan --}}
    <div class="row g-3 mb-4">
        @foreach($triwulans as $tw)
            @php
                $list = $belumDiisi[$tw] ?? collect();
            @endphp
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Triwulan</span>
                            <span class="badge {{ $list->count() ? 'bg-danger' : 'bg-success' }}">
                                {{ $list->count() ? 'Perlu Diisi' : 'Lengkap' }}
                            </span>
                        </div>
                        <h4 class="fw-bold mb-1">TW {{ $tw }}</h4>
                        <p class="mb-0 text-muted small">
                            {{ $list->count() }} kegiatan belum diisi.
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Detail kegiatan yang belum diisi --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Detail Kegiatan yang Belum Diisi</h5>

            @foreach($triwulans as $tw)
                @php
                    $list = $belumDiisi[$tw] ?? collect();
                    $noTw = $mapNo[$tw];
                @endphp

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Triwulan {{ $tw }}</h6>
                        <span class="badge rounded-pill {{ $list->count() ? 'bg-danger' : 'bg-success' }}">
                            {{ $list->count() }} kegiatan
                        </span>
                    </div>

                    @if($list->isEmpty())
                        <p class="text-success small mb-0">
                            ✅ Tidak ada kegiatan yang pending di Triwulan {{ $tw }}.
                        </p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%">No</th>
                                        <th>Program</th>
                                        <th>Indikator</th>
                                        <th>Seksi</th>
                                        @if(in_array($user->role, ['superuser','user']))
                                            <th style="width: 10%">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($list as $index => $induk)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $induk->program }}</td>
                                            <td>{{ $induk->indikator }}</td>
                                            <td>{{ optional($induk->seksi)->nama ?? '-' }}</td>
                                            @if(in_array($user->role, ['superuser','user']))
                                                <td>
                                                    <a href="{{ route('realisasi.triwulan.create', ['no' => $noTw, 'induk' => $induk->id]) }}"
                                                       class="btn btn-sm btn-primary">
                                                        Isi
                                                    </a>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                @if(!$loop->last)
                    <hr>
                @endif
            @endforeach
        </div>
    </div>

</div>
@endsection
