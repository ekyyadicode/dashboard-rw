<?php

use App\Http\Controllers\Admin\RtController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankSampahController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JenisIuranController;
use App\Http\Controllers\KartuKeluargaController;
use App\Http\Controllers\PembayaranIuranController;
use App\Http\Controllers\PosyanduController;
use App\Http\Controllers\WargaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Authenticated & Active User Routes
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Super Admin Routes
    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:super_admin')
        ->group(function () {
            Route::resource('rts', RtController::class)->except(['show']);
            Route::resource('users', UserController::class)->except(['show']);

            Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
            Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        });

    // Modul Kependudukan: Data Kartu Keluarga (KK) & Anggota Warga
    Route::middleware('permission:warga')->group(function () {
        Route::get('/warga', [KartuKeluargaController::class, 'index'])->name('warga.index');
        Route::get('/warga/kk/create', [KartuKeluargaController::class, 'create'])->name('warga.kk.create');
        Route::post('/warga/kk', [KartuKeluargaController::class, 'store'])->name('warga.kk.store');
        Route::get('/warga/kk/{kartuKeluarga}', [KartuKeluargaController::class, 'show'])->name('warga.kk.show');
        Route::get('/warga/kk/{kartuKeluarga}/edit', [KartuKeluargaController::class, 'edit'])->name('warga.kk.edit');
        Route::put('/warga/kk/{kartuKeluarga}', [KartuKeluargaController::class, 'update'])->name('warga.kk.update');
        Route::delete('/warga/kk/{kartuKeluarga}', [KartuKeluargaController::class, 'destroy'])->name('warga.kk.destroy');

        Route::get('/warga/individu', [WargaController::class, 'index'])->name('warga.individu.index');
        Route::post('/warga/kk/{kartuKeluarga}/anggota', [WargaController::class, 'store'])->name('warga.anggota.store');
        Route::get('/warga/anggota/{warga}/edit', [WargaController::class, 'edit'])->name('warga.anggota.edit');
        Route::put('/warga/anggota/{warga}', [WargaController::class, 'update'])->name('warga.anggota.update');
        Route::delete('/warga/anggota/{warga}', [WargaController::class, 'destroy'])->name('warga.anggota.destroy');
    });

    // Modul Keuangan: Iuran Warga
    Route::middleware('permission:iuran')->group(function () {
        Route::get('/iuran', [PembayaranIuranController::class, 'index'])->name('iuran.index');
        Route::get('/iuran/matriks-tahunan', [PembayaranIuranController::class, 'matriksTahunan'])->name('iuran.matriks');
        Route::post('/iuran/bayar', [PembayaranIuranController::class, 'store'])->name('iuran.bayar.store');
        Route::get('/iuran/riwayat', [PembayaranIuranController::class, 'riwayat'])->name('iuran.riwayat');
        Route::delete('/iuran/pembayaran/{pembayaranIuran}', [PembayaranIuranController::class, 'destroy'])->name('iuran.pembayaran.destroy');

        Route::get('/iuran/jenis', [JenisIuranController::class, 'index'])->name('iuran.jenis.index');
        Route::post('/iuran/jenis', [JenisIuranController::class, 'store'])->name('iuran.jenis.store');
        Route::get('/iuran/jenis/{jenisIuran}/edit', [JenisIuranController::class, 'edit'])->name('iuran.jenis.edit');
        Route::put('/iuran/jenis/{jenisIuran}', [JenisIuranController::class, 'update'])->name('iuran.jenis.update');
        Route::delete('/iuran/jenis/{jenisIuran}', [JenisIuranController::class, 'destroy'])->name('iuran.jenis.destroy');
    });

    // Modul Lingkungan: Bank Sampah
    Route::middleware('permission:bank_sampah')->group(function () {
        Route::get('/bank-sampah', [BankSampahController::class, 'index'])->name('bank-sampah.index');
        Route::post('/bank-sampah/setor', [BankSampahController::class, 'storeSetor'])->name('bank-sampah.setor.store');
        Route::post('/bank-sampah/tarik', [BankSampahController::class, 'storeTarik'])->name('bank-sampah.tarik.store');
        Route::get('/bank-sampah/riwayat', [BankSampahController::class, 'riwayat'])->name('bank-sampah.riwayat');
        Route::get('/bank-sampah/warga/{warga}', [BankSampahController::class, 'showWarga'])->name('bank-sampah.warga.show');
        Route::delete('/bank-sampah/transaksi/{bankSampahTransaksi}', [BankSampahController::class, 'destroy'])->name('bank-sampah.transaksi.destroy');
    });

    // Modul Kesehatan: Posyandu Balita (Master Balita + Penimbangan)
    Route::middleware('permission:posyandu')->group(function () {
        Route::get('/posyandu', [PosyanduController::class, 'index'])->name('posyandu.index');
        Route::get('/posyandu/balita/create', [PosyanduController::class, 'createBalita'])->name('posyandu.balita.create');
        Route::post('/posyandu/balita', [PosyanduController::class, 'storeBalita'])->name('posyandu.balita.store');
        Route::get('/posyandu/balita/{balita}', [PosyanduController::class, 'showBalita'])->name('posyandu.balita.show');
        Route::get('/posyandu/balita/{balita}/edit', [PosyanduController::class, 'editBalita'])->name('posyandu.balita.edit');
        Route::put('/posyandu/balita/{balita}', [PosyanduController::class, 'updateBalita'])->name('posyandu.balita.update');
        Route::delete('/posyandu/balita/{balita}', [PosyanduController::class, 'destroyBalita'])->name('posyandu.balita.destroy');

        Route::post('/posyandu/periksa', [PosyanduController::class, 'storePeriksa'])->name('posyandu.periksa.store');
        Route::get('/posyandu/riwayat', [PosyanduController::class, 'riwayat'])->name('posyandu.riwayat');
        Route::delete('/posyandu/periksa/{posyanduBalita}', [PosyanduController::class, 'destroyPeriksa'])->name('posyandu.periksa.destroy');
    });
});
