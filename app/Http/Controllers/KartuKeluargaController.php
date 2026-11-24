<?php

namespace App\Http\Controllers;

use App\Models\KartuKeluarga;
use App\Models\Rt;
use App\Models\Warga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KartuKeluargaController extends Controller
{
    public function index(Request $request): View
    {
        $query = KartuKeluarga::with(['rt', 'kepalaKeluarga'])->withCount('warga');

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('no_kk', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('nomor_rumah', 'like', "%{$search}%")
                    ->orWhereHas('warga', function ($wargaQuery) use ($search) {
                        $wargaQuery->where('nama_lengkap', 'like', "%{$search}%")
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

        if ($request->filled('status_aktif')) {
            $query->where('status_aktif', $request->input('status_aktif'));
        }

        $kartuKeluargaList = $query->latest()->paginate(15)->withQueryString();
        $rts = Rt::orderBy('nomor_rt')->get();

        return view('warga.kk.index', compact('kartuKeluargaList', 'rts'));
    }

    public function create(): View
    {
        $rts = Rt::orderBy('nomor_rt')->get();

        return view('warga.kk.create', compact('rts'));
    }

    public function store(Request $request): RedirectResponse
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
            'rt_id.exists' => 'Wilayah RT yang dipilih tidak valid.',
            'no_kk.required' => 'Nomor KK wajib diisi.',
            'no_kk.digits' => 'Nomor KK harus terdiri dari tepat 16 digit angka.',
            'no_kk.unique' => 'Nomor KK sudah terdaftar di sistem.',
            'alamat.required' => 'Alamat lengkap wajib diisi.',
            'nomor_rumah.required' => 'Nomor rumah wajib diisi.',
            'nomor_rumah.max' => 'Nomor rumah maksimal 20 karakter.',
            'status_domisili.required' => 'Status domisili wajib dipilih.',
            'status_domisili.in' => 'Status domisili tidak valid.',
            'status_aktif.required' => 'Status aktif KK wajib dipilih.',
            'status_aktif.in' => 'Status aktif KK tidak valid.',
            'anggota.required' => 'Minimal harus ada 1 anggota keluarga.',
            'anggota.min' => 'Minimal harus ada 1 anggota keluarga.',
            'anggota.*.nik.required' => 'NIK anggota wajib diisi.',
            'anggota.*.nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'anggota.*.nik.distinct' => 'NIK antar anggota dalam satu form tidak boleh sama.',
            'anggota.*.nik.unique' => 'NIK sudah terdaftar di sistem.',
            'anggota.*.nama_lengkap.required' => 'Nama lengkap anggota wajib diisi.',
            'anggota.*.nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter.',
            'anggota.*.jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'anggota.*.jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki (L) atau Perempuan (P).',
            'anggota.*.tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'anggota.*.tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'anggota.*.tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'anggota.*.status_keluarga.required' => 'Status dalam keluarga wajib dipilih.',
            'anggota.*.status_keluarga.in' => 'Status dalam keluarga tidak valid.',
            'anggota.*.no_hp.max' => 'Nomor HP maksimal 20 karakter.',
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

        return redirect()
            ->route('warga.kk.show', $kk)
            ->with('success', 'Kartu Keluarga beserta '.count($anggotaList).' anggota keluarga berhasil disimpan.');
    }

    public function show(KartuKeluarga $kartuKeluarga): View
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

        return view('warga.kk.show', compact('kartuKeluarga'));
    }

    public function edit(KartuKeluarga $kartuKeluarga): View
    {
        $rts = Rt::orderBy('nomor_rt')->get();

        return view('warga.kk.edit', compact('kartuKeluarga', 'rts'));
    }

    public function update(Request $request, KartuKeluarga $kartuKeluarga): RedirectResponse
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();

        $validated = $request->validate([
            'rt_id' => $isSuperAdmin ? ['required', 'exists:rts,id'] : ['nullable'],
            'no_kk' => ['required', 'digits:16', Rule::unique('kartu_keluarga', 'no_kk')->ignore($kartuKeluarga->id)],
            'alamat' => ['required', 'string'],
            'nomor_rumah' => ['required', 'string', 'max:20'],
            'status_domisili' => ['required', Rule::in(['tetap', 'kontrak'])],
            'status_aktif' => ['required', Rule::in(['aktif', 'pindah'])],
        ], [
            'rt_id.required' => 'Wilayah RT wajib dipilih.',
            'rt_id.exists' => 'Wilayah RT yang dipilih tidak valid.',
            'no_kk.required' => 'Nomor KK wajib diisi.',
            'no_kk.digits' => 'Nomor KK harus terdiri dari tepat 16 digit angka.',
            'no_kk.unique' => 'Nomor KK sudah terdaftar pada Kartu Keluarga lain.',
            'alamat.required' => 'Alamat lengkap wajib diisi.',
            'nomor_rumah.required' => 'Nomor rumah wajib diisi.',
            'nomor_rumah.max' => 'Nomor rumah maksimal 20 karakter.',
            'status_domisili.required' => 'Status domisili wajib dipilih.',
            'status_aktif.required' => 'Status aktif KK wajib dipilih.',
        ]);

        DB::transaction(function () use ($validated, $kartuKeluarga, $isSuperAdmin) {
            $oldRtId = (int) $kartuKeluarga->rt_id;
            $newRtId = $isSuperAdmin ? (int) $validated['rt_id'] : $oldRtId;

            $kartuKeluarga->update([
                'rt_id' => $newRtId,
                'no_kk' => $validated['no_kk'],
                'alamat' => $validated['alamat'],
                'nomor_rumah' => $validated['nomor_rumah'],
                'status_domisili' => $validated['status_domisili'],
                'status_aktif' => $validated['status_aktif'],
            ]);

            if ($isSuperAdmin && $newRtId !== $oldRtId) {
                $kartuKeluarga->warga()->update(['rt_id' => $newRtId]);
            }
        });

        return redirect()
            ->route('warga.kk.show', $kartuKeluarga)
            ->with('success', 'Data Kartu Keluarga berhasil diperbarui.');
    }

    public function destroy(KartuKeluarga $kartuKeluarga): RedirectResponse
    {
        if ($kartuKeluarga->pembayaranIuran()->exists()) {
            return redirect()
                ->route('warga.index')
                ->with('error', 'Kartu Keluarga '.$kartuKeluarga->no_kk.' tidak dapat dihapus karena masih memiliki riwayat pembayaran iuran. Ubah status KK menjadi "Pindah" agar riwayat keuangan tetap terjaga.');
        }

        DB::transaction(function () use ($kartuKeluarga) {
            $kartuKeluarga->warga()->delete();
            $kartuKeluarga->delete();
        });

        return redirect()
            ->route('warga.index')
            ->with('success', 'Data Kartu Keluarga beserta seluruh anggotanya berhasil dihapus.');
    }
}

