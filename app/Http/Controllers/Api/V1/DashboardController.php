<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\KartuKeluarga;
use App\Models\PembayaranIuran;
use App\Models\Rt;
use App\Models\Warga;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();
        $filterRtId = ($isSuperAdmin && $request->filled('rt_id'))
            ? (int) $request->input('rt_id')
            : null;

        $data = [
            'total_kk_aktif' => KartuKeluarga::where('status_aktif', 'aktif')
                ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
                ->count(),
            'total_warga_aktif' => Warga::where('status_warga', 'aktif')
                ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
                ->count(),
            'pemasukan_iuran_bulan_ini' => (int) PembayaranIuran::whereMonth('tanggal_bayar', now()->month)
                ->whereYear('tanggal_bayar', now()->year)
                ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
                ->sum('nominal_dibayar'),
            'total_saldo_bank_sampah' => (int) Warga::where('status_warga', 'aktif')
                ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
                ->sum('saldo_bank_sampah'),
            'total_balita_aktif' => Balita::where('status_aktif', 'aktif')
                ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
                ->count(),
            'rts' => $isSuperAdmin
                ? Rt::orderBy('nomor_rt')->get(['id', 'nomor_rt', 'nama_ketua_rt', 'no_hp_ketua'])
                : [],
        ];

        return $this->successResponse($data, 'Ringkasan dashboard berhasil dimuat.');
    }
}

