<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RealisasiKeuangan;
use App\Models\Keuangan;

class KeuanganController extends Controller
{
    private function isKepalaDinas($user): bool
    {
        $jab = $user?->jabatan;

        return $jab && (
            ($jab->jenis_jabatan ?? null) === 'kepala_dinas'
            || ($jab->nama ?? null) === 'Kepala Dinas'
        );
    }

    private function isKasubagKeu($user): bool
    {
        $jab = $user?->jabatan;

        return $jab && (
            ($jab->jenis_jabatan ?? null) === 'kasubag_keuangan'
            || ($jab->nama ?? null) === 'Kasubag Keuangan'
        );
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // akses
        if (!(
            $user->role === 'superuser' ||
            $this->isKepalaDinas($user) ||
            $this->isKasubagKeu($user)
        )) {
            abort(403, 'Tidak punya akses ke Keuangan.');
        }

        // tahun
        $tahun = (int) $request->input('tahun', date('Y'));

        // jenis: masuk / keluar
        $jenis = $request->input('jenis'); // bisa null

        // total & saldo (tetap dihitung untuk 1 tahun)
        $totalMasuk = Keuangan::where('jenis', 'masuk')
            ->whereYear('tanggal', $tahun)
            ->sum('jumlah');

        $totalKeluar = Keuangan::where('jenis', 'keluar')
            ->whereYear('tanggal', $tahun)
            ->sum('jumlah');

        $saldo = $totalMasuk - $totalKeluar;

        // default biar tidak null (hindari error foreach di blade)
        $masuk  = collect();
        $keluar = collect();

        if ($jenis === 'masuk') {
            $masuk = Keuangan::whereYear('tanggal', $tahun)
                ->where('jenis', 'masuk')
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(10)
                ->appends($request->query());

        } elseif ($jenis === 'keluar') {
            $keluar = Keuangan::whereYear('tanggal', $tahun)
                ->where('jenis', 'keluar')
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(10)
                ->appends($request->query());

        } else {
            // kalau user buka /keuangan tanpa jenis → tampilkan keduanya
            $masuk = Keuangan::whereYear('tanggal', $tahun)
                ->where('jenis', 'masuk')
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(10, ['*'], 'masuk_page')
                ->appends($request->query());

            $keluar = Keuangan::whereYear('tanggal', $tahun)
                ->where('jenis', 'keluar')
                ->orderBy('tanggal', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(10, ['*'], 'keluar_page')
                ->appends($request->query());
        }

        $isKepalaDinas = $this->isKepalaDinas($user);

        return view('keuangan.index', compact(
            'tahun',
            'jenis',
            'masuk',
            'keluar',
            'totalMasuk',
            'totalKeluar',
            'saldo',
            'isKepalaDinas'
        ));
    }


    public function storeMasuk(Request $request)
    {
        $user = auth()->user();

        // hanya kepala dinas / superuser yang boleh input uang masuk
        if (!($user->role === 'superuser' || $this->isKepalaDinas($user))) {
            abort(403, 'Hanya Kepala Dinas yang bisa menambah uang masuk.');
        }

        $validated = $request->validate([
            'tanggal'    => ['required', 'date'],
            'jumlah'     => ['required', 'numeric', 'min:0'],
            'keterangan' => ['nullable', 'string'],
        ]);

        Keuangan::create([
            'tanggal'      => $validated['tanggal'],
            'jenis'        => 'masuk',
            'jumlah'       => $validated['jumlah'],
            'keterangan'   => $validated['keterangan'] ?? null,
            'created_by'   => $user->id,
            'realisasi_induk_id' => null,
        ]);

        return back()->with('success', 'Uang masuk berhasil ditambahkan.');
    }
}