<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\TargetController;

// ===========================
// 🌐 LANDING (publik)
// ===========================
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('home'); // /home
    }
    return view('home'); // resources/views/home.blade.php (landing)
})->name('landing');


// ===========================
// 🔒 GUEST (belum login)
// ===========================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// ===========================
// 🔐 AUTH (sudah login)
// ===========================
Route::middleware('auth')->group(function () {

    // 🔸 Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // 🏠 Halaman utama
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // ============================================================
    // 📊 REKAP & EXPORT (dapat diakses semua role)
    // ============================================================
    Route::prefix('realisasi')->group(function () {
        Route::get('/rekap', [RealisasiController::class, 'rekap'])->name('realisasi.rekap');
        Route::get('/{induk}/pdf', [RealisasiController::class, 'exportPDF'])->whereNumber('induk')->name('realisasi.rekap.pdf');
        Route::get('/rekap/excel', [RealisasiController::class, 'exportExcel'])->name('realisasi.rekap.excel');

        // rekap anak
        Route::get('/rekap/anak/{induk}', [RealisasiController::class, 'rekapAnak'])
            ->name('rekap.anak');
        Route::get('/rekap/anak/{induk}/download', [RealisasiController::class, 'rekapAnakDownload'])
            ->name('rekap.anak.download');
        Route::post('/rekap/anak/{induk}/disposisi', [RealisasiController::class, 'simpanDisposisi'])
            ->name('rekap.anak.disposisi');

        Route::get('/admin/users/export-excel', [AdminController::class, 'exportUsersExcel'])
            ->name('admin.users.export.excel');

    });

    // ============================================================
    // 📆 REALISASI PER TRIWULAN (dibagi per tahun & bidang)
    // ============================================================
    Route::prefix('realisasi')->group(function () {
        Route::get('/triwulan/{no}', [RealisasiController::class, 'byTriwulan'])
            ->name('realisasi.triwulan.index');
        Route::get('/triwulan/{no}/{induk}/create', [RealisasiController::class, 'createByTriwulan'])
            ->name('realisasi.triwulan.create');

        // SIMPAN DATA (POST)
        Route::post('/triwulan/{no}/{induk}', [RealisasiController::class, 'storeByTriwulan'])
            ->name('realisasi.triwulan.store');
            });

    // 📊 REALISASI CRUD (utama)
    Route::post('/realisasi', [RealisasiController::class, 'store'])->name('realisasi.store');

    Route::resource('realisasi', RealisasiController::class)->except(['destroy']);

    // 📑 LAPORAN INDUK
    Route::get('/realisasi-induk/create',    [RealisasiController::class, 'createInduk'])->name('realisasi-induk.create');
    Route::post('/realisasi-induk',          [RealisasiController::class, 'storeInduk'])->name('realisasi-induk.store');
    Route::get('/realisasi-induk/{id}/edit', [RealisasiController::class, 'editInduk'])->name('realisasi-induk.edit');
    Route::put('/realisasi-induk/{id}',      [RealisasiController::class, 'updateInduk'])->name('realisasi-induk.update');
    Route::delete('/realisasi-induk/{id}',   [RealisasiController::class, 'destroyInduk'])->name('realisasi-induk.destroy');

    // 🧑‍💼 MENU ADMINISTRASI HANYA SUPERUSER
    Route::middleware('role:superuser')->group(function () {

        Route::get('/admin/menu', [AdminController::class, 'index'])->name('admin.menu');

        Route::get('/admin/users/{user}/edit', [AdminController::class, 'edit'])->name('admin.users.edit');
        Route::put('/admin/users/{user}', [AdminController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminController::class, 'destroy'])->name('admin.users.destroy');

        Route::prefix('jabatan')->group(function () {
            Route::get('/kepala-dinas', [JabatanController::class, 'kepalaDinas'])->name('jabatan.kepala-dinas');
            Route::get('/kepala-bidang', [JabatanController::class, 'kepalaBidang'])->name('jabatan.kepala-bidang');
            Route::get('/kepala-seksi', [JabatanController::class, 'kepalaSeksi'])->name('jabatan.kepala-seksi');
            Route::get('/get-seksi/{bidang_id}', [AdminController::class, 'getSeksi'])->name('get.seksi');
        });
    });


    // ============================================================
    // 🧑‍💼 TAMBAH USER 
    // ============================================================
    Route::prefix('user')->middleware('role:superuser')->group(function () {
        Route::get('/create', [AdminController::class, 'create'])->name('user.create');
        Route::post('/store', [AdminController::class, 'store'])->name('user.store');
    });

    // ============================================================
    // 👤 USER BIASA
    // ============================================================
    Route::middleware('role:user')->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard.user');
        })->name('dashboard.user');
    });

    // kasubag keuangan //
    Route::get('/keuangan', [KeuanganController::class, 'index'])
    ->name('keuangan.index');

    // target kepala seksi //
    Route::middleware(['auth', 'role:superuser,user'])->group(function () {
        Route::get('/target', [TargetController::class, 'index'])->name('target.index');
        Route::get('/target/create', [TargetController::class, 'create'])->name('target.create');
        Route::post('/target', [TargetController::class, 'store'])->name('target.store');

        Route::post('/target/{id}/approve', [TargetController::class, 'approve'])->name('target.approve');
        Route::post('/target/{id}/reject',  [TargetController::class, 'reject'])->name('target.reject');

        Route::get('/target/{id}/edit', [TargetController::class, 'edit'])->name('target.edit');
        Route::put('/target/{id}', [TargetController::class, 'update'])->name('target.update');

        Route::delete('/target/{id}', [TargetController::class, 'destroy'])->name('target.destroy');
    });

});
