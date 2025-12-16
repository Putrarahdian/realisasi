@extends('layout.weblab')

@section('content')
<div class="container py-4">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body">
      <h4 class="fw-bold mb-3">Alur Keuangan</h4>

      <form class="d-flex gap-2 mb-3" method="GET" action="{{ route('keuangan.index') }}">
        <select name="tahun" class="form-select" style="max-width: 140px" onchange="this.form.submit()">
          @for($y = date('Y'); $y >= date('Y')-5; $y--)
            <option value="{{ $y }}" {{ (int)$tahun === $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </form>

      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead class="table-light">
            <tr class="text-center">
              <th style="width:70px;">TW</th>
              <th>Program/Kegiatan</th>
              <th style="width:160px;">Target</th>
              <th style="width:160px;">Realisasi</th>
              <th style="width:120px;">Capaian</th>
            </tr>
          </thead>
          <tbody>
            @forelse($data as $row)
              <tr>
                <td class="text-center">{{ $row->triwulan }}</td>
                <td>{{ $row->induk->program ?? '-' }}</td>
                <td class="text-end">{{ $row->target ?? '-' }}</td>
                <td class="text-end">{{ $row->realisasi ?? '-' }}</td>
                <td class="text-end">{{ isset($row->capaian) ? $row->capaian.'%' : '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted">Belum ada data.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="d-flex justify-content-center">
        {{ $data->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection
