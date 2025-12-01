<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RealisasiInduk;
use App\Models\Bidang;

class HomeController extends Controller
{
    // Pastikan hanya user yang sudah login bisa mengakses
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Method untuk halaman home
    public function index(Request $request)
    {
        $user = Auth::user();
            if ($user && $user->role === 'admin') {
        return redirect()->route('realisasi.rekap');
        }

        $tahunDashboard = $request->input('tahun_dashboard', date('Y'));

        $selectedBidangId = $request->input('bidang_id'); // boleh null = semua
        $bidangs = [];

        $baseQuery = RealisasiInduk::with('seksi')
            -> where('tahun', $tahunDashboard);

        if ($user -> role === 'superuser'){
            $bidangs = Bidang::orderBy('nama')->get();

            if (!empty($selectedBidangId)){ 
            $baseQuery->where('bidang_id', $selectedBidangId);   
                }
        }
        else {
        if ($user->bidang_id) {
            $baseQuery->where('bidang_id', $user->bidang_id);
            $selectedBidangId = $user->bidang_id; // biar bisa ditampilkan di header kalau mau
        }
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

        return view('home',[
            'user'           => $user,
            'tahunDashboard' => $tahunDashboard,
            'triwulans'      => $triwulans,
            'belumDiisi'     => $belumDiisi,
            'selectedBidangId'     => $selectedBidangId,
        ]);
    }
}
