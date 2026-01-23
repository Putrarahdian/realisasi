@extends('layout.weblab')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Target (Rencana)</h3>

        {{-- Hanya selain Kasubag Keuangan --}}
        @if(!$isKasubagKeu)
            <a href="{{ route('target.create') }}" class="btn btn-primary">
                + Tambah Target
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:90px">Tahun</th>
                    <th>Judul</th>
                    <th>Ringkasan Rencana</th>
                    <th style="width:140px">Status</th>
                    <th style="width:220px">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($targets as $t)
                @php
                    $output   = $t->rincian->firstWhere('jenis','output');
                    $outcome  = $t->rincian->firstWhere('jenis','outcome');
                    $sasaran  = $t->rincian->firstWhere('jenis','sasaran');
                    $keuangan = $t->rincian->firstWhere('jenis','keuangan');
                @endphp

                <tr>
                    <td>{{ $t->tahun }}</td>

                    <td>
                        <strong>{{ $t->judul }}</strong>
                        <div class="text-muted small">
                            {{ $t->bidang->nama ?? '-' }} /
                            {{ $t->seksi->nama ?? '-' }}
                        </div>
                    </td>

                    <td>
                        <div><strong>Output:</strong> {{ $output?->target ?? '-' }}</div>
                        <div><strong>Outcome:</strong> {{ $outcome?->target ?? '-' }}</div>
                        <div><strong>Sasaran:</strong> {{ $sasaran?->target ?? '-' }}</div>
                        <div><strong>Keuangan:</strong> {{ $keuangan?->target ?? '-' }}</div>
                    </td>

                    {{-- STATUS --}}
                    <td class="text-center">
                        @if($t->approval_status === 'approved')
                            <span class="badge bg-success">Disetujui</span>
                        @elseif($t->approval_status === 'rejected')
                            <span class="badge bg-danger"
                                  title="{{ $t->rejection_reason }}">
                                Ditolak
                            </span>
                        @else
                            <span class="badge bg-warning text-dark">Pending</span>
                        @endif
                    </td>

                    {{-- AKSI --}}
                    <td>
                        {{-- KASUBAG KEUANGAN --}}
                        @if($isKasubagKeu)

                            @if($t->approval_status === 'pending')
                                <form action="{{ route('target.approve', $t->id) }}"
                                      method="POST"
                                      style="display:inline">
                                    @csrf
                                    <button class="btn btn-success btn-sm"
                                            onclick="return confirm('Setujui target ini?')">
                                        ✔ Setujui
                                    </button>
                                </form>

                                <form action="{{ route('target.reject', $t->id) }}"
                                      method="POST"
                                      style="display:inline">
                                    @csrf
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Tolak target ini?')">
                                        ✖ Tolak
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small">—</span>
                            @endif

                        {{-- SUPERUSER / KEPALA SEKSI --}}
                        @else
                            <a href="{{ route('target.edit', $t->id) }}"
                               class="btn btn-warning btn-sm">
                                Edit
                            </a>

                            <form action="{{ route('target.destroy', $t->id) }}"
                                  method="POST"
                                  style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Hapus target ini?')">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        Belum ada target.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
