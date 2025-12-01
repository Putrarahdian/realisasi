<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RealisasiIndukController extends Controller
{
     public function create()
    {
        return view('realisasi_induk.create');
    }

    public function store(Request $request)
    {
        // Akan kita isi nanti, setelah form selesai dibuat
    }
}
