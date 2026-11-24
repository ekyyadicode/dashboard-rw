<?php

namespace App\Http\Controllers;

use App\Models\Balita;
use App\Models\PosyanduBalita;
use App\Models\Rt;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PosyanduController extends Controller
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

    public const IMUNISASI_CHIPS = [
        'Vitamin A',
        'BCG',
        'Polio',
        'DPT-HB-Hib',
        'Campak / MR',
        'PCV',
    ];

    public function index(Request $request): View
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();
        $filterRtId = ($isSuperAdmin && $request->filled('rt_id'))
            ? (int) $request->input('rt_id')
            : null;

        $selectedStatusAktif = $request->has('status_aktif')
            ? (string) $request->input('status_aktif')
            : 'aktif';

        $currentMonth = now()->month;
        $currentYear = now()->year;

        // 4 Kartu Ringkasan berbasis Balita aktif
        $activeBalitaBase = fn () => Balita::where('status_aktif', 'aktif')
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId));

        $totalBalita = $activeBalitaBase()->count();

        $sudahDitimbangBulanIni = $activeBalitaBase()
            ->whereHas('posyanduBalita', function ($pq) use ($currentMonth, $currentYear) {
                $pq->whereMonth('tanggal_periksa', $currentMonth)
                    ->whereYear('tanggal_periksa', $currentYear);
            })
            ->count();

        $belumDitimbangBulanIni = max(0, $totalBalita - $sudahDitimbangBulanIni);

        $totalKunjunganBulanIni = PosyanduBalita::whereMonth('tanggal_periksa', $currentMonth)
            ->whereYear('tanggal_periksa', $currentYear)
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
            ->count();

        // Daftar Balita terfilter
        $balitaQuery = Balita::with(['rt', 'pemeriksaanTerakhir'])
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId));

        if ($selectedStatusAktif !== 'all' && $selectedStatusAktif !== '') {
            $balitaQuery->where('status_aktif', $selectedStatusAktif);
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $balitaQuery->where(function ($q) use ($search) {
                $q->where('nama_balita', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nama_ibu', 'like', "%{$search}%")
                    ->orWhere('nama_ayah', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status_bulan_ini')) {
            $statusBulan = $request->input('status_bulan_ini');
            if ($statusBulan === 'sudah') {
                $balitaQuery->whereHas('posyanduBalita', function ($pq) use ($currentMonth, $currentYear) {
                    $pq->whereMonth('tanggal_periksa', $currentMonth)
                        ->whereYear('tanggal_periksa', $currentYear);
                });
            } elseif ($statusBulan === 'belum') {
                $balitaQuery->whereDoesntHave('posyanduBalita', function ($pq) use ($currentMonth, $currentYear) {
                    $pq->whereMonth('tanggal_periksa', $currentMonth)
                        ->whereYear('tanggal_periksa', $currentYear);
                });
            }
        }

        $balitaList = $balitaQuery
            ->orderByDesc('tanggal_lahir')
            ->paginate(15)
            ->withQueryString();

        $checkedThisMonthIds = PosyanduBalita::whereMonth('tanggal_periksa', $currentMonth)
            ->whereYear('tanggal_periksa', $currentYear)
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
            ->distinct()
            ->pluck('balita_id')
            ->flip();

        $balitaOptions = $activeBalitaBase()
            ->with('rt')
            ->orderBy('nama_balita')
            ->get(['id', 'rt_id', 'nama_balita', 'tanggal_lahir']);

        $rts = Rt::orderBy('nomor_rt')->get();
        $imunisasiChips = self::IMUNISASI_CHIPS;

        return view('posyandu.index', compact(
            'rts',
            'totalBalita',
            'sudahDitimbangBulanIni',
            'belumDitimbangBulanIni',
            'totalKunjunganBulanIni',
            'balitaList',
            'checkedThisMonthIds',
            'balitaOptions',
            'imunisasiChips',
            'selectedStatusAktif'
        ));
    }

    public function createBalita(): View
    {
        $rts = Rt::orderBy('nomor_rt')->get();

        return view('posyandu.balita.create', compact('rts'));
    }

    public function storeBalita(Request $request): RedirectResponse
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();

        $validated = $request->validate([
            'rt_id' => $isSuperAdmin ? ['required', 'exists:rts,id'] : ['nullable'],
            'nama_balita' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'digits:16', 'unique:balita,nik'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'nama_ibu' => ['required', 'string', 'max:255'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'no_hp_ortu' => ['nullable', 'string', 'max:20'],
            'status_aktif' => ['required', Rule::in(['aktif', 'lulus', 'pindah'])],
        ], [
            'rt_id.required' => 'Wilayah RT wajib dipilih.',
            'rt_id.exists' => 'Wilayah RT tidak valid.',
            'nama_balita.required' => 'Nama balita wajib diisi.',
            'nik.digits' => 'NIK balita harus terdiri dari tepat 16 digit angka.',
            'nik.unique' => 'NIK balita sudah terdaftar di Posyandu.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki (L) atau Perempuan (P).',
            'tanggal_lahir.required' => 'Tanggal lahir balita wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'nama_ibu.required' => 'Nama ibu wajib diisi.',
            'no_hp_ortu.max' => 'Nomor HP orang tua maksimal 20 karakter.',
            'status_aktif.required' => 'Status balita wajib dipilih.',
        ]);

        Balita::create([
            'rt_id' => $isSuperAdmin ? $validated['rt_id'] : $request->user()->rt_id,
            'nama_balita' => $validated['nama_balita'],
            'nik' => ! empty($validated['nik']) ? $validated['nik'] : null,
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'nama_ibu' => $validated['nama_ibu'],
            'nama_ayah' => ! empty($validated['nama_ayah']) ? $validated['nama_ayah'] : null,
            'alamat' => ! empty($validated['alamat']) ? $validated['alamat'] : null,
            'no_hp_ortu' => ! empty($validated['no_hp_ortu']) ? $validated['no_hp_ortu'] : null,
            'status_aktif' => $validated['status_aktif'],
        ]);

        return redirect()
            ->route('posyandu.index')
            ->with('success', 'Data balita '.$validated['nama_balita'].' berhasil ditambahkan.');
    }

    public function editBalita(Balita $balita): View
    {
        $balita->load('rt');
        $rts = Rt::orderBy('nomor_rt')->get();

        return view('posyandu.balita.edit', compact('balita', 'rts'));
    }

    public function updateBalita(Request $request, Balita $balita): RedirectResponse
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();

        $validated = $request->validate([
            'rt_id' => $isSuperAdmin ? ['required', 'exists:rts,id'] : ['nullable'],
            'nama_balita' => ['required', 'string', 'max:255'],
            'nik' => ['nullable', 'digits:16', Rule::unique('balita', 'nik')->ignore($balita->id)],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'nama_ibu' => ['required', 'string', 'max:255'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'no_hp_ortu' => ['nullable', 'string', 'max:20'],
            'status_aktif' => ['required', Rule::in(['aktif', 'lulus', 'pindah'])],
        ], [
            'rt_id.required' => 'Wilayah RT wajib dipilih.',
            'rt_id.exists' => 'Wilayah RT tidak valid.',
            'nama_balita.required' => 'Nama balita wajib diisi.',
            'nik.digits' => 'NIK balita harus terdiri dari tepat 16 digit angka.',
            'nik.unique' => 'NIK balita sudah terdaftar pada balita lain.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.required' => 'Tanggal lahir balita wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'nama_ibu.required' => 'Nama ibu wajib diisi.',
            'no_hp_ortu.max' => 'Nomor HP orang tua maksimal 20 karakter.',
            'status_aktif.required' => 'Status balita wajib dipilih.',
        ]);

        DB::transaction(function () use ($validated, $balita, $isSuperAdmin) {
            $oldRtId = (int) $balita->rt_id;
            $newRtId = $isSuperAdmin ? (int) $validated['rt_id'] : $oldRtId;

            $balita->update([
                'rt_id' => $newRtId,
                'nama_balita' => $validated['nama_balita'],
                'nik' => ! empty($validated['nik']) ? $validated['nik'] : null,
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'tanggal_lahir' => $validated['tanggal_lahir'],
                'nama_ibu' => $validated['nama_ibu'],
                'nama_ayah' => ! empty($validated['nama_ayah']) ? $validated['nama_ayah'] : null,
                'alamat' => ! empty($validated['alamat']) ? $validated['alamat'] : null,
                'no_hp_ortu' => ! empty($validated['no_hp_ortu']) ? $validated['no_hp_ortu'] : null,
                'status_aktif' => $validated['status_aktif'],
            ]);

            if ($isSuperAdmin && $newRtId !== $oldRtId) {
                $balita->posyanduBalita()->update(['rt_id' => $newRtId]);
            }
        });

        return redirect()
            ->route('posyandu.index')
            ->with('success', 'Data balita '.$validated['nama_balita'].' berhasil diperbarui.');
    }

    public function destroyBalita(Balita $balita): RedirectResponse
    {
        $nama = $balita->nama_balita;

        DB::transaction(function () use ($balita) {
            $balita->posyanduBalita()->delete();
            $balita->delete();
        });

        return redirect()
            ->route('posyandu.index')
            ->with('success', 'Data balita '.$nama.' beserta riwayat pemeriksaannya berhasil dihapus.');
    }

    public function storePeriksa(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'balita_id' => ['required', 'exists:balita,id'],
            'tanggal_periksa' => ['required', 'date', 'before_or_equal:today'],
            'berat_badan_kg' => ['required', 'numeric', 'min:1', 'max:50'],
            'tinggi_badan_cm' => ['required', 'numeric', 'min:30', 'max:150'],
            'imunisasi' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ], [
            'balita_id.required' => 'Balita wajib dipilih.',
            'balita_id.exists' => 'Data balita tidak ditemukan.',
            'tanggal_periksa.required' => 'Tanggal pemeriksaan wajib diisi.',
            'tanggal_periksa.date' => 'Format tanggal pemeriksaan tidak valid.',
            'tanggal_periksa.before_or_equal' => 'Tanggal pemeriksaan tidak boleh melebihi hari ini.',
            'berat_badan_kg.required' => 'Berat badan wajib diisi.',
            'berat_badan_kg.numeric' => 'Berat badan harus berupa angka.',
            'berat_badan_kg.min' => 'Berat badan minimal 1 kg.',
            'berat_badan_kg.max' => 'Berat badan maksimal 50 kg.',
            'tinggi_badan_cm.required' => 'Tinggi badan wajib diisi.',
            'tinggi_badan_cm.numeric' => 'Tinggi badan harus berupa angka.',
            'tinggi_badan_cm.min' => 'Tinggi badan minimal 30 cm.',
            'tinggi_badan_cm.max' => 'Tinggi badan maksimal 150 cm.',
            'imunisasi.max' => 'Keterangan imunisasi maksimal 255 karakter.',
            'catatan.max' => 'Catatan maksimal 255 karakter.',
        ]);

        $balita = Balita::findOrFail($validated['balita_id']);

        $tglLahir = $balita->tanggal_lahir->copy()->startOfDay();
        $tglPeriksa = Carbon::parse($validated['tanggal_periksa'])->startOfDay();

        if ($tglPeriksa->lt($tglLahir)) {
            return back()
                ->withInput()
                ->with('error', 'Tanggal pemeriksaan tidak boleh lebih awal dari tanggal lahir balita ('.$tglLahir->format('d/m/Y').').');
        }

        $usiaBulan = min(255, $balita->hitungUsiaBulanPada($validated['tanggal_periksa']));

        PosyanduBalita::create([
            'rt_id' => $balita->rt_id,
            'balita_id' => $balita->id,
            'tanggal_periksa' => $validated['tanggal_periksa'],
            'usia_bulan' => $usiaBulan,
            'berat_badan_kg' => $validated['berat_badan_kg'],
            'tinggi_badan_cm' => $validated['tinggi_badan_cm'],
            'imunisasi' => ! empty($validated['imunisasi']) ? $validated['imunisasi'] : null,
            'catatan' => ! empty($validated['catatan']) ? $validated['catatan'] : null,
        ]);

        return back()->with('success', "Data pemeriksaan Posyandu untuk {$balita->nama_balita} berhasil disimpan.");
    }

    public function riwayat(Request $request): View
    {
        $selectedBulan = $request->has('bulan')
            ? (string) $request->input('bulan')
            : (string) now()->month;
        $selectedTahun = (int) $request->input('tahun', now()->year);

        $query = PosyanduBalita::with(['rt', 'balita', 'creator']);

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('imunisasi', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%")
                    ->orWhereHas('balita', function ($bQ) use ($search) {
                        $bQ->where('nama_balita', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%")
                            ->orWhere('nama_ibu', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $query->where('rt_id', $request->input('rt_id'));
        }

        if ($selectedBulan !== 'all' && $selectedBulan !== '') {
            $query->whereMonth('tanggal_periksa', (int) $selectedBulan);
        }

        if ($selectedTahun > 0) {
            $query->whereYear('tanggal_periksa', $selectedTahun);
        }

        $riwayatList = $query
            ->orderByDesc('tanggal_periksa')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $rts = Rt::orderBy('nomor_rt')->get();
        $bulanList = self::BULAN_LIST;
        $tahunList = range(now()->year - 2, now()->year + 1);

        return view('posyandu.riwayat', compact(
            'riwayatList',
            'rts',
            'bulanList',
            'tahunList',
            'selectedBulan',
            'selectedTahun'
        ));
    }

    public function showBalita(Balita $balita): View
    {
        $balita->load('rt');

        $chronological = PosyanduBalita::with('creator')
            ->where('balita_id', $balita->id)
            ->orderBy('tanggal_periksa')
            ->orderBy('id')
            ->get();

        $prev = null;
        foreach ($chronological as $record) {
            $record->berat_diff = $prev
                ? round((float) $record->berat_badan_kg - (float) $prev->berat_badan_kg, 2)
                : null;
            $record->tinggi_diff = $prev
                ? round((float) $record->tinggi_badan_cm - (float) $prev->tinggi_badan_cm, 2)
                : null;
            $prev = $record;
        }

        $chartLabels = $chronological
            ->map(fn ($r) => $r->usia_bulan.' bln ('.$r->tanggal_periksa->format('d/m/y').')')
            ->values();
        $chartBerat = $chronological
            ->map(fn ($r) => (float) $r->berat_badan_kg)
            ->values();
        $chartTinggi = $chronological
            ->map(fn ($r) => (float) $r->tinggi_badan_cm)
            ->values();

        $riwayat = $chronological->reverse()->values();
        $imunisasiChips = self::IMUNISASI_CHIPS;

        return view('posyandu.balita-show', compact(
            'balita',
            'riwayat',
            'chartLabels',
            'chartBerat',
            'chartTinggi',
            'imunisasiChips'
        ));
    }

    public function destroyPeriksa(PosyanduBalita $posyanduBalita): RedirectResponse
    {
        $posyanduBalita->delete();

        return back()->with('success', 'Catatan pemeriksaan berhasil dihapus.');
    }
}
