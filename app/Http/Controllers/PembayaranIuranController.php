<?php

namespace App\Http\Controllers;

use App\Models\JenisIuran;
use App\Models\KartuKeluarga;
use App\Models\PembayaranIuran;
use App\Models\Rt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PembayaranIuranController extends Controller
{
    public const BULAN_LIST = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    public const BULAN_SINGKAT = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'Mei',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Agu',
        9 => 'Sep',
        10 => 'Okt',
        11 => 'Nov',
        12 => 'Des',
    ];

    public function index(Request $request): View
    {
        $rts = Rt::orderBy('nomor_rt')->get();

        $selectedRtId = $request->user()->isSuperAdmin()
            ? (int) $request->get('rt_id', $rts->first()?->id)
            : (int) $request->user()->rt_id;

        $jenisIuranList = JenisIuran::where('rt_id', $selectedRtId)
            ->where('is_active', true)
            ->orderBy('nama_iuran')
            ->get();

        $selectedJenis = $jenisIuranList->firstWhere('id', (int) $request->get('jenis_iuran_id'))
            ?? $jenisIuranList->first();
        $selectedJenisId = $selectedJenis?->id;

        $selectedBulan = (int) $request->get('bulan', now()->month);
        if ($selectedBulan < 1 || $selectedBulan > 12) {
            $selectedBulan = now()->month;
        }

        $selectedTahun = (int) $request->get('tahun', now()->year);

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

        $paymentsGrouped = collect();
        $totalTerkumpul = 0;
        $tarifStandar = (int) ($selectedJenis?->nominal ?? 0);

        if ($selectedJenis) {
            $payQuery = PembayaranIuran::where('rt_id', $selectedRtId)
                ->where('jenis_iuran_id', $selectedJenis->id)
                ->where('periode_tahun', $selectedTahun);

            if ($selectedJenis->tipe === 'bulanan') {
                $payQuery->where('periode_bulan', $selectedBulan);
            }

            $payments = $payQuery->latest('tanggal_bayar')->latest('id')->get();
            $totalTerkumpul = (int) $payments->sum('nominal_dibayar');
            $paymentsGrouped = $payments->groupBy('kartu_keluarga_id');
        }

        $statusByKk = [];
        $jumlahLunas = 0;
        $jumlahSebagian = 0;
        $jumlahBelumBayar = 0;

        foreach ($kkList as $kk) {
            $listBayar = $paymentsGrouped->get($kk->id, collect());
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

            $statusByKk[$kk->id] = [
                'status' => $status,
                'list_bayar' => $listBayar,
                'latest' => $listBayar->first(),
                'total_dibayar' => $totalDibayar,
                'tarif_standar' => $tarifStandar,
                'sisa_tagihan' => $sisaTagihan,
            ];
        }

        $totalKk = $kkList->count();
        $jumlahBelum = $jumlahBelumBayar + $jumlahSebagian;

        $bulanList = self::BULAN_LIST;
        $tahunList = range(now()->year - 2, now()->year + 1);

        return view('iuran.index', compact(
            'rts',
            'selectedRtId',
            'jenisIuranList',
            'selectedJenis',
            'selectedJenisId',
            'selectedBulan',
            'selectedTahun',
            'kkList',
            'statusByKk',
            'totalKk',
            'jumlahLunas',
            'jumlahSebagian',
            'jumlahBelumBayar',
            'jumlahBelum',
            'totalTerkumpul',
            'bulanList',
            'tahunList'
        ));
    }

    public function matriksTahunan(Request $request): View
    {
        $rts = Rt::orderBy('nomor_rt')->get();

        $selectedRtId = $request->user()->isSuperAdmin()
            ? (int) $request->get('rt_id', $rts->first()?->id)
            : (int) $request->user()->rt_id;

        $jenisIuranList = JenisIuran::where('rt_id', $selectedRtId)
            ->where('tipe', 'bulanan')
            ->where('is_active', true)
            ->orderBy('nama_iuran')
            ->get();

        $selectedJenis = $jenisIuranList->firstWhere('id', (int) $request->get('jenis_iuran_id'))
            ?? $jenisIuranList->first();
        $selectedJenisId = $selectedJenis?->id;

        $selectedTahun = (int) $request->get('tahun', now()->year);

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

        $matriks = [];
        $totalTerkumpulTahunan = 0;
        $tarifStandar = (int) ($selectedJenis?->nominal ?? 0);

        if ($selectedJenis) {
            $payments = PembayaranIuran::where('rt_id', $selectedRtId)
                ->where('jenis_iuran_id', $selectedJenis->id)
                ->where('periode_tahun', $selectedTahun)
                ->whereNotNull('periode_bulan')
                ->latest('tanggal_bayar')
                ->latest('id')
                ->get();

            $totalTerkumpulTahunan = (int) $payments->sum('nominal_dibayar');

            $grouped = [];
            foreach ($payments as $payment) {
                $kkId = $payment->kartu_keluarga_id;
                $bulan = (int) $payment->periode_bulan;
                $grouped[$kkId][$bulan][] = $payment;
            }

            foreach ($grouped as $kkId => $months) {
                foreach ($months as $bulan => $items) {
                    $collection = collect($items);
                    $totalDibayar = (int) $collection->sum('nominal_dibayar');
                    $sisaTagihan = max(0, $tarifStandar - $totalDibayar);

                    if (($tarifStandar > 0 && $totalDibayar >= $tarifStandar) || ($tarifStandar === 0 && $totalDibayar > 0)) {
                        $status = 'lunas';
                    } elseif ($tarifStandar > 0 && $totalDibayar > 0 && $totalDibayar < $tarifStandar) {
                        $status = 'sebagian';
                    } else {
                        $status = 'belum';
                    }

                    $latest = $collection->first();

                    $matriks[$kkId][$bulan] = [
                        'status' => $status,
                        'total_dibayar' => $totalDibayar,
                        'sisa_tagihan' => $sisaTagihan,
                        'count' => $collection->count(),
                        'latest_date' => $latest?->tanggal_bayar?->format('d/m/Y'),
                        'latest_method' => $latest?->metode_bayar,
                    ];
                }
            }
        }

        $bulanList = self::BULAN_LIST;
        $bulanSingkat = self::BULAN_SINGKAT;
        $tahunList = range(now()->year - 2, now()->year + 1);

        return view('iuran.matriks', compact(
            'rts',
            'selectedRtId',
            'jenisIuranList',
            'selectedJenis',
            'selectedJenisId',
            'selectedTahun',
            'kkList',
            'matriks',
            'totalTerkumpulTahunan',
            'bulanList',
            'bulanSingkat',
            'tahunList'
        ));
    }

    public function store(Request $request): RedirectResponse
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
        ], [
            'kartu_keluarga_id.required' => 'Kartu Keluarga wajib dipilih.',
            'kartu_keluarga_id.exists' => 'Data Kartu Keluarga tidak ditemukan.',
            'jenis_iuran_id.required' => 'Jenis iuran wajib dipilih.',
            'jenis_iuran_id.exists' => 'Jenis iuran tidak ditemukan.',
            'periode_bulan.required' => 'Periode bulan wajib dipilih untuk iuran bulanan.',
            'periode_bulan.min' => 'Periode bulan tidak valid.',
            'periode_bulan.max' => 'Periode bulan tidak valid.',
            'periode_tahun.required' => 'Periode tahun wajib diisi.',
            'nominal_dibayar.required' => 'Nominal dibayar wajib diisi.',
            'nominal_dibayar.integer' => 'Nominal dibayar harus berupa angka bulat.',
            'nominal_dibayar.min' => 'Nominal dibayar minimal Rp 1.',
            'metode_bayar.required' => 'Metode bayar wajib dipilih.',
            'metode_bayar.in' => 'Metode bayar harus Tunai atau Transfer.',
            'tanggal_bayar.required' => 'Tanggal bayar wajib diisi.',
            'tanggal_bayar.date' => 'Format tanggal bayar tidak valid.',
            'catatan.max' => 'Catatan maksimal 255 karakter.',
        ]);

        $kk = KartuKeluarga::findOrFail($validated['kartu_keluarga_id']);
        $jenis = JenisIuran::findOrFail($validated['jenis_iuran_id']);

        if ((int) $kk->rt_id !== (int) $jenis->rt_id) {
            return back()->with('error', 'Wilayah RT pada Kartu Keluarga tidak sesuai dengan jenis iuran yang dipilih.');
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
                return back()->with('error', 'Iuran KK ini pada periode tersebut sudah lunas.');
            }

            $sisaTagihan = $tarifStandar - $sudahDibayar;

            if ($nominalBaru > $sisaTagihan) {
                return back()->with('error', 'Nominal pembayaran melebihi sisa tagihan sebesar Rp '.number_format($sisaTagihan, 0, ',', '.').'.');
            }
        }

        PembayaranIuran::create([
            'rt_id' => $kk->rt_id,
            'kartu_keluarga_id' => $kk->id,
            'jenis_iuran_id' => $jenis->id,
            'periode_bulan' => $periodeBulan,
            'periode_tahun' => $periodeTahun,
            'nominal_dibayar' => $nominalBaru,
            'metode_bayar' => $validated['metode_bayar'],
            'tanggal_bayar' => $validated['tanggal_bayar'],
            'catatan' => $validated['catatan'] ?: null,
        ]);

        $totalSetelahBayar = $sudahDibayar + $nominalBaru;

        if ($tarifStandar === 0 || $totalSetelahBayar >= $tarifStandar) {
            return back()->with('success', 'Pembayaran berhasil dicatat (Status: LUNAS).');
        }

        $sisaBaru = $tarifStandar - $totalSetelahBayar;

        return back()->with('success', 'Pembayaran sebagian berhasil dicatat. Sisa tagihan: Rp '.number_format($sisaBaru, 0, ',', '.'));
    }

    public function riwayat(Request $request): View
    {
        $query = PembayaranIuran::with([
            'rt',
            'kartuKeluarga.kepalaKeluarga',
            'jenisIuran',
            'creator',
        ]);

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('catatan', 'like', "%{$search}%")
                    ->orWhereHas('kartuKeluarga', function ($kkQ) use ($search) {
                        $kkQ->where('no_kk', 'like', "%{$search}%")
                            ->orWhere('nomor_rumah', 'like', "%{$search}%")
                            ->orWhereHas('kepalaKeluarga', function ($wQ) use ($search) {
                                $wQ->where('nama_lengkap', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $query->where('rt_id', $request->input('rt_id'));
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

        $riwayatList = $query->latest('tanggal_bayar')->latest('id')->paginate(20)->withQueryString();
        $rts = Rt::orderBy('nomor_rt')->get();

        $jenisIuranQuery = JenisIuran::with('rt')->orderBy('rt_id')->orderBy('nama_iuran');
        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $jenisIuranQuery->where('rt_id', $request->input('rt_id'));
        }
        $jenisIuranList = $jenisIuranQuery->get();

        $bulanList = self::BULAN_LIST;
        $tahunList = range(now()->year - 2, now()->year + 1);

        return view('iuran.riwayat', compact(
            'riwayatList',
            'rts',
            'jenisIuranList',
            'bulanList',
            'tahunList'
        ));
    }

    public function destroy(PembayaranIuran $pembayaranIuran): RedirectResponse
    {
        $pembayaranIuran->delete();

        return back()->with('success', 'Transaksi pembayaran berhasil dibatalkan.');
    }
}
