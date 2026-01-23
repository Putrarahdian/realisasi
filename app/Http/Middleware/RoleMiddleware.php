<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Normalisasi roles param (biar aman kalau ada huruf besar/kecil beda)
        $roles = array_map(fn ($r) => strtolower(trim($r)), $roles);

        $userRole    = strtolower((string) $user->role); // superuser/admin/user
        $userJabatan = strtolower((string) optional($user->jabatan)->jenis_jabatan); // kepala_seksi/kasubag_keuangan/dll

        // ✅ Boleh lewat kalau:
        // - role enum match (superuser/admin/user)
        // - ATAU jabatan match (kepala_seksi/kasubag_keuangan)
        $allowed = in_array($userRole, $roles, true) || in_array($userJabatan, $roles, true);

        if (!$allowed) {
            abort(403, 'Akses Ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        // ====== FILTER OTOMATIS ======
        $isSuperuser = ($userRole === 'superuser');
        $isKasubag   = ($userJabatan === 'kasubag_keuangan');

        // Kalau user PUNYA bidang+seksi → batasi, kecuali superuser & kasubag
        $punyaBidangSeksi = !empty($user->bidang_id) && !empty($user->seksi_id);

        if (!$isSuperuser && !$isKasubag && $punyaBidangSeksi) {
            $request->merge([
                'filter_bidang_id' => $user->bidang_id,
                'filter_seksi_id'  => $user->seksi_id,
            ]);
        }

        // Kalau user TIDAK punya bidang/seksi → biarkan tanpa filter (bisa lihat semua)

        return $next($request);
    }
}
