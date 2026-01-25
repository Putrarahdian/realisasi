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

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
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
                    @php
                        $isSuperuser = (auth()->user()->role ?? null) === 'superuser';
                        $canApprove  = $isKasubagKeu || $isSuperuser; // kasubag keu ATAU superuser bisa approve
                        $isPending   = $t->approval_status === 'pending';
                        $isLocked    = in_array($t->approval_status, ['approved','rejected']); // ✅ KUNCI EDIT/HAPUS
                    @endphp

                    {{-- ================= SUPERUSER (rapi + lengkap) ================= --}}
                    @if($isSuperuser)
                        <div class="d-flex flex-wrap gap-2 align-items-center">

                        {{-- Approve / Reject hanya kalau pending --}}
                        @if($canApprove && $isPending)
                            <div class="btn-group btn-group-sm" role="group" aria-label="Approve Reject">
                                <form action="{{ route('target.approve', $t->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success"
                                            onclick="return confirm('Setujui target ini?')">
                                        ✔ Setujui
                                    </button>
                                </form>

                                <form action="{{ route('target.reject', $t->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger"
                                            onclick="return confirm('Tolak target ini?')">
                                        ✖ Tolak
                                    </button>
                                </form>
                            </div>
                        @endif

                        {{-- Edit / Hapus (DISABLE kalau sudah approved/rejected) --}}
                        <div class="d-inline-flex gap-1">
                            @if(!$isLocked)
                                <a href="{{ route('target.edit', $t->id) }}" class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                <form action="{{ route('target.destroy', $t->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-danger btn-sm"
                                            onclick="return confirm('Hapus target ini?')">
                                        Hapus
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-warning btn-sm" disabled title="Target sudah diproses">
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm" disabled title="Target sudah diproses">
                                    Hapus
                                </button>
                            @endif
                        </div>

                        </div>

                    {{-- ================= KASUBAG KEUANGAN ================= --}}
                    @elseif($isKasubagKeu)
                        @if($isPending)
                            <div class="d-flex flex-wrap gap-2">
                                <form action="{{ route('target.approve', $t->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-success btn-sm"
                                            onclick="return confirm('Setujui target ini?')">✔ Setujui</button>
                                </form>

                                <form action="{{ route('target.reject', $t->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Tolak target ini?')">✖ Tolak</button>
                                </form>
                            </div>
                        @else
                            <span class="text-muted small">—</span>
                        @endif

                    {{-- ================= ROLE LAIN (kepala seksi / dll) ================= --}}
                    @else
                        <div class="d-flex flex-wrap gap-2">
                            @if(!$isLocked)
                                <a href="{{ route('target.edit', $t->id) }}" class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                <form action="{{ route('target.destroy', $t->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('Hapus target ini?')">
                                        Hapus
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-warning btn-sm" disabled title="Target sudah diproses">
                                    Edit
                                </button>
                                <button class="btn btn-danger btn-sm" disabled title="Target sudah diproses">
                                    Hapus
                                </button>
                            @endif
                        </div>
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
