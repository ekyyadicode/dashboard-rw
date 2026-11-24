<?php

namespace App\Http\Controllers;

use App\Models\KartuKeluarga;
use App\Models\Rt;
use App\Models\Warga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WargaController extends Controller
{
    public function index(Request $request): View
    {
        $query = Warga::with(['rt', 'kartuKeluarga']);

        if ($request->filled('q')) {
            $search = trim((string) $request->input('q'));
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $query->where('rt_id', $request->input('rt_id'));
        }

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->input('jenis_kelamin'));
        }

        if ($request->filled('status_warga')) {
            $query->where('status_warga', $request->input('status_warga'));
        }

        if ($request->filled('kategori')) {
            $kategori = $request->input('kategori');
            if ($kategori === 'balita') {
                $query->whereDate('tanggal_lahir', '>', now()->subYears(5));
            } elseif ($kategori === 'lansia') {
                $query->whereDate('tanggal_lahir', '<=', now()->subYears(60));
            }
        }

        $wargaList = $query->latest()->paginate(20)->withQueryString();
        $rts = Rt::orderBy('nomor_rt')->get();

        return view('warga.individu.index', compact('wargaList', 'rts'));
    }

    public function store(Request $request, KartuKeluarga $kartuKeluarga): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16', 'unique:warga,nik'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'status_keluarga' => ['required', Rule::in(['kepala_keluarga', 'istri', 'anak', 'famili_lain'])],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar di sistem.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki (L) atau Perempuan (P).',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'status_keluarga.required' => 'Status dalam keluarga wajib dipilih.',
            'status_keluarga.in' => 'Status dalam keluarga tidak valid.',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter.',
        ]);

        Warga::create([
            'rt_id' => $kartuKeluarga->rt_id,
            'kartu_keluarga_id' => $kartuKeluarga->id,
            'nik' => $validated['nik'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'status_keluarga' => $validated['status_keluarga'],
            'no_hp' => $validated['no_hp'] ?: null,
            'status_warga' => 'aktif',
        ]);

        return redirect()
            ->route('warga.kk.show', $kartuKeluarga)
            ->with('success', 'Anggota keluarga berhasil ditambahkan.');
    }

    public function edit(Warga $warga): View
    {
        $warga->load(['rt', 'kartuKeluarga']);

        return view('warga.anggota.edit', compact('warga'));
    }

    public function update(Request $request, Warga $warga): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'digits:16', Rule::unique('warga', 'nik')->ignore($warga->id)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'status_keluarga' => ['required', Rule::in(['kepala_keluarga', 'istri', 'anak', 'famili_lain'])],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'status_warga' => ['required', Rule::in(['aktif', 'pindah', 'meninggal'])],
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'nik.unique' => 'NIK sudah terdaftar pada warga lain.',
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh melebihi hari ini.',
            'status_keluarga.required' => 'Status dalam keluarga wajib dipilih.',
            'no_hp.max' => 'Nomor HP maksimal 20 karakter.',
            'status_warga.required' => 'Status warga wajib dipilih.',
        ]);

        $warga->update([
            'nik' => $validated['nik'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'status_keluarga' => $validated['status_keluarga'],
            'no_hp' => $validated['no_hp'] ?: null,
            'status_warga' => $validated['status_warga'],
        ]);

        return redirect()
            ->route('warga.kk.show', $warga->kartu_keluarga_id)
            ->with('success', 'Data anggota warga berhasil diperbarui.');
    }

    public function destroy(Warga $warga): RedirectResponse
    {
        $kartuKeluargaId = $warga->kartu_keluarga_id;
        $namaLengkap = $warga->nama_lengkap;

        $warga->delete();

        return redirect()
            ->route('warga.kk.show', $kartuKeluargaId)
            ->with('success', 'Data warga '.$namaLengkap.' berhasil dihapus.');
    }
}

