<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RealisasiKeuangan;

class KeuanganController extends Controller
{
    private function ensureKasubag(): void
    {
        $u = auth()->user();
        $isKasubag = $u && $u->jabatan && $u->jabatan->jenis_jabatan === 'kasubag_keuangan';

        if (!$isKasubag) {
            abort(403, 'Hanya Kasubag Keuangan yang boleh mengakses menu Keuangan.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureKasubag();

        $tahun = $request->input('tahun', date('Y'));

        $data = RealisasiKeuangan::with(['induk'])
            ->whereHas('induk', function ($q) use ($tahun) {
                $q->where('tahun', $tahun);
            })
            ->orderBy('induk_id', 'desc')
            ->orderByRaw("FIELD(triwulan,'I','II','III','IV')")
            ->paginate(15);

        return view('keuangan.index', compact('data', 'tahun'));
    }
}
