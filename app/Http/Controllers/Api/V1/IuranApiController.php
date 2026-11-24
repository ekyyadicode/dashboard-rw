<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\JenisIuran;
use App\Models\KartuKeluarga;
use App\Models\PembayaranIuran;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IuranApiController extends Controller
{
    use ApiResponse;

    public function jenisIuran(Request $request): JsonResponse
    {
        $query = JenisIuran::with('rt')->where('is_active', true);

        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $query->where('rt_id', $request->input('rt_id'));
        }

        $items = $query->orderBy('rt_id')->orderBy('nama_iuran')->get();

        return $this->successResponse($items, 'Daftar jenis iuran berhasil dimuat.');
    }

    public function statusPerKk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'jenis_iuran_id' => ['required', 'exists:jenis_iuran,id'],
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'tahun' => ['nullable', 'integer', 'min:2020', 'max:2100'],
            'rt_id' => ['nullable', 'exists:rts,id'],
            'q' => ['nullable', 'string'],
        ], [
            'jenis_iuran_id.required' => 'Parameter jenis_iuran_id wajib diisi.',
            'jenis_iuran_id.exists' => 'Jenis iuran tidak ditemukan.',
        ]);

        $jenis = JenisIuran::findOrFail($validated['jenis_iuran_id']);

        $selectedRtId = $request->user()->isSuperAdmin()
            ? (int) ($request->filled('rt_id') ? $request->input('rt_id') : $jenis->rt_id)
            : (int) $request->user()->rt_id;

        $bulan = (int) ($validated['bulan'] ?? now()->month);
        $tahun = (int) ($validated['tahun'] ?? now()->year);
        $tarifStandar = (int) $jenis->nominal;

        $kkQuery = KartuKeluarga::with('kepalaKeluarga')
            ->where('rt_id', $selectedRtId)
            ->where('status_aktif', 'aktif');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $kkQuery->where(function ($q) use ($search) {
                $q->where('no_kk', 'like', "%{$search}%")
                    ->orWhere('nomor_rumah', 'like', "%{$search}%")
                    ->orWhereHas('kepalaKeluarga', function ($kq) use ($search) {
                        $kq->where('nama_lengkap', 'like', "%{$search}%");
                    });
            });
        }

        $kkList = $kkQuery->orderBy('nomor_rumah')->get();

        $payQuery = PembayaranIuran::where('rt_id', $selectedRtId)
            ->where('jenis_iuran_id', $jenis->id)
            ->where('periode_tahun', $tahun);

        if ($jenis->tipe === 'bulanan') {
            $payQuery->where('periode_bulan', $bulan);
        }

        $payments = $payQuery->latest('tanggal_bayar')->latest('id')->get();
        $totalTerkumpul = (int) $payments->sum('nominal_dibayar');
        $grouped = $payments->groupBy('kartu_keluarga_id');

        $jumlahLunas = 0;
        $jumlahSebagian = 0;
        $jumlahBelumBayar = 0;

        $items = $kkList->map(function (KartuKeluarga $kk) use ($grouped, $tarifStandar, &$jumlahLunas, &$jumlahSebagian, &$jumlahBelumBayar) {
            $listBayar = $grouped->get($kk->id, collect());
            $totalDibayar = (int) $listBayar->sum('nominal_dibayar');
            $sisaTagihan = max(0, $tarifStandar - $totalDibayar);

            if (($tarifStandar > 0 && $totalDibayar >= $tarifStandar) || ($tarifStandar === 0 && $totalDibayar > 0)) {
                $status = 'lunas';
                $jumlahLunas++;
            } elseif ($tarifStandar > 0 && $totalDibayar > 0 && $totalDibayar < $tarifStandar) {
                $status = 'sebagian';
                $jumlahSebagian++;
            } else {
                $status = 'belum';
                $jumlahBelumBayar++;
            }

            $latest = $listBayar->first();

            return [
                'kartu_keluarga_id' => $kk->id,
                'no_kk' => $kk->no_kk,
                'nomor_rumah' => $kk->nomor_rumah,
                'alamat' => $kk->alamat,
                'status_domisili' => $kk->status_domisili,
                'kepala_keluarga' => $kk->kepalaKeluarga?->nama_lengkap,
                'total_dibayar' => $totalDibayar,
                'tarif_standar' => $tarifStandar,
                'sisa_tagihan' => $sisaTagihan,
                'status' => $status,
                'jumlah_transaksi' => $listBayar->count(),
                'pembayaran_terakhir' => $latest ? [
                    'id' => $latest->id,
                    'tanggal_bayar' => $latest->tanggal_bayar?->format('Y-m-d'),
                    'metode_bayar' => $latest->metode_bayar,
                    'nominal_dibayar' => (int) $latest->nominal_dibayar,
                    'catatan' => $latest->catatan,
                ] : null,
            ];
        })->values();

        return $this->successResponse([
            'jenis_iuran' => $jenis,
            'periode' => [
                'bulan' => $jenis->tipe === 'bulanan' ? $bulan : null,
                'tahun' => $tahun,
            ],
            'summary' => [
                'total_kk' => $kkList->count(),
                'jumlah_lunas' => $jumlahLunas,
                'jumlah_sebagian' => $jumlahSebagian,
                'jumlah_belum' => $jumlahBelumBayar,
                'total_terkumpul' => $totalTerkumpul,
            ],
            'items' => $items,
        ], 'Status iuran per KK berhasil dimuat.');
    }

    public function storeBayar(Request $request): JsonResponse
    {
        $jenisForRule = JenisIuran::find($request->input('jenis_iuran_id'));

        $validated = $request->validate([
            'kartu_keluarga_id' => ['required', 'exists:kartu_keluarga,id'],
            'jenis_iuran_id' => ['required', 'exists:jenis_iuran,id'],
            'periode_bulan' => [
                Rule::requiredIf(fn () => $jenisForRule?->tipe === 'bulanan'),
                'nullable',
                'integer',
                'min:1',
                'max:12',
            ],
            'periode_tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'nominal_dibayar' => ['required', 'integer', $jenisForRule && $jenisForRule->nominal > 0 ? 'min:1' : 'min:0'],
            'metode_bayar' => ['required', Rule::in(['tunai', 'transfer'])],
            'tanggal_bayar' => ['required', 'date'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $kk = KartuKeluarga::findOrFail($validated['kartu_keluarga_id']);
        $jenis = JenisIuran::findOrFail($validated['jenis_iuran_id']);

        if ((int) $kk->rt_id !== (int) $jenis->rt_id) {
            return $this->errorResponse('Wilayah RT pada Kartu Keluarga tidak sesuai dengan jenis iuran.', 422);
        }

        $periodeBulan = $jenis->tipe === 'bulanan' ? (int) $validated['periode_bulan'] : null;
        $periodeTahun = (int) $validated['periode_tahun'];
        $nominalBaru = (int) $validated['nominal_dibayar'];
        $tarifStandar = (int) $jenis->nominal;

        $sudahDibayarQuery = PembayaranIuran::where('kartu_keluarga_id', $kk->id)
            ->where('jenis_iuran_id', $jenis->id)
            ->where('periode_tahun', $periodeTahun);

        if ($jenis->tipe === 'bulanan') {
            $sudahDibayarQuery->where('periode_bulan', $periodeBulan);
        }

        $sudahDibayar = (int) $sudahDibayarQuery->sum('nominal_dibayar');

        if ($tarifStandar > 0) {
            if ($sudahDibayar >= $tarifStandar) {
                return $this->errorResponse('Iuran KK ini pada periode tersebut sudah lunas.', 422);
            }

            $sisaTagihan = $tarifStandar - $sudahDibayar;

            if ($nominalBaru > $sisaTagihan) {
                return $this->errorResponse(
                    'Nominal pembayaran melebihi sisa tagihan sebesar Rp '.number_format($sisaTagihan, 0, ',', '.').'.',
                    422
                );
            }
        }

        $pembayaran = PembayaranIuran::create([
            'rt_id' => $kk->rt_id,
            'kartu_keluarga_id' => $kk->id,
            'jenis_iuran_id' => $jenis->id,
            'periode_bulan' => $periodeBulan,
            'periode_tahun' => $periodeTahun,
            'nominal_dibayar' => $nominalBaru,
            'metode_bayar' => $validated['metode_bayar'],
            'tanggal_bayar' => $validated['tanggal_bayar'],
            'catatan' => ! empty($validated['catatan']) ? $validated['catatan'] : null,
        ]);

        $totalSetelahBayar = $sudahDibayar + $nominalBaru;
        $isLunas = $tarifStandar === 0 || $totalSetelahBayar >= $tarifStandar;
        $sisaBaru = max(0, $tarifStandar - $totalSetelahBayar);
        $statusTerbaru = $isLunas ? 'lunas' : 'sebagian';

        $message = $isLunas
            ? 'Pembayaran berhasil dicatat (Status: LUNAS).'
            : 'Pembayaran sebagian berhasil dicatat. Sisa tagihan: Rp '.number_format($sisaBaru, 0, ',', '.');

        return $this->successResponse([
            'pembayaran' => $pembayaran,
            'status' => $statusTerbaru,
            'total_dibayar' => $totalSetelahBayar,
            'tarif_standar' => $tarifStandar,
            'sisa_tagihan' => $sisaBaru,
        ], $message, 201);
    }

    public function riwayat(Request $request): JsonResponse
    {
        $query = PembayaranIuran::with(['kartuKeluarga.kepalaKeluarga', 'jenisIuran', 'creator']);

        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $query->where('rt_id', $request->input('rt_id'));
        }

        if ($request->filled('kartu_keluarga_id')) {
            $query->where('kartu_keluarga_id', $request->input('kartu_keluarga_id'));
        }

        if ($request->filled('jenis_iuran_id')) {
            $query->where('jenis_iuran_id', $request->input('jenis_iuran_id'));
        }

        if ($request->filled('bulan')) {
            $query->where('periode_bulan', (int) $request->input('bulan'));
        }

        if ($request->filled('tahun')) {
            $query->where('periode_tahun', (int) $request->input('tahun'));
        }

        $result = $query->latest('tanggal_bayar')->latest('id')->paginate(20);

        return $this->successResponse($result, 'Riwayat pembayaran iuran berhasil dimuat.');
    }
}

