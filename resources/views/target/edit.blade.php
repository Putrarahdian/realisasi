@extends('layout.weblab')

@section('content')
<div class="container">
    <h3>Edit Target (Rencana)</h3>

    @php
        $output   = $target->rincian->firstWhere('jenis','output');
        $outcome  = $target->rincian->firstWhere('jenis','outcome');
        $sasaran  = $target->rincian->firstWhere('jenis','sasaran');
        $keuangan = $target->rincian->firstWhere('jenis','keuangan');
    @endphp

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('target.update', $target->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Tahun</label>
            <input type="number" name="tahun" class="form-control"
                   value="{{ old('tahun', $target->tahun) }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Judul Target</label>
            <input type="text" name="judul" class="form-control"
                   value="{{ old('judul', $target->judul) }}" required>
        </div>

        <hr>

        <h5>Output</h5>
        <div class="mb-3">
            <label class="form-label">Uraian Output</label>
            <textarea name="output_uraian" class="form-control" rows="3" required>{{ old('output_uraian', $output?->uraian) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Target Output</label>
            <input type="text" name="output_target" class="form-control"
                   value="{{ old('output_target', $output?->target) }}" required>
        </div>

        <h5>Outcome</h5>
        <div class="mb-3">
            <label class="form-label">Uraian Outcome</label>
            <textarea name="outcome_uraian" class="form-control" rows="3" required>{{ old('outcome_uraian', $outcome?->uraian) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Target Outcome</label>
            <input type="text" name="outcome_target" class="form-control"
                   value="{{ old('outcome_target', $outcome?->target) }}" required>
        </div>

        <h5>Sasaran</h5>
        <div class="mb-3">
            <label class="form-label">Uraian Sasaran</label>
            <textarea name="sasaran_uraian" class="form-control" rows="3" required>{{ old('sasaran_uraian', $sasaran?->uraian) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Target Sasaran</label>
            <input type="text" name="sasaran_target" class="form-control"
                   value="{{ old('sasaran_target', $sasaran?->target) }}" required>
        </div>

        <h5>Keuangan</h5>
        <div class="mb-3">
            <label class="form-label">Uraian Keuangan</label>
            <textarea name="keuangan_uraian" class="form-control" rows="3" required>{{ old('keuangan_uraian', $keuangan?->uraian) }}</textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Target Keuangan</label>
            <input type="text" name="keuangan_target" class="form-control"
                   value="{{ old('keuangan_target', $keuangan?->target) }}" required>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-success">Update</button>
            <a href="{{ route('target.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
