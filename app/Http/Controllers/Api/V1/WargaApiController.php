<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KartuKeluarga;
use App\Models\Warga;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WargaApiController extends Controller
{
    use ApiResponse;

    public function indexKk(Request $request): JsonResponse
    {
        $query = KartuKeluarga::with(['rt', 'kepalaKeluarga'])->withCount('warga');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('no_kk', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('nomor_rumah', 'like', "%{$search}%")
                    ->orWhereHas('warga', function ($wQ) use ($search) {
                        $wQ->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nik', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $query->where('rt_id', $request->input('rt_id'));
        }

        if ($request->filled('status_domisili')) {
            $query->where('status_domisili', $request->input('status_domisili'));
        }

        $statusAktif = $request->has('status_aktif')
            ? (string) $request->input('status_aktif')
            : 'aktif';

        if ($statusAktif !== 'all' && $statusAktif !== '') {
            $query->where('status_aktif', $statusAktif);
        }

        $result = $query->latest()->paginate(15);

        return $this->successResponse($result, 'Daftar Kartu Keluarga berhasil dimuat.');
    }

    public function storeKk(Request $request): JsonResponse
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();

        $validated = $request->validate([
            'rt_id' => $isSuperAdmin ? ['required', 'exists:rts,id'] : ['nullable'],
            'no_kk' => ['required', 'digits:16', 'unique:kartu_keluarga,no_kk'],
            'alamat' => ['required', 'string'],
            'nomor_rumah' => ['required', 'string', 'max:20'],
            'status_domisili' => ['required', Rule::in(['tetap', 'kontrak'])],
            'status_aktif' => ['required', Rule::in(['aktif', 'pindah'])],
            'anggota' => ['required', 'array', 'min:1'],
            'anggota.*.nik' => ['required', 'digits:16', 'distinct', 'unique:warga,nik'],
            'anggota.*.nama_lengkap' => ['required', 'string', 'max:255'],
            'anggota.*.jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'anggota.*.tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'anggota.*.status_keluarga' => ['required', Rule::in(['kepala_keluarga', 'istri', 'anak', 'famili_lain'])],
            'anggota.*.no_hp' => ['nullable', 'string', 'max:20'],
        ], [
            'rt_id.required' => 'Wilayah RT wajib dipilih.',
            'no_kk.digits' => 'Nomor KK harus terdiri dari tepat 16 digit angka.',
            'no_kk.unique' => 'Nomor KK sudah terdaftar.',
            'anggota.required' => 'Minimal harus ada 1 anggota keluarga.',
            'anggota.*.nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'anggota.*.nik.distinct' => 'NIK antar anggota dalam satu form tidak boleh sama.',
            'anggota.*.nik.unique' => 'NIK sudah terdaftar.',
        ]);

        $rtId = $isSuperAdmin ? $validated['rt_id'] : $request->user()->rt_id;
        $anggotaList = $validated['anggota'];

        $kk = DB::transaction(function () use ($validated, $rtId, $anggotaList) {
            $kk = KartuKeluarga::create([
                'rt_id' => $rtId,
                'no_kk' => $validated['no_kk'],
                'alamat' => $validated['alamat'],
                'nomor_rumah' => $validated['nomor_rumah'],
                'status_domisili' => $validated['status_domisili'],
                'status_aktif' => $validated['status_aktif'],
            ]);

            foreach ($anggotaList as $anggota) {
                Warga::create([
                    'rt_id' => $kk->rt_id,
                    'kartu_keluarga_id' => $kk->id,
                    'nik' => $anggota['nik'],
                    'nama_lengkap' => $anggota['nama_lengkap'],
                    'jenis_kelamin' => $anggota['jenis_kelamin'],
                    'tanggal_lahir' => $anggota['tanggal_lahir'],
                    'status_keluarga' => $anggota['status_keluarga'],
                    'no_hp' => ! empty($anggota['no_hp']) ? $anggota['no_hp'] : null,
                    'status_warga' => 'aktif',
                ]);
            }

            return $kk;
        });

        $kk->load(['rt', 'warga']);
        $kk->warga->each->append('usia_bulan');

        return $this->successResponse(
            $kk,
            'Kartu Keluarga beserta '.count($anggotaList).' anggota keluarga berhasil disimpan.',
            201
        );
    }

    public function showKk(KartuKeluarga $kartuKeluarga): JsonResponse
    {
        $kartuKeluarga->load([
            'rt',
            'kepalaKeluarga',
            'warga' => function ($query) {
                $query->orderByRaw("CASE status_keluarga
                    WHEN 'kepala_keluarga' THEN 1
                    WHEN 'istri' THEN 2
                    WHEN 'anak' THEN 3
                    WHEN 'famili_lain' THEN 4
                    ELSE 5
                END")->orderBy('tanggal_lahir', 'asc');
            },
        ]);

        $kartuKeluarga->warga->each->append('usia_bulan');

        return $this->successResponse($kartuKeluarga, 'Detail Kartu Keluarga berhasil dimuat.');
    }

    public function storeAnggota(Request $request, KartuKeluarga $kartuKeluarga): JsonResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16', 'unique:warga,nik'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'status_keluarga' => ['required', Rule::in(['kepala_keluarga', 'istri', 'anak', 'famili_lain'])],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ], [
            'nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar.',
        ]);

        $warga = Warga::create([
            'rt_id' => $kartuKeluarga->rt_id,
            'kartu_keluarga_id' => $kartuKeluarga->id,
            'nik' => $validated['nik'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'status_keluarga' => $validated['status_keluarga'],
            'no_hp' => ! empty($validated['no_hp']) ? $validated['no_hp'] : null,
            'status_warga' => 'aktif',
        ]);

        $warga->append('usia_bulan');

        return $this->successResponse($warga, 'Anggota keluarga berhasil ditambahkan.', 201);
    }

    public function indexWarga(Request $request): JsonResponse
    {
        $query = Warga::with(['rt', 'kartuKeluarga']);

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $query->where('rt_id', $request->input('rt_id'));
        }

        if ($request->filled('status_warga')) {
            $query->where('status_warga', $request->input('status_warga'));
        }

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->input('jenis_kelamin'));
        }

        $result = $query->latest()->paginate(20);
        $result->getCollection()->each->append('usia_bulan');

        return $this->successResponse($result, 'Daftar warga berhasil dimuat.');
    }
}

