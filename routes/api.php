<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BankSampahApiController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\IuranApiController;
use App\Http\Controllers\Api\V1\PosyanduApiController;
use App\Http\Controllers\Api\V1\WargaApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Authentication
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Sanctum Routes
    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Modul Kependudukan (KK & Warga)
        Route::middleware('permission:warga')->group(function () {
            Route::get('/kk', [WargaApiController::class, 'indexKk']);
            Route::post('/kk', [WargaApiController::class, 'storeKk']);
            Route::get('/kk/{kartuKeluarga}', [WargaApiController::class, 'showKk']);
            Route::post('/kk/{kartuKeluarga}/anggota', [WargaApiController::class, 'storeAnggota']);
            Route::get('/warga', [WargaApiController::class, 'indexWarga']);
        });

        // Modul Iuran Warga
        Route::middleware('permission:iuran')->group(function () {
            Route::get('/iuran/jenis', [IuranApiController::class, 'jenisIuran']);
            Route::get('/iuran/status', [IuranApiController::class, 'statusPerKk']);
            Route::post('/iuran/bayar', [IuranApiController::class, 'storeBayar']);
            Route::get('/iuran/riwayat', [IuranApiController::class, 'riwayat']);
        });

        // Modul Bank Sampah
        Route::middleware('permission:bank_sampah')->group(function () {
            Route::get('/bank-sampah/warga', [BankSampahApiController::class, 'wargaSaldo']);
            Route::post('/bank-sampah/setor', [BankSampahApiController::class, 'storeSetor']);
            Route::post('/bank-sampah/tarik', [BankSampahApiController::class, 'storeTarik']);
            Route::get('/bank-sampah/riwayat', [BankSampahApiController::class, 'riwayat']);
        });

        // Modul Posyandu Balita
        Route::middleware('permission:posyandu')->group(function () {
            Route::get('/posyandu/balita', [PosyanduApiController::class, 'indexBalita']);
            Route::post('/posyandu/balita', [PosyanduApiController::class, 'storeBalita']);
            Route::get('/posyandu/balita/{balita}', [PosyanduApiController::class, 'showBalita']);
            Route::post('/posyandu/periksa', [PosyanduApiController::class, 'storePeriksa']);
            Route::get('/posyandu/riwayat', [PosyanduApiController::class, 'riwayat']);
        });
    });
});
