<?php

namespace App\Http\Controllers;

use App\Models\RealisasiInduk;
use App\Models\RealisasiOutput;
use App\Models\RealisasiOutcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RealisasiOutputController extends Controller
{
    // 🔸 Tampilkan semua data output milik user
    public function index()
    {
        $user = Auth::user();
        $isSuperuser = $user->role === 'superuser';

        $queryOutput = RealisasiOutput::query();
        $queryOutcome = RealisasiOutcome::query();

        // Superuser bisa lihat semua user
        if (!$isSuperuser) {
            $queryOutput->where('user_id', $user->id);
            $queryOutcome->where('user_id', $user->id);
        } elseif (request('user')) {
            $queryOutput->where('user_id', request('user'));
            $queryOutcome->where('user_id', request('user'));
        }

        if (request('search')) {
            $queryOutput->where('uraian', 'like', '%' . request('search') . '%');
            $queryOutcome->where('uraian', 'like', '%' . request('search') . '%');
        }

        $data_output = $queryOutput->latest()->get();
        $data_outcome = $queryOutcome->latest()->get();
        $users = $isSuperuser ? \App\Models\User::all() : null;

        return view('realisasi.index', compact('data_output', 'data_outcome', 'users'));
    }


    // 🔸 Tampilkan form input semua bagian (a–g) dalam satu halaman
    public function create()
    {
        return view('output.create');
    }

    // 🔸 Simpan data bagian a. Output (Sub Koordinator / Ess IV)
    public function store(Request $request)
    {
        $request->validate([
            'uraian' => 'required|string',
            'target' => 'nullable|string',
            'realisasi' => 'nullable|string',
            'capaian' => 'nullable|string',
        ]);

        // Hitung triwulan otomatis berdasarkan bulan saat ini
        $bulan = now()->month;
        $triwulan = match(true) {
            $bulan >= 1 && $bulan <= 3 => 'I',
            $bulan >= 4 && $bulan <= 6 => 'II',
            $bulan >= 7 && $bulan <= 9 => 'III',
            default => 'IV',
        };

        RealisasiOutput::create([
            'user_id' => Auth::id(),
            'triwulan' => $triwulan,
            'uraian' => $request->uraian,
            'target' => $request->target,
            'realisasi' => $request->realisasi,
            'capaian' => $request->capaian,
        ]);

        return redirect()->route('realisasi-output.index')->with('success', 'Data Output berhasil disimpan!');
    }

    // 🔸 Simpan data bagian b. Outcome (Eselon III)
    public function storeOutcome(Request $request)
    {
        $request->validate([
            'uraian' => 'required|string',
            'target' => 'nullable|string',
            'realisasi' => 'nullable|string',
            'capaian' => 'nullable|string',
        ]);

        // Hitung triwulan otomatis
        $bulan = now()->month;
        $triwulan = match(true) {
            $bulan >= 1 && $bulan <= 3 => 'I',
            $bulan >= 4 && $bulan <= 6 => 'II',
            $bulan >= 7 && $bulan <= 9 => 'III',
            default => 'IV',
        };

        RealisasiOutcome::create([
            'user_id' => Auth::id(),
            'triwulan' => $triwulan,
            'uraian' => $request->uraian,
            'target' => $request->target,
            'realisasi' => $request->realisasi,
            'capaian' => $request->capaian,
        ]);

        return redirect()->route('realisasi-output.index')->with('success', 'Data Outcome berhasil disimpan!');
    }
    public function show($indukId)
    {
        $induk = \App\Models\RealisasiInduk::with([
            'outputs', 'outcomes', 'keuangans', 'keberhasilan', 'sasarans', 'sebelumnya'
        ])->findOrFail($indukId);

        // Kelompokkan per triwulan
        $outputs = $induk->outputs->groupBy('triwulan');
        $outcomes = $induk->outcomes->groupBy('triwulan');
        $keuangans = $induk->keuangans->groupBy('triwulan');
        $keberhasilan = $induk->keberhasilan;

        return view('realisasi.show', compact(
            'induk',
            'outputs',
            'outcomes',
            'keuangans',
            'keberhasilan'
        ));
    }

    public function edit($indukId){
        $induk = RealisasiInduk::findOrFail($indukId);
            $informasi = \App\Models\InformasiUmum::where('user_id', auth()->id())->first();
            $outputs   = \App\Models\RealisasiOutput::where('induk_id', $indukId)->get()->keyBy('triwulan');
            $outcomes  = \App\Models\RealisasiOutcome::where('induk_id', $indukId)->get()->keyBy('triwulan');
            $keuangans = \App\Models\RealisasiKeuangan::where('induk_id', $indukId)->get()->keyBy('triwulan');
            $keberhasilan = \App\Models\RealisasiKeberhasilan::where('induk_id', $indukId)->first();
    
        return view('realisasi_output.edit', compact('induk', 'informasi', 'outputs', 'outcomes', 'keuangans', 'keberhasilan'));
    }

}