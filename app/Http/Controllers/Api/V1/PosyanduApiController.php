<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Balita;
use App\Models\PosyanduBalita;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PosyanduApiController extends Controller
{
    use ApiResponse;

    public function indexBalita(Request $request): JsonResponse
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();
        $filterRtId = ($isSuperAdmin && $request->filled('rt_id'))
            ? (int) $request->input('rt_id')
            : null;

        $currentMonth = now()->month;
        $currentYear = now()->year;

        $query = Balita::with(['rt', 'pemeriksaanTerakhir'])
            ->where('status_aktif', 'aktif')
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId));

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_balita', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nama_ibu', 'like', "%{$search}%")
                    ->orWhere('nama_ayah', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status_bulan_ini')) {
            $status = $request->input('status_bulan_ini');
            if ($status === 'sudah') {
                $query->whereHas('posyanduBalita', function ($pq) use ($currentMonth, $currentYear) {
                    $pq->whereMonth('tanggal_periksa', $currentMonth)
                        ->whereYear('tanggal_periksa', $currentYear);
                });
            } elseif ($status === 'belum') {
                $query->whereDoesntHave('posyanduBalita', function ($pq) use ($currentMonth, $currentYear) {
                    $pq->whereMonth('tanggal_periksa', $currentMonth)
                        ->whereYear('tanggal_periksa', $currentYear);
                });
            }
        }

        $checkedThisMonthIds = PosyanduBalita::whereMonth('tanggal_periksa', $currentMonth)
            ->whereYear('tanggal_periksa', $currentYear)
            ->when($filterRtId, fn ($q) => $q->where('rt_id', $filterRtId))
            ->distinct()
            ->pluck('balita_id')
            ->flip();

        $result = $query->orderByDesc('tanggal_lahir')->paginate(15);

        $result->getCollection()->transform(function (Balita $balita) use ($checkedThisMonthIds) {
            $balita->append(['usia_bulan', 'usia_format']);
            $balita->setAttribute('sudah_ditimbang_bulan_ini', $checkedThisMonthIds->has($balita->id));
            if ($balita->pemeriksaanTerakhir) {
                $balita->pemeriksaanTerakhir->append('usia_format');
            }

            return $balita;
        });

        return $this->successResponse($result, 'Daftar balita Posyandu berhasil dimuat.');
    }

    public function storeBalita(Request $request): JsonResponse
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
            'status_aktif' => ['nullable', Rule::in(['aktif', 'lulus', 'pindah'])],
        ]);

        $balita = Balita::create([
            'rt_id' => $isSuperAdmin ? $validated['rt_id'] : $request->user()->rt_id,
            'nama_balita' => $validated['nama_balita'],
            'nik' => ! empty($validated['nik']) ? $validated['nik'] : null,
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'nama_ibu' => $validated['nama_ibu'],
            'nama_ayah' => ! empty($validated['nama_ayah']) ? $validated['nama_ayah'] : null,
            'alamat' => ! empty($validated['alamat']) ? $validated['alamat'] : null,
            'no_hp_ortu' => ! empty($validated['no_hp_ortu']) ? $validated['no_hp_ortu'] : null,
            'status_aktif' => $validated['status_aktif'] ?? 'aktif',
        ]);

        $balita->load('rt')->append(['usia_bulan', 'usia_format']);

        return $this->successResponse($balita, 'Data balita berhasil ditambahkan.', 201);
    }

    public function showBalita(Balita $balita): JsonResponse
    {
        $balita->load([
            'rt',
            'posyanduBalita' => function ($query) {
                $query->with('creator')
                    ->orderBy('tanggal_periksa')
                    ->orderBy('id');
            },
        ]);

        $balita->append(['usia_bulan', 'usia_format']);
        $balita->posyanduBalita->each->append('usia_format');

        return $this->successResponse($balita, 'Detail profil dan riwayat tumbuh kembang balita berhasil dimuat.');
    }

    public function storePeriksa(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'balita_id' => ['required', 'exists:balita,id'],
            'tanggal_periksa' => ['required', 'date', 'before_or_equal:today'],
            'berat_badan_kg' => ['required', 'numeric', 'min:1', 'max:50'],
            'tinggi_badan_cm' => ['required', 'numeric', 'min:30', 'max:150'],
            'imunisasi' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ]);

        $balita = Balita::findOrFail($validated['balita_id']);

        $tglLahir = $balita->tanggal_lahir->copy()->startOfDay();
        $tglPeriksa = Carbon::parse($validated['tanggal_periksa'])->startOfDay();

        if ($tglPeriksa->lt($tglLahir)) {
            return $this->errorResponse(
                'Tanggal pemeriksaan tidak boleh lebih awal dari tanggal lahir balita ('.$tglLahir->format('d/m/Y').').',
                422
            );
        }

        $usiaBulan = min(255, $balita->hitungUsiaBulanPada($validated['tanggal_periksa']));

        $periksa = PosyanduBalita::create([
            'rt_id' => $balita->rt_id,
            'balita_id' => $balita->id,
            'tanggal_periksa' => $validated['tanggal_periksa'],
            'usia_bulan' => $usiaBulan,
            'berat_badan_kg' => $validated['berat_badan_kg'],
            'tinggi_badan_cm' => $validated['tinggi_badan_cm'],
            'imunisasi' => ! empty($validated['imunisasi']) ? $validated['imunisasi'] : null,
            'catatan' => ! empty($validated['catatan']) ? $validated['catatan'] : null,
        ]);

        $periksa->load(['balita', 'creator'])->append('usia_format');

        return $this->successResponse($periksa, 'Data pemeriksaan Posyandu berhasil disimpan.', 201);
    }

    public function riwayat(Request $request): JsonResponse
    {
        $query = PosyanduBalita::with(['balita', 'creator']);

        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $query->where('rt_id', $request->input('rt_id'));
        }

        if ($request->filled('balita_id')) {
            $query->where('balita_id', $request->input('balita_id'));
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_periksa', (int) $request->input('bulan'));
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_periksa', (int) $request->input('tahun'));
        }

        $result = $query
            ->orderByDesc('tanggal_periksa')
            ->orderByDesc('id')
            ->paginate(20);

        $result->getCollection()->each->append('usia_format');

        return $this->successResponse($result, 'Riwayat pemeriksaan Posyandu berhasil dimuat.');
    }
}

