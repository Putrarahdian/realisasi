@extends('layout.weblab')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Target (Rencana)</h3>
        <a href="{{ route('target.create') }}" class="btn btn-primary">+ Tambah Target</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th style="width:90px">Tahun</th>
                    <th>Judul</th>
                    <th>Ringkasan Rencana</th>
                    <th style="width:190px">Aksi</th>
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
                        <div class="text-muted" style="font-size:12px;">
                            (Bidang ID: {{ $t->bidang_id }}, Seksi ID: {{ $t->seksi_id }})
                        </div>
                    </td>
                    <td>
                        <div><strong>Output</strong>: {{ $output?->target ?? '-' }}</div>
                        <div><strong>Outcome</strong>: {{ $outcome?->target ?? '-' }}</div>
                        <div><strong>Sasaran</strong>: {{ $sasaran?->target ?? '-' }}</div>
                        <div><strong>Keuangan</strong>: {{ $keuangan?->target ?? '-' }}</div>
                    </td>
                    <td>
                        <a href="{{ route('target.edit', $t->id) }}" class="btn btn-warning btn-sm">Edit</a>

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
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada target.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
