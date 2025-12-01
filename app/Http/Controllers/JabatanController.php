<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jabatan;

class JabatanController extends Controller
{
     // 🟢 Kepala Dinas — hanya 1, tidak bisa tambah/hapus
    public function kepalaDinas()
    {
        $jabatan = Jabatan::where('nama', 'Kepala Dinas')->first();
        return view('jabatan.kepala_dinas', compact('jabatan'));
    }

    // 🟠 Kepala Bidang — maksimal 3, bisa edit tapi tidak hapus
    public function kepalaBidang()
    {
        $jabatans = Jabatan::where('nama', 'like', 'Kepala Bidang%')->get();
        return view('jabatan.kepala_bidang', compact('jabatans'));
    }

    // 🔵 Kepala Seksi — bisa tambah, edit, hapus
    public function kepalaSeksi()
    {
        $jabatans = Jabatan::where('nama', 'like', 'Kepala Seksi%')->get();
        return view('jabatan.kepala_seksi', compact('jabatans'));
    }
}
