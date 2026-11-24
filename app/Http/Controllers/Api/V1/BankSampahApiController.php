<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BankSampahTransaksi;
use App\Models\Warga;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankSampahApiController extends Controller
{
    use ApiResponse;

    public function wargaSaldo(Request $request): JsonResponse
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();
        $filterRtId = ($isSuperAdmin && $request->filled('rt_id'))
            ? (int) $request->input('rt_id')
            : null;

        $totalSaldoRt = (int) Warga::where('status_warga', 'aktif')
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
            ->sum('saldo_bank_sampah');

        $query = Warga::with(['rt', 'kartuKeluarga'])
            ->where('status_warga', 'aktif')
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId));

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhereHas('kartuKeluarga', function ($kkQ) use ($search) {
                        $kkQ->where('nomor_rumah', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->boolean('hanya_berisi')) {
            $query->where('saldo_bank_sampah', '>', 0);
        }

        $warga = $query
            ->orderByDesc('saldo_bank_sampah')
            ->orderBy('nama_lengkap')
            ->paginate(15);

        return $this->successResponse([
            'total_saldo_rt' => $totalSaldoRt,
            'warga' => $warga,
        ], 'Daftar saldo tabungan Bank Sampah berhasil dimuat.');
    }

    public function storeSetor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warga_id' => ['required', 'exists:warga,id'],
            'berat_kg' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'nominal' => ['required', 'integer', 'min:1'],
            'tanggal_transaksi' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $result = DB::transaction(function () use ($validated) {
            $warga = Warga::lockForUpdate()->findOrFail($validated['warga_id']);
            $saldoBaru = (int) $warga->saldo_bank_sampah + (int) $validated['nominal'];

            $warga->update(['saldo_bank_sampah' => $saldoBaru]);

            $transaksi = BankSampahTransaksi::create([
                'rt_id' => $warga->rt_id,
                'warga_id' => $warga->id,
                'jenis_transaksi' => 'setor',
                'berat_kg' => $validated['berat_kg'],
                'nominal' => (int) $validated['nominal'],
                'saldo_akhir' => $saldoBaru,
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
                'catatan' => ! empty($validated['catatan']) ? $validated['catatan'] : null,
            ]);

            return [
                'transaksi' => $transaksi->load(['warga', 'creator']),
                'saldo_terbaru' => $saldoBaru,
            ];
        });

        return $this->successResponse($result, 'Setoran sampah berhasil dicatat.', 201);
    }

    public function storeTarik(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warga_id' => ['required', 'exists:warga,id'],
            'nominal' => ['required', 'integer', 'min:1'],
            'tanggal_transaksi' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $result = DB::transaction(function () use ($validated) {
            $warga = Warga::lockForUpdate()->findOrFail($validated['warga_id']);
            $nominal = (int) $validated['nominal'];

            if ((int) $warga->saldo_bank_sampah < $nominal) {
                return ['ok' => false, 'saldo' => (int) $warga->saldo_bank_sampah];
            }

            $saldoBaru = (int) $warga->saldo_bank_sampah - $nominal;

            $warga->update(['saldo_bank_sampah' => $saldoBaru]);

            $transaksi = BankSampahTransaksi::create([
                'rt_id' => $warga->rt_id,
                'warga_id' => $warga->id,
                'jenis_transaksi' => 'tarik',
                'berat_kg' => null,
                'nominal' => $nominal,
                'saldo_akhir' => $saldoBaru,
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
                'catatan' => ! empty($validated['catatan']) ? $validated['catatan'] : null,
            ]);

            return [
                'ok' => true,
                'transaksi' => $transaksi->load(['warga', 'creator']),
                'saldo_terbaru' => $saldoBaru,
            ];
        });

        if (! $result['ok']) {
            return $this->errorResponse('Saldo warga tidak mencukupi.', 422, [
                'saldo_tersedia' => $result['saldo'],
            ]);
        }

        return $this->successResponse([
            'transaksi' => $result['transaksi'],
            'saldo_terbaru' => $result['saldo_terbaru'],
        ], 'Penarikan saldo berhasil dicatat.', 201);
    }

    public function riwayat(Request $request): JsonResponse
    {
        $query = BankSampahTransaksi::with(['warga', 'creator']);

        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $query->where('rt_id', $request->input('rt_id'));
        }

        if ($request->filled('warga_id')) {
            $query->where('warga_id', $request->input('warga_id'));
        }

        if ($request->filled('jenis_transaksi')) {
            $query->where('jenis_transaksi', $request->input('jenis_transaksi'));
        }

        $result = $query
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->paginate(20);

        return $this->successResponse($result, 'Riwayat transaksi Bank Sampah berhasil dimuat.');
    }
}

