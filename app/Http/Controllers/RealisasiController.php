<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RealisasiExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'keuangan' => 'required|array',
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
            abort(403, 'Kepala Bidang dan Kepala Dinas hanya dapat mengakses halaman rekap.');
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
        if (empty($user->bidang_id) || $induk->bidang_id !== $user->bidang_id) {
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
        $tanggalDari   = $request->input('tanggal_dari');
        $tanggalSampai = $request->input('tanggal_sampai');


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

        if ($tanggalDari && $tanggalSampai) {
            $query->whereBetween('tanggal', [$tanggalDari, $tanggalSampai]);
        } elseif ($tanggalDari) {
            $query->whereDate('tanggal', '>=', $tanggalDari);
        } elseif ($tanggalSampai) {
            $query->whereDate('tanggal', '<=', $tanggalSampai);
        }


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
        $data_induk->appends($request->only('search', 'tanggal_dari', 'tanggal_sampai',  'bidang_id', 'seksi_id'));

        
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

        return view('realisasi_induk.show', compact('data_induk', 'tanggalDari', 'tanggalSampai', 'seksis', 'bidangs','user') + [
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

        $tahun = (int) $request->input('tahun', date('Y'));

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
        ])

        ->where('tahun', $tahun);

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

        // filter tanggal
        $tanggalDari   = $request->input('tanggal_dari');
        $tanggalSampai = $request->input('tanggal_sampai');

        if ($tanggalDari && $tanggalSampai) {
            $query->whereBetween('tanggal', [$tanggalDari, $tanggalSampai]);
        } elseif ($tanggalDari) {
            $query->whereDate('tanggal', '>=', $tanggalDari);
        } elseif ($tanggalSampai) {
            $query->whereDate('tanggal', '<=', $tanggalSampai);
        }

        // ambil data
        $data_induk = $query->orderBy('id', 'desc')->paginate(10);
        $data_induk->appends($request->only('tanggal_dari', 'tanggal_sampai', 'bidang_id', 'tahun'));
        
        return view('triwulan.index', compact(
            'tw',
            'tahun',
            'no',
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

        $induk = RealisasiInduk::findOrFail($indukId);
        $this->authorizeBidang($induk);

        $sasaran = RealisasiSasaran::where('induk_id', $indukId)->first();

        $keuangan = RealisasiKeuangan::where('induk_id', $indukId)
            ->where('triwulan', $kodeTriwulan)
            ->first();

        $twt = null;
        if ((int)$no > 1) {
            $prevIndex = (int)$no - 2;                 
            $tws = self::TRIWULAN[$prevIndex] ?? 'I';

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
            'keuangan'     => $keuangan,
            'keberhasilan' => null,
            'kodeTriwulan' => $kodeTriwulan,
            'no'           => $no,
            'twt'          => $twt,
            'sasaran'      => $sasaran,
        ]);
    }

    public function storeByTriwulan($no, RealisasiInduk $induk, Request $request)
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();

        // konversi 1–4 ke I–IV pakai constant
        $index        = (int) $no - 1;
        $kodeTriwulan = self::TRIWULAN[$index] ?? abort(404, 'Triwulan tidak valid');

        // ✅ KASUBAG KEUANGAN: hanya boleh simpan data keuangan
        if ($this->isKasubagKeuangan()) {
            $this->validateKeuangan($request);

            $indukId = $induk->id;
            $this->authorizeBidang($induk);

            $rowKeu = RealisasiKeuangan::where('induk_id', $indukId)
                ->where('triwulan', $kodeTriwulan)
                ->first();

            if (!$rowKeu || $rowKeu->target <= 0) {
                return redirect()
                    ->route('realisasi.triwulan.index', $no)
                    ->with('error', "Target Keuangan Triwulan {$kodeTriwulan} belum diisi Seksi.");
            }

            $k = $request->input('keuangan', []);
            $realisasiK = (float) ($k['realisasi'] ?? 0);

            $rowKeu->update([
                'user_id'   => auth()->id(),
                'realisasi' => $realisasiK,
                'capaian'   => 0,
            ]);

            $this->hitungUlangCapaianKeuangan($indukId);

            return redirect()
                ->route('realisasi.triwulan.index', $no)
                ->with('success', "Data Keuangan Triwulan {$kodeTriwulan} berhasil disimpan.");
        }

        // ===================== VALIDASI =====================
        $rules = [
            'keuangan' => 'required|array',

            // OUTPUT
            'output.uraian'    => 'required|string',
            'output.target'    => 'required|numeric|min:0',
            'output.realisasi' => 'required|numeric|min:0',

            // OUTCOME
            'outcome.uraian'    => 'required|string',
            'outcome.target'    => 'required|numeric|min:0',
            'outcome.realisasi' => 'required|numeric|min:0',

            // KEUANGAN (selalu wajib tiap TW)
            'keuangan.target' => 'required|numeric|min:0',

            // SASARAN: tiap triwulan wajib target & realisasi
            'sasaran.target'    => 'required|numeric|min:0',
            'sasaran.realisasi' => 'required|numeric|min:0',
        ];

        if ((int)$no === 1) {
            $rules['sasaran.uraian'] = 'required|string';
        } else {
            $sasaranRowCheck = RealisasiSasaran::where('induk_id', $induk->id)->first();
            if (!$sasaranRowCheck || trim((string)($sasaranRowCheck->uraian ?? '')) === '') {
                return redirect()->back()->withInput()
                    ->with('error', 'Uraian Sasaran harus diisi pada Triwulan I terlebih dahulu.');
            }
        }

        $request->validate($rules);

        $indukId = $induk->id;
        $this->authorizeBidang($induk);

        // ===================== OUTPUT =====================
        $o = $request->input('output', []);
        $targetO    = (float) ($o['target'] ?? 0);
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

        // ===================== OUTCOME =====================
        $oc = $request->input('outcome', []);
        $targetOc    = (float) ($oc['target'] ?? 0);
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

        // ===================== SASARAN =====================
        $s = $request->input('sasaran', []);

        $sasaran = RealisasiSasaran::firstOrCreate(
            ['induk_id' => $indukId],
            ['user_id' => auth()->id(), 'uraian' => null]
        );

        // uraian hanya boleh diisi di TW1
        if ((int)$no === 1 && !empty(trim($s['uraian'] ?? ''))) {
            $sasaran->uraian = $s['uraian'];
        }

        // simpan nilai per triwulan
        $sasaran->{"target_tw{$no}"}    = (float)($s['target'] ?? 0);
        $sasaran->{"realisasi_tw{$no}"} = (float)($s['realisasi'] ?? 0);
        $sasaran->user_id = auth()->id();
        $sasaran->save();

        // hitung ulang TOTAL otomatis
        $this->recalcSasaranTotal($indukId);

        // ===================== KEUANGAN =====================
        $k = $request->input('keuangan', []);
        $targetK = (float) ($k['target'] ?? 0);

        $existingKeu = RealisasiKeuangan::where('induk_id', $indukId)
            ->where('triwulan', $kodeTriwulan)
            ->first();

        RealisasiKeuangan::updateOrCreate(
            ['induk_id' => $indukId, 'triwulan' => $kodeTriwulan],
            [
                'user_id'   => auth()->id(),
                'target'    => $targetK,
                'realisasi' => $existingKeu?->realisasi,
                'capaian'   => 0,
            ]
        );

        $this->hitungUlangCapaianKeuangan($induk->id);

        // ===================== KEBERHASILAN / HAMBATAN PER TRIWULAN =====================
        $teksKeberhasilan = trim(strip_tags($request->input('keberhasilan_triwulan', '')));
        $teksHambatan     = trim(strip_tags($request->input('hambatan_triwulan', '')));

        if ($teksKeberhasilan !== '' || $teksHambatan !== '') {
            $rekap = RealisasiKeberhasilan::firstOrCreate(
                ['induk_id' => $indukId],
                [
                    'user_id'      => auth()->id(),
                    'keberhasilan' => '',
                    'hambatan'     => '',
                ]
            );

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

    public function storeInduk(Request $request)
    {
        $this->forbidAdminOnKegiatan();
        $this->authorizeEditData();

        $rules = [
            'induk.tanggal'           => 'required|date',
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
        $tanggal   = $data['tanggal'];
        $tahun     = \Carbon\Carbon::parse($tanggal)->year;

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
            'tanggal'           => $tanggal,
            'tahun'             => $tahun,
            'bidang_id'         => $bidangId,
            'seksi_id'          => $seksiId,
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
        $informasi = null;

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
        $informasi = null;

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

        // ✅ KASUBAG KEUANGAN: hanya update realisasi keuangan
        if ($this->isKasubagKeuangan()) {
            foreach (['I','II','III','IV'] as $tw) {
                $k = $request->input("keuangan.$tw");
                if (!is_array($k)) continue;

                $rowKeu = RealisasiKeuangan::where('induk_id', $indukId)
                    ->where('triwulan', $tw)
                    ->first();

                // kalau target belum ada, skip
                if (!$rowKeu || (float)$rowKeu->target <= 0) continue;

                $rowKeu->update([
                    'user_id'   => auth()->id(),
                    'realisasi' => (float)($k['realisasi'] ?? 0),
                    'capaian'   => 0,
                ]);
            }

            $this->hitungUlangCapaianKeuangan($indukId);

            return redirect()->route('realisasi.show', $indukId)
                ->with('success', 'Data keuangan berhasil diperbarui.');
        }

        // ===================== UPDATE OUTPUT/OUTCOME/KEUANGAN =====================
        foreach (['I', 'II', 'III', 'IV'] as $tw) {

            // OUTPUT
            $o = $request->input("output.$tw");
            if (is_array($o)) {
                $targetO    = (float) ($o['target'] ?? 0);
                $realisasiO = (float) ($o['realisasi'] ?? 0);

                if ($targetO <= 0) {
                    $targetO = 0;
                    $realisasiO = 0;
                    $capaianO = 0;
                } else {
                    $capaianO = round($realisasiO / $targetO * 100, 2);
                }

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

            // OUTCOME
            $oc = $request->input("outcome.$tw");
            if (is_array($oc)) {
                $targetOc    = (float) ($oc['target'] ?? 0);
                $realisasiOc = (float) ($oc['realisasi'] ?? 0);

                if ($targetOc <= 0) {
                    $targetOc = 0;
                    $realisasiOc = 0;
                    $capaianOc = 0;
                } else {
                    $capaianOc = round($realisasiOc / $targetOc * 100, 2);
                }

                RealisasiOutcome::updateOrCreate(
                    ['induk_id' => $indukId, 'triwulan' => $tw],
                    [
                        'user_id'   => auth()->id(),
                        'uraian'    => $oc['uraian'] ?? '-',
                        'target'    => $targetOc,
                        'realisasi' => $realisasiOc,
                        'capaian'   => $capaianOc,
                    ]
                );
            }

            // KEUANGAN (target boleh diubah oleh seksi, realisasi dikunci dari DB)
            $k = $request->input("keuangan.$tw");
            if (is_array($k)) {
                $targetK = (float) ($k['target'] ?? 0);

                $existing = RealisasiKeuangan::where('induk_id', $indukId)
                    ->where('triwulan', $tw)
                    ->first();

                RealisasiKeuangan::updateOrCreate(
                    ['induk_id' => $indukId, 'triwulan' => $tw],
                    [
                        'user_id'   => auth()->id(),
                        'target'    => $targetK,
                        'realisasi' => $existing?->realisasi, // tetap dikunci
                        'capaian'   => 0,
                    ]
                );
            }
        }

        $this->hitungUlangCapaianKeuangan($indukId);

        // ===================== SASARAN =====================
        $s = $request->input('sasaran');
        if (is_array($s)) {

            $request->validate([
                'sasaran.uraian'        => 'nullable|string',
                'sasaran.target_tw1'    => 'nullable|numeric|min:0',
                'sasaran.target_tw2'    => 'nullable|numeric|min:0',
                'sasaran.target_tw3'    => 'nullable|numeric|min:0',
                'sasaran.target_tw4'    => 'nullable|numeric|min:0',
                'sasaran.realisasi_tw1' => 'nullable|numeric|min:0',
                'sasaran.realisasi_tw2' => 'nullable|numeric|min:0',
                'sasaran.realisasi_tw3' => 'nullable|numeric|min:0',
                'sasaran.realisasi_tw4' => 'nullable|numeric|min:0',
            ]);

            $sasaran = RealisasiSasaran::firstOrCreate(
                ['induk_id' => $indukId],
                ['user_id' => auth()->id(), 'uraian' => null]
            );

            if (array_key_exists('uraian', $s)) {
                $val = trim((string) ($s['uraian'] ?? ''));
                if ($val !== '') {
                    $sasaran->uraian = $val;
                }
            }

            foreach (['1','2','3','4'] as $n) {
                $tk = "target_tw{$n}";
                $rk = "realisasi_tw{$n}";

                if (array_key_exists($tk, $s)) {
                    $sasaran->{$tk} = (float) ($s[$tk] ?? 0);
                }
                if (array_key_exists($rk, $s)) {
                    $sasaran->{$rk} = (float) ($s[$rk] ?? 0);
                }
            }

            $sasaran->user_id = auth()->id();
            $sasaran->save();

            $this->recalcSasaranTotal($indukId);
        }

        // ===================== KEBERHASILAN =====================
        $keb = $request->input('keberhasilan', []);
        $clean = fn($v) => $v === null ? null : trim(strip_tags($v));

        RealisasiKeberhasilan::updateOrCreate(
            ['induk_id' => $indukId],
            [
                'user_id'          => auth()->id(),
                'keberhasilan_tw1' => $clean($keb['keberhasilan_tw1'] ?? null),
                'keberhasilan_tw2' => $clean($keb['keberhasilan_tw2'] ?? null),
                'keberhasilan_tw3' => $clean($keb['keberhasilan_tw3'] ?? null),
                'keberhasilan_tw4' => $clean($keb['keberhasilan_tw4'] ?? null),
                'hambatan_tw1'     => $clean($keb['hambatan_tw1'] ?? null),
                'hambatan_tw2'     => $clean($keb['hambatan_tw2'] ?? null),
                'hambatan_tw3'     => $clean($keb['hambatan_tw3'] ?? null),
                'hambatan_tw4'     => $clean($keb['hambatan_tw4'] ?? null),
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
            'induk.tanggal'           => 'required|date',
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
        $induk->update($validated['induk']);

        return redirect()->route('realisasi.index')->with('success', 'Data Berhasil Diperbaharui');
    }

    public function destroyInduk($id)
    {
        $this->forbidAdminOnKegiatan();
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
        $isKasubagKeu = $this->isKasubagKeuangan();

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
        if (!$isKasubagKeu) {
            if ($user->bidang_id) {
                $query->where('bidang_id', $user->bidang_id);
            }
            if ($user->seksi_id) {
                $query->where('seksi_id', $user->seksi_id);
            }
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

        return view('rekap.rekap', compact('data_induk', 'tahun', 'bidangs', 'seksis'));
    }

    public function rekapAnak(RealisasiInduk $induk)
    {
        $induk->load([
            'bidang',
            'outputs',
            'outcomes',
            'keuangans',
            'keberhasilan',
            'sasaran',
        ]);

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

        return view('rekap.rekapanak', compact(
            'induk',
            'outputs',
            'outcomes',
            'keuangans',
            'sasaran',
            'bolehDownload',
            'riwayat2Tahun'
        ));
    }

    private function getRiwayat2Tahun(RealisasiInduk $induk): array
    {
        $hasil = [];

        $tahunSekarang = (int) $induk->tahun;
        $tahunList = [
            $tahunSekarang - 2,
            $tahunSekarang - 1,
        ];

        foreach ($tahunList as $th) {
            if ($th <= 0) {
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

    private function recalcSasaranTotal(int $indukId): void
    {
        $row = RealisasiSasaran::where('induk_id', $indukId)->first();
        if (!$row) return;

        $t1 = (float) ($row->target_tw1 ?? 0);
        $t2 = (float) ($row->target_tw2 ?? 0);
        $t3 = (float) ($row->target_tw3 ?? 0);
        $t4 = (float) ($row->target_tw4 ?? 0);

        $r1 = (float) ($row->realisasi_tw1 ?? 0);
        $r2 = (float) ($row->realisasi_tw2 ?? 0);
        $r3 = (float) ($row->realisasi_tw3 ?? 0);
        $r4 = (float) ($row->realisasi_tw4 ?? 0);

        $totalTarget    = $t1 + $t2 + $t3 + $t4;
        $totalRealisasi = $r1 + $r2 + $r3 + $r4;

        $capaian = $totalTarget > 0
            ? round(($totalRealisasi / $totalTarget) * 100, 2)
            : 0;

        // ✅ set manual biar tidak ke-block fillable
        $row->target    = $totalTarget;
        $row->realisasi = $totalRealisasi;
        $row->capaian   = $capaian;

        $row->save();
    }

    public function exportPDF(RealisasiInduk $induk)
    {
        $induk->load([
            'bidang',
            'seksi',
            'outputs',
            'outcomes',
            'keuangans',
            'keberhasilan',
            'sasaran',
        ]);

        $this->authorizeBidang($induk);

        // Kalau mau kunci hanya bisa download setelah dua disposisi terisi:
        if (empty($induk->disposisi_kabid) || empty($induk->disposisi_kadis)) {
            return redirect()
                ->route('rekap.anak', $induk->id)
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

        $pdf = Pdf::loadView('rekap.rekap_pdf', [
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
    public function simpanDisposisi(Request $request, RealisasiInduk $induk)
    {
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
            abort(403, 'Anda tidak berhak mengisi disposisi.');
        }

        $induk->save();

        return redirect()
            ->route('rekap.anak', $induk->id)
            ->with('success', 'Disposisi berhasil disimpan.');
    }


    public function rekapAnakDownload(RealisasiInduk $induk) 
    {
        return $this->exportPDF($induk);
    }


}
