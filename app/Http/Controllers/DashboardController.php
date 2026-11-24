<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\KartuKeluarga;
use App\Models\PembayaranIuran;
use App\Models\Rt;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $totalKkAktif = KartuKeluarga::where('status_aktif', 'aktif')->count();
        $totalWargaAktif = Warga::where('status_warga', 'aktif')->count();
        $pemasukanIuranBulanIni = PembayaranIuran::whereMonth('tanggal_bayar', now()->month)
            ->whereYear('tanggal_bayar', now()->year)
            ->sum('nominal_dibayar');
        $totalSaldoBankSampah = Warga::sum('saldo_bank_sampah');
        $totalBalita = Balita::where('status_aktif', 'aktif')->count();

        $rtSummaries = collect();

        if ($request->user()->isSuperAdmin()) {
            $rtSummaries = Rt::orderBy('nomor_rt')
                ->withCount([
                    'kartuKeluarga as kk_aktif_count' => fn ($query) => $query->where('status_aktif', 'aktif'),
                    'warga as warga_aktif_count' => fn ($query) => $query->where('status_warga', 'aktif'),
                    'users as pengurus_aktif_count' => fn ($query) => $query->where('role', 'pengurus')->where('is_active', true),
                ])
                ->get();
        }

        return view('dashboard.index', compact(
            'totalKkAktif',
            'totalWargaAktif',
            'pemasukanIuranBulanIni',
            'totalSaldoBankSampah',
            'totalBalita',
            'rtSummaries'
        ));
    }
}

