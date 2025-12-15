<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RealisasiExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\InformasiUmum;
use App\Models\RealisasiOutput;
use App\Models\RealisasiOutcome;
use App\Models\RealisasiSasaran;
use App\Models\RealisasiKeuangan;
use App\Models\RealisasiKeberhasilan;
use App\Models\RealisasiInduk;
use App\Models\Bidang;
use App\Models\Seksi;
use App\Models\User;

class RealisasiController extends Controller
{
    const TRIWULAN = ['I', 'II', 'III', 'IV'];

    /**
     * 🔒 Hanya superuser & user (seksi) yang boleh mengubah data
     */

    private function isKasubagKeuangan(): bool
    {
        $u = auth()->user();
        return $u && $u->jabatan && $u->jabatan->jenis_jabatan === 'kasubag_keuangan';
    }

    private function validateKeuangan(Request $request)
    {
        $request->validate([
            'induk_id' => 'required|exists:realisasi_induks,id',
            'keuangan' => 'required|array',
            'keuangan.target' => 'required|numeric|min:0',
            'keuangan.realisasi' => 'required|numeric|min:0',
        ]);
    }


    private function authorizeEditData()
    {
        $user = auth()->user();
        $role = $user?->role;

        if (!in_array($role, ['superuser', 'user'])) {
            abort(403, 'Hanya Superuser dan Seksi yang boleh menambah / mengubah data realisasi.');
        }
    }

    // di dalam class RealisasiController
    private function forbidAdminOnKegiatan(): void
    {
        $user = auth()->user();
        if ($user && $user->role === 'admin') {
            abort(403, 'Kepala Bidang hanya dapat mengakses halaman rekap.');
        }
    }


    /**
     * 🔒 Cek apakah user boleh mengakses data induk tertentu (berdasarkan bidang)
     */
    private function authorizeBidang($induk)
    {
        if (!$induk) {
        abort(404, 'Data induk tidak ditemukan.');
        }

        $user = auth()->user();

        if ($this->isKasubagKeuangan()) {
            return true; // boleh lihat semua induk
        }

        // Superuser bebas
        if ($user->role === 'superuser') {
            return true;
        }
        // Kadis = admin yang tidak punya bidang_id → boleh lihat semua bidang
        if ($user->role === 'admin' && empty($user->bidang_id)) {
            return true;
        }

        // User & admin hanya boleh akses bidangnya sendiri
        if ($induk->bidang_id !== $user->bidang_id) {
            abort(403, 'Akses Ditolak! Anda tidak memiliki izin untuk data bidang ini.');
        }

        return true;
    }
    // 🔒 Hanya Kadis & Kabid yang boleh isi disposisi
    private function authorizeDisposisi(RealisasiInduk $induk): void
    {
    $user    = auth()->user();
    $jabatan = $user->jabatan->nama ?? null;

    // Kadis boleh semua bidang
    if ($jabatan === 'Kepala Dinas') {
        return;
    }

    // Kabid: hanya boleh disposisi untuk bidangnya sendiri
    if ($jabatan === 'Kepala Bidang') {
        if ($user->bidang_id && $user->bidang_id == $induk->bidang_id) {
            return;
        }

        abort(403, 'Anda hanya boleh memberi disposisi untuk bidang Anda sendiri.');
    }

    // Selain Kadis / Kabid tidak boleh
    abort(403, 'Hanya Kepala Dinas dan Kepala Bidang yang boleh mengisi disposisi.');
    }

    private function hitungUlangCapaianKeuangan($indukId)
    {
        $semuaKeu    = RealisasiKeuangan::where('induk_id', $indukId)->get();
        $totalTarget = $semuaKeu->sum('target');
    
        foreach ($semuaKeu as $row) {
            $row->capaian = $totalTarget > 0
                ? round(($row->realisasi ?? 0) / $totalTarget * 100, 2)
                : 0;
            $row->save();
        }
    }



    public function index(Request $request)
    {
        $this->forbidAdminOnKegiatan();
        $query = RealisasiInduk::query();
        $user  = Auth::user();

        $bidangs = [];
        $seksis = [];
        
        $selectedBidangId = $request->input('bidang_id');
        $selectedSeksiId = $request->input('seksi_id');

        if ($user->role === 'superuser') {

            $bidangs = Bidang::orderBy('nama')->get();

        // list seksi buat dropdown
        if ($selectedBidangId) {
            $seksis = Seksi::where('bidang_id', $selectedBidangId)
                ->orderBy('nama')
                ->get();
        } else {
            $seksis = Seksi::orderBy('nama')->get();
        }
        if ($selectedSeksiId && !$seksis->contains('id', $selectedSeksiId)) {
            $selectedSeksiId = null;
        }
    }

        // 🔹 Filter bidang otomatis
        if ($user->role !== 'superuser' && !$this->isKasubagKeuangan()) {
            $query->where('bidang_id', $user->bidang_id);
        } elseif ($request->filled('bidang_id')) {
            $query->where('bidang_id', $request->bidang_id);
        }

        if ($user->role === 'superuser' && $selectedSeksiId) {
        $query->where('seksi_id', $selectedSeksiId);
        }

        $tahun = $request->input('tahun', date('Y'));
        $query->where('tahun', $tahun);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sasaran_strategis', 'like', "%{$search}%")
                    ->orWhere('program', 'like', "%{$search}%")
                    ->orWhere('indikator', 'like', "%{$search}%")
                    ->orWhere('target', 'like', "%{$search}%")
                    ->orWhere('hambatan', 'like', "%{$search}%")
                    ->orWhere('rekomendasi', 'like', "%{$search}%")
                    ->orWhere('tindak_lanjut', 'like', "%{$search}%")
                    ->orWhere('dokumen', 'like', "%{$search}%")
                    ->orWhere('strategi', 'like', "%{$search}%")
                    ->orWhere('alasan', 'like', "%{$search}%");
            });
        }

        $data_induk = $query->orderBy('id', 'desc')->paginate(5);
        $data_induk->appends($request->only('search', 'tahun'));

        
        $tahunDashboard = $request->input('tahun_dashboard', date('Y'));
        $baseQuery = RealisasiInduk::query()
            -> where('tahun', $tahunDashboard);

        if ($user->role !== 'superuser' && !$this->isKasubagKeuangan() && $user->bidang_id) {
            $baseQuery->where('bidang_id', $user->bidang_id);
        }
        
        $triwulans = ['I', 'II', 'III', 'IV'];
        $belumDiisi = [];

        foreach ($triwulans as $tw) {
            $qBelum = (clone $baseQuery);

            $qBelum->where(function($q) use ($tw) {
                $q->whereDoesntHave('outputs', function ($sub) use ($tw){
                        $sub->where('triwulan', $tw);
                    });
                $q->orWhereDoesntHave('outcomes', function ($sub) use ($tw){
                        $sub->where('triwulan', $tw);
                    });
                $q->orWhereDoesntHave('keuangans', function ($sub) use ($tw){
                        $sub->where('triwulan', $tw);
                    });
            });
            $belumDiisi[$tw] = $qBelum->get();
        }

        return view('realisasi.index', compact('data_induk', 'tahun','seksis', 'bidangs','user') + [
            'tahunDashboard' => $tahunDashboard,
            'triwulans' => $triwulans,
            'belumDiisi' => $belumDiisi,
        ]);
    }

    public function byTriwulan($no, Request $request)
    {
        $this->forbidAdminOnKegiatan();
        // 1 -> I, 2 -> II, dst
        $mapTriwulan = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];

        if (!isset($mapTriwulan[$no])) {
            abort(404, 'Triwulan tidak valid');
        }

        $tw   = $mapTriwulan[$no];
        $user = Auth::user();

        // ambil data induk + relasi per triwulan
        $query = RealisasiInduk::with([
            'outputs' => function ($q) use ($tw) {
                $q->where('triwulan', $tw);
            },
            'outcomes' => function ($q) use ($tw) {
                $q->where('triwulan', $tw);
            },
            'keuangans' => function ($q) use ($tw) {
                $q->where('triwulan', $tw);
            },
            'bidang',
        ]);

        // untuk dropdown bidang di superuser
        $bidangs = [];
        if ($user->role === 'superuser') {
            $bidangs = Bidang::all();
        }

        // filter bidang
        if ($user->role !== 'superuser' && !$this->isKasubagKeuangan()) {
            $query->where('bidang_id', $user->bidang_id);
        } elseif ($request->filled('bidang_id')) {
            $query->where('bidang_id', $request->bidang_id);
        }

        // filter tahun
        $tahun = $request->input('tahun', date('Y'));
        $query->where('tahun', $tahun);

        // ambil data
        $data_induk = $query->orderBy('id', 'desc')->paginate(10);
        $data_induk->appends($request->only('tahun', 'bidang_id'));
        
        return view('triwulan.index', compact(
            'tw',
            'no',
            'tahun',
            'data_induk',
            'bidangs'
        ));
    }

    // 🔹 Form tambah data dari halaman Triwulan (masih pakai form induk)
    public function createByTriwulan($no, $indukId)
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();

        $index        = (int)$no - 1;
        $kodeTriwulan = self::TRIWULAN[$index] ?? abort(404, 'Triwulan tidak valid');

        // di atas return view('triwulan.create', [...]);
        $sasaran = RealisasiSasaran::where('induk_id', $indukId)->first();
        $induk = RealisasiInduk::findOrFail($indukId);
        $this->authorizeBidang($induk);

        $twt = null;
        if ($no > 1) {
            $tws = self::TRIWULAN[0];

            $twt = [
                'output' => RealisasiOutput::where('induk_id', $indukId)
                    -> where ('triwulan', $tws)
                    -> first(),
                'outcome' => RealisasiOutcome::where('induk_id', $indukId)
                    -> where ('triwulan', $tws)
                    -> first(),
            ];
        }

        return view('triwulan.create', [
            'induk'        => $induk,
            'output'       => null,
            'outcome'      => null,
            'keuangan'     => null,
            'keberhasilan' => null,
            'kodeTriwulan' => $kodeTriwulan,
            'no'           => $no,
            'twt'           => $twt,
            'sasaran'           => $sasaran,
        ]);
    }


    // 🔹 Proses simpan dari form khusus Triwulan (isi child untuk satu triwulan)
    public function storeByTriwulan($no, Request $request)
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();

        // konversi 1–4 ke I–IV pakai constant
        $index        = (int) $no - 1;
        $kodeTriwulan = self::TRIWULAN[$index] ?? abort(404, 'Triwulan tidak valid');

        // ✅ KASUBAG KEUANGAN: hanya boleh simpan data keuangan
        if ($this->isKasubagKeuangan()) {
            $this->validateKeuangan($request);
        
            $indukId = $request->input('induk_id');
            $induk   = RealisasiInduk::findOrFail($indukId);
        
            // kasubag boleh lihat semua (authorizeBidang kamu sudah return true)
            $this->authorizeBidang($induk);
        
            // simpan hanya keuangan
            $k = $request->input('keuangan', []);
            $targetK   = (float) ($k['target'] ?? 0);
            $realisasiK= (float) ($k['realisasi'] ?? 0);
        
            RealisasiKeuangan::updateOrCreate(
                ['induk_id' => $indukId, 'triwulan' => $kodeTriwulan],
                [
                    'user_id'   => auth()->id(),
                    'target'    => $targetK,
                    'realisasi' => $realisasiK,
                    'capaian'   => 0,
                ]
            );
        
            $this->hitungUlangCapaianKeuangan($indukId);
        
            return redirect()
                ->route('realisasi.triwulan.index', $no)
                ->with('success', "Data Keuangan Triwulan {$kodeTriwulan} berhasil disimpan.");
        }

        $rules = [
            'induk_id'             => 'required|exists:realisasi_induks,id',

            'keuangan' => 'required|array',

            // OUTPUT
            'output.uraian'        => 'required|string',
            'output.realisasi'     => 'required|numeric|min:0',

            // OUTCOME
            'outcome.uraian'       => 'required|string',
            'outcome.realisasi'    => 'required|numeric|min:0',

            // KEUANGAN (selalu wajib tiap TW)
            'keuangan.target'      => 'required|numeric|min:0',
            'keuangan.realisasi'   => 'required|numeric|min:0',
        ];

        // 🔹 Target Output & Outcome wajib HANYA di TW I
        if ((int)$no === 1) {
            $rules['output.target']   = 'required|numeric|min:0';
            $rules['outcome.target']  = 'required|numeric|min:0';

            // Sasaran wajib diisi di TW I
            $rules['sasaran.uraian']  = 'required|string';
            $rules['sasaran.target']  = 'required|numeric|min:0';
        }

        // 🔹 Sasaran realisasi wajib di TW IV
        if ((int)$no === 4) {
            $rules['sasaran.realisasi'] = 'required|numeric|min:0';
        }

        $request->validate($rules);

        $indukId = $request->input('induk_id');

        $induk = RealisasiInduk::findOrFail($indukId);
        $this->authorizeBidang($induk);

        $tws = self::TRIWULAN[0];
        $ots = RealisasiOutput::where('induk_id', $indukId)->where('triwulan', $tws)->first();
        $otcs = RealisasiOutcome::where('induk_id', $indukId)->where('triwulan', $tws)->first();

        // 🔹 OUTPUT
        $o = $request->input('output', []);
        
        $targetO = $no == 1
            ? (float) ($o['target'] ?? 0)
            : (float) (optional($ots)->target);

        $realisasiO = (float) ($o['realisasi'] ?? 0);

        if ($targetO <= 0) {
            $targetO = 0;
            $realisasiO = 0;
            $capaianO = 0;
        } else {
            $capaianO = round($realisasiO / $targetO * 100, 2);
        }
        if (!empty($o['uraian']) || $targetO > 0 || $realisasiO > 0) {
            RealisasiOutput::updateOrCreate(
                ['induk_id' => $indukId, 'triwulan' => $kodeTriwulan],
                [
                    'user_id'   => auth()->id(),
                    'uraian'    => $o['uraian'] ?? '-',
                    'target'    => $targetO,
                    'realisasi' => $realisasiO,
                    'capaian'   => $capaianO,
                ]
            );
        }

        // 🔹 OUTCOME
        $oc = $request->input('outcome', []);

        $targetOc = $no == 1
        ? (float) ($oc['target'] ?? 0)
        : (float) (optional($otcs)->target);

        $realisasiOc = (float) ($oc['realisasi'] ?? 0);

        if ($targetOc <= 0) {
            $targetOc = 0;
            $realisasiOc = 0;
            $capaianOc = 0;
        } else {
            $capaianOc = round($realisasiOc / $targetOc * 100, 2);
        }

        if (!empty($oc['uraian']) || $targetOc > 0 || $realisasiOc > 0) {
            RealisasiOutcome::updateOrCreate(
                ['induk_id' => $indukId, 'triwulan' => $kodeTriwulan],
                [
                    'user_id'   => auth()->id(),
                    'uraian'    => $oc['uraian'] ?? '-',
                    'target'    => $targetOc,
                    'realisasi' => $realisasiOc,
                    'capaian'   => $capaianOc,
                ]
            );
        }
 
        // ===================== SASARAN (1x input) =====================
        $s = $request->input('sasaran', []);

        if (!empty($s)) {
            // Ambil baris sasaran yang sudah ada (kalau ada)
            $sasaranRow = RealisasiSasaran::where('induk_id', $indukId)->first();

            // TW 1: isi uraian + target, realisasi 0
            if ((int)$no === 1) {
                $targetS    = (float) ($s['target'] ?? 0);
                $realisasiS = 0; // belum ada di TW 1
            }
            // TW 4: cuma boleh ubah realisasi
            elseif ((int)$no === 4 && $sasaranRow) {
                $targetS    = (float) $sasaranRow->target;
                $realisasiS = (float) ($s['realisasi'] ?? 0);
            } else {
                // TW 2–3: tidak mengubah apa-apa
                $targetS    = (float) (optional($sasaranRow)->target ?? 0);
                $realisasiS = (float) (optional($sasaranRow)->realisasi ?? 0);
            }

            if ($targetS <= 0) {
                $targetS    = 0;
                $realisasiS = 0;
                $capaianS   = 0;
            } else {
                $capaianS = round($realisasiS / $targetS * 100, 2);
            }

            \App\Models\RealisasiSasaran::updateOrCreate(
                ['induk_id' => $indukId],
                [
                    'user_id'   => auth()->id(),
                    'uraian' => $s['uraian'] ?? (optional($sasaranRow)->uraian ?? '-'),
                    'target'    => $targetS,
                    'realisasi' => $realisasiS,
                    'capaian'   => $capaianS,
                ]
            );
        }

        // ===================== KEUANGAN =====================
        $k = $request->input('keuangan', []);

        $targetK = (float) ($k['target'] ?? 0);
        $realisasiK = (float) ($k['realisasi'] ?? 0);

            RealisasiKeuangan::updateOrCreate(
                ['induk_id' => $indukId, 'triwulan' => $kodeTriwulan],
                [
                    'user_id'   => auth()->id(),
                    'target'    => $targetK,
                    'realisasi' => $realisasiK,
                    'capaian'   => 0,
                ]
            );
        $this->hitungUlangCapaianKeuangan($induk->id);
        

        // ===================== KEBERHASILAN / HAMBATAN PER TRIWULAN =====================
        $teksKeberhasilan = trim($request->input('keberhasilan_triwulan', ''));
        $teksHambatan     = trim($request->input('hambatan_triwulan', ''));

        // kalau dua-duanya kosong, tidak usah simpan apa-apa
        if ($teksKeberhasilan !== '' || $teksHambatan !== '') {

            // ambil atau buat baris realisasi_keberhasilan untuk induk ini
            $rekap = RealisasiKeberhasilan::firstOrCreate(
                ['induk_id' => $indukId],
                [
                    'user_id'      => auth()->id(),
                    'keberhasilan' => '',
                    'hambatan'     => '',
                ]
            );

            // simpan ke kolom sesuai triwulan
            switch ((int) $no) {
                case 1:
                    $rekap->keberhasilan_tw1 = $teksKeberhasilan;
                    $rekap->hambatan_tw1     = $teksHambatan;
                    break;
                case 2:
                    $rekap->keberhasilan_tw2 = $teksKeberhasilan;
                    $rekap->hambatan_tw2     = $teksHambatan;
                    break;
                case 3:
                    $rekap->keberhasilan_tw3 = $teksKeberhasilan;
                    $rekap->hambatan_tw3     = $teksHambatan;
                    break;
                case 4:
                    $rekap->keberhasilan_tw4 = $teksKeberhasilan;
                    $rekap->hambatan_tw4     = $teksHambatan;
                    break;
            }

            $rekap->save();
        }

        return redirect()
            ->route('realisasi.triwulan.index', $no)
            ->with('success', "Data Triwulan {$kodeTriwulan} berhasil disimpan.");
    } 


    public function createInduk()
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();

        $bidangs = [];
        $seksis = [];

        if (auth()->user()->role === 'superuser') {
            $bidangs = Bidang::orderBy('nama')->get();
            $seksis = Seksi::with('bidang')->orderBy('nama')->get();
        }

        return view('realisasi_induk.create', compact('bidangs','seksis'));
    }

    public function create()
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();

        return view('realisasi.create');
    }

    public function store(Request $request)
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();

        $request->validate([
            // Validasi umum
            'informasi.tahun'           => 'required|string|max:4',
            'informasi.instansi'        => 'required|string',
            'informasi.penanggung_jawab'=> 'required|string',

            // Output dan Outcome
            'output.*.uraian'    => 'required|string',
            'output.*.target'    => 'required|numeric|min:1',
            'output.*.realisasi' => 'required|numeric|min:0',
            'outcome.*.uraian'   => 'required|string',
            'outcome.*.target'   => 'required|numeric|min:1',
            'outcome.*.realisasi'=> 'required|numeric|min:0',

            // Sasaran
            'sasaran.uraian'     => 'required|string',
            'sasaran.target'     => 'required|numeric|min:1',
            'sasaran.realisasi'  => 'required|numeric|min:0',

            // Keuangan
            'keuangan.*.target'  => 'required|numeric|min:1',
            'keuangan.*.realisasi'=> 'required|numeric|min:0',

            // Keberhasilan
            'keberhasilan.keberhasilan' => 'required|string',
            'keberhasilan.hambatan'     => 'required|string',

        ], [
            'required' => 'Field ini wajib diisi.',
            'numeric'  => 'Harus berupa angka.',
            'min'      => 'Nilai minimal adalah 1.',
        ]);

        $userId = Auth::id();

        // Cek apakah user sudah pernah mengisi
        if (InformasiUmum::where('user_id', $userId)->exists()) {
            return redirect()->route('realisasi.index')->with('error', 'Anda sudah mengisi sebelumnya.');
        }

        // Buat data induk kosong (jika belum punya field input tambahan)
        $induk = RealisasiInduk::create([]);

        // Simpan Informasi Umum
        InformasiUmum::create([
            'user_id'          => $userId,
            'triwulan'         => 'I-IV',
            'tahun'            => $request->input('informasi.tahun'),
            'instansi'         => $request->input('informasi.instansi'),
            'penanggung_jawab' => $request->input('informasi.penanggung_jawab'),
        ]);
        // Hitung total target keuangan dari input (TW I–IV)
        $totalTargetKeu = 0;
        foreach (self::TRIWULAN as $tw) {
            $k = $request->input("keuangan.$tw");
            if ($k) {
                $totalTargetKeu += (float) ($k['target'] ?? 0);
            }
        }

        foreach (self::TRIWULAN as $tw) {
            // Output
            $o = $request->input("output.$tw");
            if ($o) {
                RealisasiOutput::create([
                    'user_id'   => $userId,
                    'induk_id'  => $induk->id,
                    'triwulan'  => $tw,
                    'uraian'    => $o['uraian'],
                    'target'    => $o['target'],
                    'realisasi' => $o['realisasi'],
                    'capaian'   => ($o['target'] > 0) ? round($o['realisasi'] / $o['target'] * 100, 2) : null
                ]);
            }

            // Outcome
            $oc = $request->input("outcome.$tw");
            if ($oc) {
                RealisasiOutcome::create([
                    'user_id'   => $userId,
                    'induk_id'  => $induk->id,
                    'triwulan'  => $tw,
                    'uraian'    => $oc['uraian'],
                    'target'    => $oc['target'],
                    'realisasi' => $oc['realisasi'],
                    'capaian'   => ($oc['target'] > 0) ? round($oc['realisasi'] / $oc['target'] * 100, 2) : null
                ]);
            }

            // Keuangan
        $k = $request->input("keuangan.$tw");
        if ($k) {
            $targetK    = (float) ($k['target'] ?? 0);
            $realisasiK = (float) ($k['realisasi'] ?? 0);

            RealisasiKeuangan::create([
                'user_id'   => $userId,
                'induk_id'  => $induk->id,
                'triwulan'  => $tw,
                'target'    => $targetK,
                'realisasi' => $realisasiK,
                'capaian'   => 0,
            ]);
        }
        $this->hitungUlangCapaianKeuangan($induk->id);
        }

        // Sasaran
        $s = $request->input('sasaran');
        RealisasiSasaran::create([
            'user_id'   => $userId,
            'induk_id'  => $induk->id,
            'uraian'    => $s['uraian'],
            'target'    => $s['target'],
            'realisasi' => $s['realisasi'],
            'capaian'   => ($s['target'] > 0) ? round($s['realisasi'] / $s['target'] * 100, 2) : null
        ]);

        // Keberhasilan
        $keb = $request->input('keberhasilan');
        RealisasiKeberhasilan::create([
            'user_id'      => $userId,
            'induk_id'     => $induk->id,
            'keberhasilan' => $keb['keberhasilan'],
            'hambatan'     => $keb['hambatan'],
        ]);

        return redirect()->route('realisasi.index')->with('success', 'Data berhasil disimpan!');
    }

    public function storeInduk(Request $request)
    {
        $this->forbidAdminOnKegiatan();
        // hanya superuser & user (seksi) yang boleh simpan
        $this->authorizeEditData();

        $rules = [
            'induk.tahun'             => 'required|digits:4',
            'induk.sasaran_strategis' => 'required|string',
            'induk.program'           => 'required|string',
            'induk.indikator'         => 'required|string',
            'induk.target'            => 'required|string',
            'induk.hambatan'          => 'required|string',
            'induk.rekomendasi'       => 'required|string',
            'induk.tindak_lanjut'     => 'required|string',
            'induk.dokumen'           => 'required|string',
            'induk.strategi'          => 'required|string',
            'induk.alasan'            => 'required|string',
        ];

        // superuser wajib pilih bidang
        if (auth()->user()->role === 'superuser') {
            $rules['induk.bidang_id'] = 'required|exists:bidangs,id';
            $rules['induk.seksi_id'] = 'required|exists:seksis,id';
        }

        $validated = $request->validate($rules);
        $data      = $validated['induk'];

        // tentukan bidang_id
        if (auth()->user()->role === 'superuser') {
            $bidangId = $data['bidang_id'];
            $seksiId = $data['seksi_id'];
        } else {
            $bidangId = auth()->user()->bidang_id;
            $seksiId = auth()->user()->seksi_id;
        }
        $user = auth()->user();

        $induk = RealisasiInduk::create([
            'tahun'             => $data['tahun'],
            'bidang_id'         => $bidangId,
            'sasaran_strategis' => $data['sasaran_strategis'],
            'program'           => $data['program'],
            'indikator'         => $data['indikator'],
            'target'            => $data['target'],
            'hambatan'          => $data['hambatan'],
            'rekomendasi'       => $data['rekomendasi'],
            'tindak_lanjut'     => $data['tindak_lanjut'],
            'dokumen'           => $data['dokumen'],
            'strategi'          => $data['strategi'],
            'alasan'            => $data['alasan'],
            'seksi_id'          => $seksiId,
        ]);

        return redirect()->route('realisasi.index')
            ->with('success', 'Data induk berhasil disimpan!');
    }

    public function edit($id)
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();

        $induk = RealisasiInduk::with([
            'outputs', 'outcomes', 'keuangans', 'keberhasilan', 'sasaran'
        ])->findOrFail($id);

        $this->authorizeBidang($induk);

        // ambil data informasi umum berdasarkan user aktif
        $informasi = InformasiUmum::where('user_id', auth()->id())->first();

        // siapkan data tambahan agar tidak error di view
        $outputs      = $induk->outputs->groupBy('triwulan');
        $outcomes     = $induk->outcomes->groupBy('triwulan');
        $keuangans    = $induk->keuangans->groupBy('triwulan');
        $keberhasilan = $induk->keberhasilan;
        $sasaran = $induk->sasaran;

        return view('realisasi.edit', compact('induk', 'sasaran', 'outcomes', 'informasi', 'outputs', 'keuangans', 'keberhasilan'));
    }

    public function editByTriwulan($no, $indukId)
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();
        
        // mapping 1–4 ke I–IV
        $index        = (int) $no - 1;
        $kodeTriwulan = self::TRIWULAN[$index] ?? abort(404, 'Triwulan tidak valid');

        if ($this->isKasubagKeuangan()) {
            $induk = RealisasiInduk::with(['keuangans'])->findOrFail($indukId);
            $this->authorizeBidang($induk);
        
            $keuangan = $induk->keuangans->firstWhere('triwulan', $kodeTriwulan);
        
            return view('triwulan.edit', [
                'induk'        => $induk,
                'informasi'    => null,
                'output'       => null,
                'outcome'      => null,
                'keuangan'     => $keuangan,
                'keberhasilan' => null,
                'keb_tw'       => null,
                'ham_tw'       => null,
                'kodeTriwulan' => $kodeTriwulan,
                'no'           => $no,
                'sasaran'      => null,
            ]);
        }

        // setelah $keberhasilan = $induk->keberhasilan;

        $induk = RealisasiInduk::with(['outputs', 'outcomes', 'keuangans', 'keberhasilan', 'sasaran'])
            ->findOrFail($indukId);
        
            $sasaran = $induk->sasaran instanceof \Illuminate\Support\Collection
            ? $induk->sasaran->first()
            : $induk->sasaran;

        // pastikan user hanya boleh akses bidangnya
        $this->authorizeBidang($induk);

        // informasi umum milik user aktif (kalau mau dipakai di view)
        $informasi = InformasiUmum::where('user_id', auth()->id())->first();

        // ambil hanya baris untuk triwulan ini
        $output       = $induk->outputs->firstWhere('triwulan', $kodeTriwulan);
        $outcome      = $induk->outcomes->firstWhere('triwulan', $kodeTriwulan);
        $keuangan     = $induk->keuangans->firstWhere('triwulan', $kodeTriwulan);
        $keberhasilan = $induk->keberhasilan;

        // *** Tambahan: ambil teks keberhasilan / hambatan sesuai triwulan ***
        $keb_tw = null;
        $ham_tw = null;

        if ($keberhasilan) {
            switch ((int) $no) {
                case 1:
                    $keb_tw = $keberhasilan->keberhasilan_tw1;
                    $ham_tw = $keberhasilan->hambatan_tw1;
                    break;
                case 2:
                    $keb_tw = $keberhasilan->keberhasilan_tw2;
                    $ham_tw = $keberhasilan->hambatan_tw2;
                    break;
                case 3:
                    $keb_tw = $keberhasilan->keberhasilan_tw3;
                    $ham_tw = $keberhasilan->hambatan_tw3;
                    break;
                case 4:
                    $keb_tw = $keberhasilan->keberhasilan_tw4;
                    $ham_tw = $keberhasilan->hambatan_tw4;
                    break;
            }
        }

        return view('triwulan.edit', [
            'induk'        => $induk,
            'informasi'    => $informasi,
            'output'       => $output,
            'outcome'      => $outcome,
            'keuangan'     => $keuangan,
            'keberhasilan' => $keberhasilan,
            'keb_tw'       => $keb_tw,
            'ham_tw'       => $ham_tw,
            'kodeTriwulan' => $kodeTriwulan, 
            'no'           => $no,           
            'sasaran'      => $sasaran,           
        ]);
    }

    public function editInduk($id)
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();

        $induk = RealisasiInduk::findOrFail($id);

        $this->authorizeBidang($induk);

        return view('realisasi_induk.edit', compact('induk'));
    }

    public function update(Request $request, $indukId)
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();

        $induk = RealisasiInduk::findOrFail($indukId);
        $this->authorizeBidang($induk);

        if ($this->isKasubagKeuangan()) {
                // izinkan update hanya keuangan
                foreach (['I','II','III','IV'] as $tw) {
                    $k = $request->input("keuangan.$tw");
                    if ($k) {
                        RealisasiKeuangan::updateOrCreate(
                            ['induk_id' => $indukId, 'triwulan' => $tw],
                            [
                                'user_id' => auth()->id(),
                                'target' => (float)($k['target'] ?? 0),
                                'realisasi' => (float)($k['realisasi'] ?? 0),
                                'capaian' => 0,
                            ]
                        );
                    }
                }
            
                $this->hitungUlangCapaianKeuangan($indukId);
            
                return redirect()->route('realisasi.show', $indukId)
                    ->with('success', 'Data keuangan berhasil diperbarui.');
            }

        $info = $request->input('informasi', []);

        // Update Informasi Umum
        InformasiUmum::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'tahun'             => $info['tahun'] ?? date('Y'),
                'triwulan'          => 'I-IV',
            ]
        );
        // Hitung total target keuangan dari input update
        $totalTargetKeu = 0;
        foreach (['I', 'II', 'III', 'IV'] as $twTmp) {
            $kTmp = $request->input("keuangan.$twTmp");
            if ($kTmp) {
                $totalTargetKeu += (float) ($kTmp['target'] ?? 0);
            }
        }

        // Update Output, Outcome, dan Keuangan
        foreach (['I', 'II', 'III', 'IV'] as $tw) {
            // Output
            $o = $request->input("output.$tw");
            $targetO    = (float) ($o['target']    ?? 0);
            $realisasiO = (float) ($o['realisasi'] ?? 0);

            // kalau target kosong ⇒ realisasi & capaian dipaksa 0
            if ($targetO <= 0) {
                $targetO    = 0;
                $realisasiO = 0;
                $capaianO   = 0;
            } else {
                $capaianO = round($realisasiO / $targetO * 100, 2);
            }
            if ($o) {
                RealisasiOutput::updateOrCreate(
                    ['induk_id' => $indukId, 'triwulan' => $tw],
                    [
                        'user_id'   => auth()->id(),
                        'uraian'    => $o['uraian'] ?? '-',
                        'target'    => $targetO,
                        'realisasi' => $realisasiO,
                        'capaian'   => $capaianO,
                    ]
                );
            }

            // Outcome
            $oc = $request->input("outcome.$tw");
            $targetOc    = (float) ($oc['target']    ?? 0);
            $realisasiOc = (float) ($oc['realisasi'] ?? 0);

            if ($targetOc <= 0) {
                $targetOc    = 0;
                $realisasiOc = 0;
                $capaianOc   = 0;
            } else {
                $capaianOc = round($realisasiOc / $targetOc * 100, 2);
            }
            if (!empty($oc['uraian']) || $targetOc > 0 || $realisasiOc > 0) {
                RealisasiOutcome::updateOrCreate(
                    ['induk_id' => $indukId, 'triwulan' => $tw],
                    [
                        'user_id'   => auth()->id(),
                        'uraian'    => $oc['uraian'] ?? '-',
                        'target'    => $targetOc,
                        'realisasi' => $realisasiOc,
                        'capaian'   => $capaianOc
                    ]
                );
            }

        // Keuangan
        $k = $request->input("keuangan.$tw");
        if (!empty($k['target']) || !empty($k['realisasi'])) {
            $targetK    = (float) ($k['target'] ?? 0);
            $realisasiK = (float) ($k['realisasi'] ?? 0);

            RealisasiKeuangan::updateOrCreate(
                ['induk_id' => $indukId, 'triwulan' => $tw],
                [
                    'user_id'   => auth()->id(),
                    'target'    => $targetK,
                    'realisasi' => $realisasiK,
                    'capaian'   => 0,
                ]
            );
        }
    }
        $this->hitungUlangCapaianKeuangan($induk->id);


        // ===================== SASARAN =====================
        $s = $request->input('sasaran', []);

        $tw1Output = RealisasiOutput::where('induk_id', $indukId)
            ->where('triwulan', 'I')
            ->first();

        if (!empty($s)) {
            $targetS    = $tw1Output ? (float) $tw1Output->target : ($s['target'] ?? 0);
            $realisasiS = (float) ($s['realisasi'] ?? 0);

            if ($targetS <= 0) {
                $targetS    = 0;
                $realisasiS = 0;
                $capaianS   = 0;
            } else {
                $capaianS = round($realisasiS / $targetS * 100, 2);
            }

            \App\Models\RealisasiSasaran::updateOrCreate(
                ['induk_id' => $indukId],
                [
                    'user_id'   => auth()->id(),
                    'uraian'    => $s['uraian'] ?? '-',
                    'target'    => $targetS,
                    'realisasi' => $realisasiS,
                    'capaian'   => $capaianS,
                ]
            );
        }


        // Update Keberhasilan 
        $keb = $request->input('keberhasilan', []);

        RealisasiKeberhasilan::updateOrCreate(
            ['induk_id' => $indukId],
            [
                'user_id'       => auth()->id(),
            'keberhasilan_tw1' => $keb['keberhasilan_tw1'] ?? null,
            'keberhasilan_tw2' => $keb['keberhasilan_tw2'] ?? null,
            'keberhasilan_tw3' => $keb['keberhasilan_tw3'] ?? null,
            'keberhasilan_tw4' => $keb['keberhasilan_tw4'] ?? null,
            'hambatan_tw1'     => $keb['hambatan_tw1'] ?? null,
            'hambatan_tw2'     => $keb['hambatan_tw2'] ?? null,
            'hambatan_tw3'     => $keb['hambatan_tw3'] ?? null,
            'hambatan_tw4'     => $keb['hambatan_tw4'] ?? null,
            ]
        );

        return redirect()->route('realisasi.show', $indukId)
            ->with('success', 'Data berhasil diperbarui.');
    }

    public function show($id)
    {
        $this->forbidAdminOnKegiatan();

        // ambil dulu datanya
        $induk = RealisasiInduk::with([
            'outputs',
            'outcomes',
            'keuangans',
            'keberhasilan',
            'sasaran',
        ])->findOrFail($id);

        // baru cek otorisasinya
        $this->authorizeBidang($induk);

        $riwayat2Tahun = $this->getRiwayat2Tahun($induk);

        return view('realisasi.show', compact('induk', 'riwayat2Tahun'));
    }

    public function updateInduk(Request $request, $id)
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();

        $induk = RealisasiInduk::findOrFail($id);
        $this->authorizeBidang($induk);

        $validated = $request->validate([
            'induk.sasaran_strategis' => 'required|string',
            'induk.program'           => 'required|string',
            'induk.indikator'         => 'required|string',
            'induk.target'            => 'required|string',
            'induk.hambatan'          => 'required|string',
            'induk.rekomendasi'       => 'required|string',
            'induk.tindak_lanjut'     => 'required|string',
            'induk.dokumen'           => 'required|string',
            'induk.strategi'          => 'required|string',
            'induk.alasan'            => 'required|string',
        ]);

        $data         = $validated['induk'];

        $induk->update($data);

        return redirect()->route('realisasi.index')->with('success', 'Data Berhasil Diperbaharui');
    }

    public function destroyInduk($id)
    {
        $this->forbidAdminOnKegiatan();
        // Hapus induk + anak, tapi izin spesifik tetap dikontrol via route (misal middleware superuser)
        $induk = RealisasiInduk::findOrFail($id);

        $this->authorizeBidang($induk);

        $induk->outputs()->delete();
        $induk->outcomes()->delete();
        $induk->sasaran()->delete();
        $induk->keuangans()->delete();
        $induk->keberhasilan()->delete();

        $induk->delete();

        return redirect()->route('realisasi.index')->with('success', 'Data Berhasil Dihapus');
    }

        /**
     * 🔁 Query dasar untuk halaman REKAP (dipakai rekap, export Excel, export PDF)
     */
    private function buildRekapBaseQuery(Request $request)
    {
        $user  = Auth::user();
        $query = RealisasiInduk::with('bidang', 'seksi');

        $bidangs = [];
        $seksis = [];

        $isSuperuser = $user->role === 'superuser';
        $isKadis     = $user->role === 'admin' && empty($user->bidang_id);
        $isKabid     = $user->role === 'admin' && !empty($user->bidang_id);

        $selectedBidangId = $request->input('bidang_id');
        $selectedSeksiId  = $request->input('seksi_id');

        // Superuser: boleh lihat semua bidang + filter dropdown
        if ($isSuperuser || $isKadis) {
            $bidangs = Bidang::orderBy('nama')->get();

           if (!empty($selectedBidangId)) {
            $query->where('bidang_id', $selectedBidangId);
            // seksi difilter per bidang kalau bidang dipilih
            $seksis = Seksi::where('bidang_id', $selectedBidangId)
                ->orderBy('nama')
                ->get();

        } else {
            // semua seksi (opsional, kalau mau)
            $seksis = Seksi::orderBy('nama')->get();
        }
        if ($selectedSeksiId && !$seksis->contains('id', $selectedSeksiId)) {
            $selectedSeksiId = null;
        }

        if (!empty($selectedSeksiId)) {
            $query->where('seksi_id', $selectedSeksiId);
        }
    }
     // ---------- KABID (admin yg punya bidang_id) ----------
    elseif ($isKabid) {
        // kunci ke bidang milik Kabid
        $query->where('bidang_id', $user->bidang_id);

        // untuk dropdown seksi: hanya seksi di bidang dia
        $seksis = Seksi::where('bidang_id', $user->bidang_id)
            ->orderBy('nama')
            ->get();

        if (!empty($selectedSeksiId)) {
            $query->where('seksi_id', $selectedSeksiId);
        }
    }
    // ---------- USER BIASA ----------
    else {
        if ($user->bidang_id) {
            $query->where('bidang_id', $user->bidang_id);
        }
        if ($user->seksi_id) {
            $query->where('seksi_id', $user->seksi_id);
        }
    }

        // Filter tahun (default: tahun sekarang)
        $tahun = $request->input('tahun', date('Y'));
        $query->where('tahun', $tahun);

        // dikembalikan untuk dipakai di fungsi lain
        return [$query, $tahun, $bidangs, $seksis];
    }


    // ==============================
    // 🔸 Fungsi Tambahan (Rekap)
    // ==============================

    public function rekap(Request $request)
    {
         // Pakai helper yang tadi kita buat
        [$query, $tahun, $bidangs, $seksis] = $this->buildRekapBaseQuery($request);

        // Pagination untuk tabel
        $data_induk = $query->orderBy('id', 'desc')->paginate(10);
        $data_induk->appends($request->only('tahun', 'bidang_id', 'seksi_id'));

        return view('realisasi.rekap', compact('data_induk', 'tahun', 'bidangs', 'seksis'));
    }
    public function rekapAnak($id)
    {
        $induk = RealisasiInduk::with([
            'bidang',
            'outputs',
            'outcomes',
            'keuangans',
            'keberhasilan',
            'sasaran',
        ])->findOrFail($id);

        // Batasi sesuai bidang
        $this->authorizeBidang($induk);

        // Kelompokkan per triwulan
        $outputs   = $induk->outputs->groupBy('triwulan');
        $outcomes  = $induk->outcomes->groupBy('triwulan');
        $keuangans = $induk->keuangans->groupBy('triwulan');
        $sasaran = $induk->sasaran instanceof \Illuminate\Support\Collection
        ? $induk->sasaran->first()
        : $induk->sasaran;

        // Sudah boleh download kalau dua disposisi terisi
        $bolehDownload = !empty($induk->disposisi_kabid) && !empty($induk->disposisi_kadis);

        $riwayat2Tahun = $this->getRiwayat2Tahun($induk);

        return view('realisasi.rekapanak', compact(
            'induk',
            'outputs',
            'outcomes',
            'keuangans',
            'sasaran',
            'bolehDownload',
            'riwayat2Tahun'
        ));
    }
    /**
 * Rekap hasil sasaran 2 tahun sebelumnya untuk 1 induk.
 * Dihitung per bidang + seksi + program + indikator yang sama.
 *
 * return [
 *   2023 => ['target' => ..., 'realisasi' => ..., 'capaian' => ...],
 *   2024 => [...],
 * ]
 */
    private function getRiwayat2Tahun(RealisasiInduk $induk): array
    {
        $hasil = [];

        // pastikan tahun berupa integer
        $tahunSekarang = (int) $induk->tahun;
        $tahunList = [
            $tahunSekarang - 2,
            $tahunSekarang - 1,
        ];

        foreach ($tahunList as $th) {
            if ($th <= 0) {
                // kalau aneh (tahun < 0), skip aja
                continue;
            }

            $query = RealisasiInduk::query() -> where('tahun', $th);

            if ($induk->seksi_id){
                $query->where('seksi_id', $induk->seksi_id);
            } elseif ($induk->bidang_id) {
                $query->where('bidang_id', $induk->bidang_id);
            }

            $indukIds = $query->pluck('id');

            if ($indukIds->isEmpty()) {
                $hasil[$th] = [
                    'target'    => 0,
                    'realisasi' => 0,
                    'capaian'   => 0,
                ];
                continue;
            }

            // agregasi dari tabel sasaran
            $agg = RealisasiSasaran::whereIn('induk_id', $indukIds)
                ->selectRaw('COALESCE(SUM(target),0) as total_target, COALESCE(SUM(realisasi),0) as total_realisasi')
                ->first();

            $totalTarget    = (float) ($agg->total_target ?? 0);
            $totalRealisasi = (float) ($agg->total_realisasi ?? 0);

            $capaian        = $totalTarget > 0
                ? round($totalRealisasi / $totalTarget * 100, 2)
                : 0;

            $hasil[$th] = [
                'target'    => $totalTarget,
                'realisasi' => $totalRealisasi,
                'capaian'   => $capaian,
            ];
        }

        return $hasil;
    }



    public function exportPDF($id)
    {

        $induk = RealisasiInduk::with([
            'bidang',
            'seksi',
            'outputs',
            'outcomes',
            'keuangans',
            'keberhasilan',
            'sasaran',
        ])->findOrFail($id);

        $this->authorizeBidang($induk);

        // ❗ Kalau mau kunci hanya bisa download setelah dua disposisi terisi:
        if (empty($induk->disposisi_kabid) || empty($induk->disposisi_kadis)) {
            return redirect()
                ->route('realisasi.rekap.anak', $id)
                ->with('error', 'File hanya bisa diunduh setelah disposisi Kepala Bidang dan Kepala Dinas diisi.');
        }

        $outputs   = $induk->outputs->groupBy('triwulan');
        $outcomes  = $induk->outcomes->groupBy('triwulan');
        $keuangans = $induk->keuangans->groupBy('triwulan');

        $sasaran = $induk->sasaran instanceof \Illuminate\Support\Collection
            ? $induk->sasaran->first()
            : $induk->sasaran;

        $keberhasilan = $induk->keberhasilan;

        $triwulans = ['I', 'II', 'III', 'IV'];

        // 🔹 Rekap 2 tahun sebelumnya (target, realisasi, capaian dari sasaran)
        $riwayat2Tahun = $this->getRiwayat2Tahun($induk);

        // 🔹 Kepala Dinas
        $kepalaDinas = User::whereHas('jabatan', function ($q) {
            $q->where('nama', 'Kepala Dinas');
        })->first();

        // 🔹 Kepala Seksi
        $kepalaSeksi = null;

        // 1) Coba berdasarkan seksi_id kalau ada
        if ($induk->seksi_id) {
            $kepalaSeksi = User::whereHas('jabatan', function ($q) {
                    $q->where('nama', 'Kepala Seksi');
                })
                ->where('seksi_id', $induk->seksi_id)
                ->first();
        }

        // 2) Kalau belum ketemu, coba berdasarkan bidang_id
        if (!$kepalaSeksi && $induk->bidang_id) {
            $kepalaSeksi = User::whereHas('jabatan', function ($q) {
                    $q->where('nama', 'Kepala Seksi');
                })
                ->where('bidang_id', $induk->bidang_id)
                ->first();
        }

        // 3) Kalau masih belum ketemu, pakai user pembuat kalau dia Kepala Seksi
        if (!$kepalaSeksi && $induk->user) {
            if (optional($induk->user->jabatan)->nama === 'Kepala Seksi') {
                $kepalaSeksi = $induk->user;
            }
        }


        // 🔹 Kepala Bidang sesuai bidang induk (opsional)
        $kepalaBidang = null;
        if ($induk->bidang_id) {
            $kepalaBidang = User::whereHas('jabatan', function ($q) {
                    $q->where('nama', 'Kepala Bidang');
                })
                ->where('bidang_id', $induk->bidang_id)
                ->first();
        }

        $pdf = Pdf::loadView('realisasi.rekap_pdf', [
            'induk'        => $induk,
            'outputs'      => $outputs,
            'outcomes'     => $outcomes,
            'keuangans'    => $keuangans,
            'sasaran'      => $sasaran,
            'keberhasilan' => $keberhasilan,
            'triwulans'    => $triwulans,

            'kepalaDinas'  => $kepalaDinas,
            'kepalaSeksi'  => $kepalaSeksi,
            'kepalaBidang' => $kepalaBidang,
            'riwayat2Tahun' => $riwayat2Tahun,

        ])->setPaper('A4', 'portrait');

        $namaFile = 'Laporan-Triwulan-'.$induk->tahun.'-'.$induk->program.'.pdf';

        return $pdf->download($namaFile);
    }

    public function exportExcel(Request $request)
    {
        // Query sama persis dengan halaman rekap
        [$query, $tahun, $bidangs, $seksis] = $this->buildRekapBaseQuery($request);

        // Untuk export kita ambil semua (tanpa paginate)
        $data = $query
            ->orderBy('tahun')
            ->orderBy('bidang_id')
            ->orderBy('id')
            ->get();

        // Nama file: rekap-realisasi-2025.xlsx, dsb
        $namaFile = 'rekap-realisasi-' . $tahun . '.xlsx';
        
        $judulTriwulan = 'TRIWULAN';

        return Excel::download(new RealisasiExport($data, $judulTriwulan), $namaFile);
    }
        // 🔸 Simpan Disposisi Kabid / Kadis
    public function simpanDisposisi(Request $request, $id)
    {
        // ambil induk
        $induk = RealisasiInduk::findOrFail($id);

        // cek hak akses disposisi (Kadis/Kabid saja)
        $this->authorizeDisposisi($induk);

        // validasi isi disposisi
        $validated = $request->validate([
            'disposisi' => 'required|string',
        ], [
            'disposisi.required' => 'Disposisi tidak boleh kosong.',
        ]);

        $user    = auth()->user();
        $jabatan = $user->jabatan->nama ?? null;

        // tentukan kolom mana yang diisi
        if ($jabatan === 'Kepala Bidang') {
            $induk->disposisi_kabid = $validated['disposisi'];
        } elseif ($jabatan === 'Kepala Dinas') {
            $induk->disposisi_kadis = $validated['disposisi'];
        } else {
            // harusnya sudah di-abort di authorizeDisposisi(),
            // tapi jaga-jaga
            abort(403, 'Anda tidak berhak mengisi disposisi.');
        }

        $induk->save();

        return redirect()
            ->route('realisasi.rekap.anak', $induk->id)
            ->with('success', 'Disposisi berhasil disimpan.');
    }


    public function rekapAnakDownload($id) 
    {
        return $this->exportPDF($id);
    }


}
