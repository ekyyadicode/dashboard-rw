<?php

namespace App\Http\Controllers;

use App\Models\BankSampahTransaksi;
use App\Models\Rt;
use App\Models\Warga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BankSampahController extends Controller
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

    public function index(Request $request): View
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();
        $filterRtId = ($isSuperAdmin && $request->filled('rt_id'))
            ? (int) $request->input('rt_id')
            : null;

        $totalSaldo = (int) Warga::where('status_warga', 'aktif')
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
            ->sum('saldo_bank_sampah');

        $beratBulanIni = (float) BankSampahTransaksi::where('jenis_transaksi', 'setor')
            ->whereMonth('tanggal_transaksi', now()->month)
            ->whereYear('tanggal_transaksi', now()->year)
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
            ->sum('berat_kg');

        $setoranBulanIni = (int) BankSampahTransaksi::where('jenis_transaksi', 'setor')
            ->whereMonth('tanggal_transaksi', now()->month)
            ->whereYear('tanggal_transaksi', now()->year)
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
            ->sum('nominal');

        $penarikanBulanIni = (int) BankSampahTransaksi::where('jenis_transaksi', 'tarik')
            ->whereMonth('tanggal_transaksi', now()->month)
            ->whereYear('tanggal_transaksi', now()->year)
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
            ->sum('nominal');

        $wargaQuery = Warga::with(['rt', 'kartuKeluarga'])
            ->where('status_warga', 'aktif');

        if ($filterRtId) {
            $wargaQuery->where('rt_id', $filterRtId);
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $wargaQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhereHas('kartuKeluarga', function ($kkQ) use ($search) {
                        $kkQ->where('nomor_rumah', 'like', "%{$search}%")
                            ->orWhere('alamat', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->boolean('hanya_berisi')) {
            $wargaQuery->where('saldo_bank_sampah', '>', 0);
        }

        $wargaList = $wargaQuery
            ->orderByDesc('saldo_bank_sampah')
            ->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString();

        $wargaOptions = Warga::with('rt')
            ->where('status_warga', 'aktif')
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
            ->orderBy('nama_lengkap')
            ->get(['id', 'rt_id', 'nama_lengkap', 'nik', 'saldo_bank_sampah']);

        $wargaSaldoMap = $wargaOptions
            ->mapWithKeys(fn ($w) => [(string) $w->id => (int) $w->saldo_bank_sampah]);

        $rts = Rt::orderBy('nomor_rt')->get();

        return view('bank-sampah.index', compact(
            'rts',
            'totalSaldo',
            'beratBulanIni',
            'setoranBulanIni',
            'penarikanBulanIni',
            'wargaList',
            'wargaOptions',
            'wargaSaldoMap'
        ));
    }

    public function storeSetor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warga_id' => ['required', 'exists:warga,id'],
            'berat_kg' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'nominal' => ['required', 'integer', 'min:1'],
            'tanggal_transaksi' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'warga_id.required' => 'Warga penyetor wajib dipilih.',
            'warga_id.exists' => 'Data warga tidak ditemukan.',
            'berat_kg.required' => 'Berat sampah wajib diisi.',
            'berat_kg.numeric' => 'Berat sampah harus berupa angka.',
            'berat_kg.min' => 'Berat sampah minimal 0,01 kg.',
            'berat_kg.max' => 'Berat sampah melebihi batas maksimum.',
            'nominal.required' => 'Nominal setoran wajib diisi.',
            'nominal.integer' => 'Nominal setoran harus berupa angka bulat.',
            'nominal.min' => 'Nominal setoran minimal Rp 1.',
            'tanggal_transaksi.required' => 'Tanggal setor wajib diisi.',
            'tanggal_transaksi.date' => 'Format tanggal setor tidak valid.',
            'tanggal_transaksi.before_or_equal' => 'Tanggal setor tidak boleh melebihi hari ini.',
            'catatan.max' => 'Catatan maksimal 255 karakter.',
        ]);

        $namaWarga = DB::transaction(function () use ($validated) {
            $warga = Warga::lockForUpdate()->findOrFail($validated['warga_id']);
            $saldoBaru = (int) $warga->saldo_bank_sampah + (int) $validated['nominal'];

            $warga->update([
                'saldo_bank_sampah' => $saldoBaru,
            ]);

            BankSampahTransaksi::create([
                'rt_id' => $warga->rt_id,
                'warga_id' => $warga->id,
                'jenis_transaksi' => 'setor',
                'berat_kg' => $validated['berat_kg'],
                'nominal' => (int) $validated['nominal'],
                'saldo_akhir' => $saldoBaru,
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
                'catatan' => $validated['catatan'] ?: null,
            ]);

            return $warga->nama_lengkap;
        });

        return back()->with('success', "Setoran sampah untuk {$namaWarga} berhasil dicatat.");
    }

    public function storeTarik(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warga_id' => ['required', 'exists:warga,id'],
            'nominal' => ['required', 'integer', 'min:1'],
            'tanggal_transaksi' => ['required', 'date', 'before_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'warga_id.required' => 'Warga penarik saldo wajib dipilih.',
            'warga_id.exists' => 'Data warga tidak ditemukan.',
            'nominal.required' => 'Nominal penarikan wajib diisi.',
            'nominal.integer' => 'Nominal penarikan harus berupa angka bulat.',
            'nominal.min' => 'Nominal penarikan minimal Rp 1.',
            'tanggal_transaksi.required' => 'Tanggal penarikan wajib diisi.',
            'tanggal_transaksi.date' => 'Format tanggal penarikan tidak valid.',
            'tanggal_transaksi.before_or_equal' => 'Tanggal penarikan tidak boleh melebihi hari ini.',
            'catatan.max' => 'Catatan maksimal 255 karakter.',
        ]);

        $result = DB::transaction(function () use ($validated) {
            $warga = Warga::lockForUpdate()->findOrFail($validated['warga_id']);
            $nominal = (int) $validated['nominal'];

            if ((int) $warga->saldo_bank_sampah < $nominal) {
                return [
                    'ok' => false,
                    'message' => "Saldo {$warga->nama_lengkap} tidak mencukupi. Saldo tersedia: Rp ".number_format($warga->saldo_bank_sampah, 0, ',', '.'),
                ];
            }

            $saldoBaru = (int) $warga->saldo_bank_sampah - $nominal;

            $warga->update([
                'saldo_bank_sampah' => $saldoBaru,
            ]);

            BankSampahTransaksi::create([
                'rt_id' => $warga->rt_id,
                'warga_id' => $warga->id,
                'jenis_transaksi' => 'tarik',
                'berat_kg' => null,
                'nominal' => $nominal,
                'saldo_akhir' => $saldoBaru,
                'tanggal_transaksi' => $validated['tanggal_transaksi'],
                'catatan' => $validated['catatan'] ?: null,
            ]);

            return [
                'ok' => true,
                'nama' => $warga->nama_lengkap,
            ];
        });

        if (! $result['ok']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', "Penarikan saldo untuk {$result['nama']} berhasil dicatat.");
    }

    public function riwayat(Request $request): View
    {
        $query = BankSampahTransaksi::with(['rt', 'warga.kartuKeluarga', 'creator']);

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('catatan', 'like', "%{$search}%")
                    ->orWhereHas('warga', function ($wQ) use ($search) {
                        $wQ->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $query->where('rt_id', $request->input('rt_id'));
        }

        if ($request->filled('jenis_transaksi')) {
            $query->where('jenis_transaksi', $request->input('jenis_transaksi'));
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_transaksi', (int) $request->input('bulan'));
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_transaksi', (int) $request->input('tahun'));
        }

        $riwayatList = $query
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $rts = Rt::orderBy('nomor_rt')->get();
        $bulanList = self::BULAN_LIST;
        $tahunList = range(now()->year - 2, now()->year + 1);

        return view('bank-sampah.riwayat', compact(
            'riwayatList',
            'rts',
            'bulanList',
            'tahunList'
        ));
    }

    public function showWarga(Warga $warga): View
    {
        $warga->load(['rt', 'kartuKeluarga']);

        $transaksiList = BankSampahTransaksi::with('creator')
            ->where('warga_id', $warga->id)
            ->orderByDesc('tanggal_transaksi')
            ->orderByDesc('id')
            ->paginate(20);

        $totalBeratSetor = (float) BankSampahTransaksi::where('warga_id', $warga->id)
            ->where('jenis_transaksi', 'setor')
            ->sum('berat_kg');

        $totalNominalSetor = (int) BankSampahTransaksi::where('warga_id', $warga->id)
            ->where('jenis_transaksi', 'setor')
            ->sum('nominal');

        $totalNominalTarik = (int) BankSampahTransaksi::where('warga_id', $warga->id)
            ->where('jenis_transaksi', 'tarik')
            ->sum('nominal');

        $latestTransactionId = BankSampahTransaksi::where('warga_id', $warga->id)->max('id');

        return view('bank-sampah.warga-show', compact(
            'warga',
            'transaksiList',
            'totalBeratSetor',
            'totalNominalSetor',
            'totalNominalTarik',
            'latestTransactionId'
        ));
    }

    public function destroy(BankSampahTransaksi $bankSampahTransaksi): RedirectResponse
    {
        $result = DB::transaction(function () use ($bankSampahTransaksi) {
            $warga = Warga::lockForUpdate()->findOrFail($bankSampahTransaksi->warga_id);

            $hasNewer = BankSampahTransaksi::where('warga_id', $warga->id)
                ->where('id', '>', $bankSampahTransaksi->id)
                ->exists();

            if ($hasNewer) {
                return [
                    'ok' => false,
                    'message' => 'Hanya transaksi paling terakhir milik warga ini yang dapat dibatalkan agar urutan riwayat saldo tetap konsisten.',
                ];
            }

            if ($bankSampahTransaksi->jenis_transaksi === 'setor') {
                if ((int) $warga->saldo_bank_sampah < (int) $bankSampahTransaksi->nominal) {
                    return [
                        'ok' => false,
                        'message' => 'Pembatalan setoran tidak dapat dilakukan karena saldo warga saat ini lebih kecil dari nominal setoran.',
                    ];
                }

                $warga->update([
                    'saldo_bank_sampah' => (int) $warga->saldo_bank_sampah - (int) $bankSampahTransaksi->nominal,
                ]);
            } elseif ($bankSampahTransaksi->jenis_transaksi === 'tarik') {
                $warga->update([
                    'saldo_bank_sampah' => (int) $warga->saldo_bank_sampah + (int) $bankSampahTransaksi->nominal,
                ]);
            }

            $bankSampahTransaksi->delete();

            return ['ok' => true];
        });

        if (! $result['ok']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', 'Transaksi berhasil dibatalkan dan saldo warga telah disesuaikan kembali.');
    }
}

