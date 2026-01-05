<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Jabatan;
use App\Models\Seksi;
use App\Models\Bidang;
use Illuminate\Validation\Rules\Password;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;


class AdminController extends Controller
{
    private function isMasterSuperuser(User $user): bool
    {
        // Asumsi: akun superuser utama id=1.
        // Kalau kamu pakai email tertentu, ganti jadi: $user->email === 'admin@example.com'
        return $user->id === 1 && $user->role === 'superuser';
    }
    private function onlySuperuser(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'superuser') {
            abort(403, 'Hanya superuser yang boleh mengelola data pengguna.');
        }
    }
    
    public function exportUsersExcel(Request $request)
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'superuser'])) {
            abort(403);
        }

        $search = $request->input('search');

        $filename = 'users_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new UsersExport(auth()->user(), $search), $filename);
    }


    // 📋 Tampilkan semua user
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = User::query()
        ->leftjoin('jabatans', 'users.jabatan_id', '=', 'jabatans.id')
        ->leftjoin('bidangs', 'users.bidang_id', '=', 'bidangs.id')
        ->leftjoin('seksis', 'users.seksi_id', '=', 'seksis.id')
        ->with(['jabatan', 'bidang', 'seksi'])
        ->select('users.*');

        if (auth()->user()->role === 'admin') {
            $query->where('users.bidang_id', auth()->user()->bidang_id);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('users.nip', 'like', "%{$search}%")
                  ->orWhere(function($sub) use ($search) {
                      $sub->whereNotNull('jabatans.id')
                          ->where('jabatans.nama', 'like', "%{$search}%");
                  })
                  ->orWhere(function($sub) use ($search) {
                      $sub->whereNotNull('bidangs.id')
                          ->where('bidangs.nama', 'like', "%{$search}%");
                  })
                  ->orWhere(function($sub) use ($search) {
                      $sub->whereNotNull('seksis.id')
                          ->where('seksis.nama', 'like', "%{$search}%");
                  });
            });
        }

        // Urutan prioritas jabatan dan role
        $users = $query
        ->orderByRaw("
                FIELD(users.role, 'superuser', 'admin', 'user'),
                FIELD(jabatans.nama, 
                    'Kepala Dinas',
                    'Sekretaris',
                    'Kepala Bidang',
                    'Kepala Seksi',
                    'Kasubag Umum dan Kepegawaian'
                )
            ")
            ->orderBy('users.name', 'asc')
            ->paginate(10)
            ->appends(['search' => $search]);

        return view('admin.menu', compact('users', 'search'));
    }

    // 🧩 Form tambah user
    public function create()
    {
        $this->onlySuperuser();
        $user = auth()->user();
        $jabatans = Jabatan::orderBy('nama')->get();

        if ($user->role === 'superuser') {
            $bidangs = Bidang::orderBy('nama')->get();
            $seksis = Seksi::orderBy('nama')->get();
        } else {
            $bidangs = Bidang::where('id', $user->bidang_id)->get();
            $seksis = Seksi::where('bidang_id', $user->bidang_id)->orderBy('nama')->get();
        }

        return view('admin.create', compact('jabatans', 'bidangs', 'seksis'));
    }

    // ➕ Tambah user baru
    public function store(Request $request)
    {
        $this->onlySuperuser();
        $messages = [
            'password.min'    => 'Password minimal 6 karakter.',
            'password.mixed'  => 'Password harus mengandung huruf besar dan kecil.',
            'password.letters'=> 'Password harus mengandung minimal satu huruf.',
            'password.numbers'=> 'Password harus mengandung angka.',
            'password.symbols'=> 'Password harus mengandung simbol.',
        ];

        // normalisasi kosong => null (biar validasi & save rapi)
        $request->merge([
            'bidang_id'  => $request->input('bidang_id') ?: null,
            'seksi_id'   => $request->input('seksi_id') ?: null,
            'jabatan_id' => $request->input('jabatan_id') ?: null,
        ]);

        // ✅ Validasi dasar
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => ['required', Password::defaults()],
            'role'     => 'required|in:user,admin,superuser',
            'nip'      => 'required|string|max:20|unique:users,nip',

            // default: jabatan wajib (admin utama bukan lewat store biasanya, tapi tetap aman)
            'jabatan_id' => 'required|exists:jabatans,id',
        ], $messages);

        // Admin hanya bisa menambah user dalam bidangnya
        if (auth()->user()->role === 'admin') {
            $request->merge(['bidang_id' => auth()->user()->bidang_id]);
        }

        $jabatanModel = Jabatan::find($request->jabatan_id);
        $jabatanNama  = $jabatanModel?->nama;
        $jenis        = $jabatanModel?->jenis_jabatan;

        // ====== ATURAN BIDANG/SEKSI ======
        // Kadis/Sekretaris: tidak pakai bidang/seksi
        if (in_array($jabatanNama, ['Kepala Dinas', 'Sekretaris'])) {
            $request->merge(['bidang_id' => null, 'seksi_id' => null]);
        }
        // Kasubag Keuangan: tidak pakai bidang/seksi
        elseif ($jenis === 'kasubag_keuangan') {
            $request->merge(['bidang_id' => null, 'seksi_id' => null]);
        }
        // Kepala Bidang: wajib bidang, seksi null
        elseif ($jabatanNama === 'Kepala Bidang') {
            if (!$request->bidang_id) {
                return back()->with('error', 'Kepala Bidang harus memilih bidang.');
            }
            $request->merge(['seksi_id' => null]);
        }
        // Selain itu: wajib bidang & seksi
        else {
            if (!$request->bidang_id || !$request->seksi_id) {
                return back()->with('error', 'Bidang dan seksi wajib diisi untuk jabatan ini.');
            }
        }

        // ====== BATASAN UNIK JABATAN (punyamu) ======
        // Kepala Dinas (maks 1 orang)
        if ($jabatanNama === 'Kepala Dinas') {
            $existing = User::whereHas('jabatan', fn($q) => $q->where('nama', 'Kepala Dinas'))->count();
            if ($existing >= 1) {
                return back()->with('error', 'Hanya boleh ada 1 Kepala Dinas.');
            }
            $request->merge(['bidang_id' => null, 'seksi_id' => null]);
        }

        // Sekretaris (maks 1 orang)
        elseif ($jabatanNama === 'Sekretaris') {
            $existing = User::whereHas('jabatan', fn($q) => $q->where('nama', 'Sekretaris'))->count();
            if ($existing >= 1) {
                return back()->with('error', 'Hanya boleh ada 1 Sekretaris.');
            }
            $request->merge(['bidang_id' => null, 'seksi_id' => null]);
        }

        // Kepala Bidang (maks 1 per bidang)
        elseif ($jabatanNama === 'Kepala Bidang') {
            $cekBidang = User::where('bidang_id', $request->bidang_id)
                ->whereHas('jabatan', fn($q) => $q->where('nama', 'Kepala Bidang'))
                ->count();

            if ($cekBidang > 0) {
                return back()->with('error', 'Setiap bidang hanya boleh memiliki satu Kepala Bidang.');
            }
        }

        // Kepala Seksi (wajib bidang & seksi)
        elseif ($jabatanNama === 'Kepala Seksi') {
            if (!$request->bidang_id || !$request->seksi_id) {
                return back()->with('error', 'Kepala Seksi harus memilih bidang dan seksi.');
            }
        }

        // ✅ Simpan user baru
        User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'role'      => $request->role,
            'nip'       => $request->nip,
            'jabatan_id'=> $request->jabatan_id,
            'bidang_id' => $request->bidang_id,
            'seksi_id'  => $request->seksi_id,
            'is_locked' => in_array($jabatanNama, ['Kepala Dinas', 'Sekretaris', 'Kepala Bidang']) ? 1 : 0,
        ]);

        return redirect()->route('admin.menu')->with('success', 'Data pengguna berhasil ditambahkan!');
    }

    // ✏️ Form edit
    public function edit(User $user)
    {
        $current = auth()->user();
        $jabatans = Jabatan::orderBy('nama')->get();
        $user->load(['jabatan', 'bidang', 'seksi']);

        if ($current->role === 'superuser') {
            $bidangs = Bidang::orderBy('nama')->get();
            $seksis = Seksi::orderBy('nama')->get();
        } else {
            $bidangs = Bidang::where('id', $current->bidang_id)->get();
            $seksis = Seksi::where('bidang_id', $current->bidang_id)->orderBy('nama')->get();
        }

        return view('admin.edit', compact('user', 'jabatans', 'bidangs', 'seksis'));
    }

    public function update(Request $request, User $user)
    {
        $this->onlySuperuser();

        $messages = [
            'password.min'    => 'Password minimal 6 karakter.',
            'password.mixed'  => 'Password harus mengandung huruf besar dan kecil.',
            'password.letters'=> 'Password harus mengandung minimal satu huruf.',
            'password.numbers'=> 'Password harus mengandung angka.',
            'password.symbols'=> 'Password harus mengandung simbol.',
        ];

        // normalisasi kosong => null
        $request->merge([
            'jabatan_id' => $request->input('jabatan_id') ?: null,
            'bidang_id'  => $request->input('bidang_id') ?: null,
            'seksi_id'   => $request->input('seksi_id') ?: null,
        ]);

        $current   = auth()->user();
        $isMaster  = ($user->id === 1 && $user->role === 'superuser');

        // ✅ Validasi dasar
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'role'     => 'required|in:user,admin,superuser',
            'password' => ['nullable', Password::defaults()],
            'nip'      => 'required|string|max:20|unique:users,nip,' . $user->id,

            // ✅ master superuser boleh NULL jabatan
            'jabatan_id' => $isMaster ? 'nullable' : 'required|exists:jabatans,id',
        ], $messages);

        // ✅ ADMIN UTAMA: tidak pakai jabatan/bidang/seksi
        if ($isMaster) {
            $request->merge([
                'jabatan_id' => null,
                'bidang_id'  => null,
                'seksi_id'   => null,
            ]);
        } else {
            $jabatanModel = Jabatan::find($request->jabatan_id);
            $jabatanNama  = $jabatanModel?->nama;
            $jenis        = $jabatanModel?->jenis_jabatan;

            // ====== ATURAN BIDANG/SEKSI ======
            if (in_array($jabatanNama, ['Kepala Dinas', 'Sekretaris'])) {
                $request->merge(['bidang_id' => null, 'seksi_id' => null]);
            }
            elseif ($jenis === 'kasubag_keuangan') {
                $request->merge(['bidang_id' => null, 'seksi_id' => null]);
            }
            elseif ($jabatanNama === 'Kepala Bidang') {
                if (!$request->bidang_id) {
                    return back()->with('error', 'Kepala Bidang harus memilih bidang.');
                }
                $request->merge(['seksi_id' => null]);
            }
            else {
                if (!$request->bidang_id || !$request->seksi_id) {
                    return back()->with('error', 'Bidang dan seksi wajib diisi untuk jabatan ini.');
                }
            }

            // ====== BATASAN UNIK JABATAN (seperti punyamu) ======
            if ($jabatanNama === 'Kepala Dinas') {
                $existing = User::whereHas('jabatan', fn($q) => $q->where('nama', 'Kepala Dinas'))
                    ->where('id', '!=', $user->id)
                    ->count();
                if ($existing >= 1) {
                    return back()->with('error', 'Hanya boleh ada 1 Kepala Dinas.');
                }
                $request->merge(['bidang_id' => null, 'seksi_id' => null]);
            }
            elseif ($jabatanNama === 'Sekretaris') {
                $existing = User::whereHas('jabatan', fn($q) => $q->where('nama', 'Sekretaris'))
                    ->where('id', '!=', $user->id)
                    ->count();
                if ($existing >= 1) {
                    return back()->with('error', 'Hanya boleh ada 1 Sekretaris.');
                }
                $request->merge(['bidang_id' => null, 'seksi_id' => null]);
            }
            elseif ($jabatanNama === 'Kepala Bidang') {
                $cekBidang = User::where('bidang_id', $request->bidang_id)
                    ->whereHas('jabatan', fn($q) => $q->where('nama', 'Kepala Bidang'))
                    ->where('id', '!=', $user->id)
                    ->count();

                if ($cekBidang > 0) {
                    return back()->with('error', 'Setiap bidang hanya boleh memiliki satu Kepala Bidang.');
                }
            }
            elseif ($jabatanNama === 'Kepala Seksi') {
                if (!$request->bidang_id || !$request->seksi_id) {
                    return back()->with('error', 'Kepala Seksi harus memilih bidang dan seksi.');
                }
            }
        }

        // (punyamu) admin tidak boleh ubah role user lain
        if ($current->role === 'admin' && $user->role !== $request->role) {
            $request->merge(['role' => $user->role]);
        }

        // 🚫 Cegah superuser menurunkan role sendiri
        if (auth()->id() === $user->id && $request->role !== 'superuser') {
            return back()->with('error', 'Anda tidak dapat menurunkan role Anda sendiri dari superuser.');
        }

        // ✅ Simpan perubahan
        $user->update([
            'name'      => $request->name,
            'email'     => $request->email,
            'role'      => $request->role,
            'nip'       => $request->nip,
            'jabatan_id'=> $request->jabatan_id,
            'bidang_id' => $request->bidang_id,
            'seksi_id'  => $request->seksi_id,
            'password'  => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        return redirect()->route('admin.menu')->with('success', 'Data user berhasil diperbarui!');
    }

    // 🗑️ Hapus user
    public function destroy(User $user)
    {
        $this->onlySuperuser();

        if (auth()->id() === $user->id)
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        
        if ($this->isMasterSuperuser($user)) {
            return back()->with('error', 'Akun superuser utama tidak dapat dihapus.');
        }
        if ($user->is_locked || in_array($user->jabatan?->nama, ['Kepala Dinas', 'Kepala Bidang'])) {
            return back()->with('error', 'Akun Kepala Dinas dan Kepala Bidang tidak dapat dihapus.');
        }

            $user->delete();
            return back()->with('success', 'User berhasil dihapus!');
        }

    // 🔁 AJAX ambil seksi berdasarkan bidang
    public function getSeksi($bidang_id)
    {
        $seksis = $bidang_id == 0
            ? Seksi::all()
            : Seksi::where('bidang_id', $bidang_id)->get();

        return response()->json($seksis);
    }
}
