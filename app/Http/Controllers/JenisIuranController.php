<?php

namespace App\Http\Controllers;

use App\Models\JenisIuran;
use App\Models\Rt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JenisIuranController extends Controller
{
    public function index(Request $request): View
    {
        $query = JenisIuran::with('rt')->withCount('pembayaranIuran');

        if ($request->user()->isSuperAdmin() && $request->filled('rt_id')) {
            $query->where('rt_id', $request->input('rt_id'));
        }

        $jenisIuranList = $query->orderBy('rt_id')->latest()->get();
        $rts = Rt::orderBy('nomor_rt')->get();

        return view('iuran.jenis.index', compact('jenisIuranList', 'rts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();

        $validated = $request->validate([
            'rt_id' => $isSuperAdmin ? ['required', 'exists:rts,id'] : ['nullable'],
            'nama_iuran' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'integer', 'min:0'],
            'tipe' => ['required', Rule::in(['bulanan', 'insidental'])],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'rt_id.required' => 'Wilayah RT wajib dipilih.',
            'rt_id.exists' => 'Wilayah RT tidak valid.',
            'nama_iuran.required' => 'Nama jenis iuran wajib diisi.',
            'nama_iuran.max' => 'Nama jenis iuran maksimal 255 karakter.',
            'nominal.required' => 'Nominal iuran wajib diisi.',
            'nominal.integer' => 'Nominal iuran harus berupa angka bulat.',
            'nominal.min' => 'Nominal iuran tidak boleh kurang dari 0.',
            'tipe.required' => 'Tipe iuran wajib dipilih.',
            'tipe.in' => 'Tipe iuran harus Bulanan atau Insidental.',
        ]);

        JenisIuran::create([
            'rt_id' => $isSuperAdmin ? $validated['rt_id'] : $request->user()->rt_id,
            'nama_iuran' => $validated['nama_iuran'],
            'nominal' => $validated['nominal'],
            'tipe' => $validated['tipe'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        return redirect()
            ->route('iuran.jenis.index')
            ->with('success', 'Jenis iuran berhasil ditambahkan.');
    }

    public function edit(JenisIuran $jenisIuran): View
    {
        $jenisIuran->load('rt')->loadCount('pembayaranIuran');
        $rts = Rt::orderBy('nomor_rt')->get();

        return view('iuran.jenis.edit', compact('jenisIuran', 'rts'));
    }

    public function update(Request $request, JenisIuran $jenisIuran): RedirectResponse
    {
        $isSuperAdmin = $request->user()->isSuperAdmin();
        $hasPayments = $jenisIuran->pembayaranIuran()->exists();

        $validated = $request->validate([
            'rt_id' => ($isSuperAdmin && ! $hasPayments) ? ['required', 'exists:rts,id'] : ['nullable'],
            'nama_iuran' => ['required', 'string', 'max:255'],
            'nominal' => ['required', 'integer', 'min:0'],
            'tipe' => ['required', Rule::in(['bulanan', 'insidental'])],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'rt_id.required' => 'Wilayah RT wajib dipilih.',
            'rt_id.exists' => 'Wilayah RT tidak valid.',
            'nama_iuran.required' => 'Nama jenis iuran wajib diisi.',
            'nama_iuran.max' => 'Nama jenis iuran maksimal 255 karakter.',
            'nominal.required' => 'Nominal iuran wajib diisi.',
            'nominal.integer' => 'Nominal iuran harus berupa angka bulat.',
            'nominal.min' => 'Nominal iuran tidak boleh kurang dari 0.',
            'tipe.required' => 'Tipe iuran wajib dipilih.',
            'tipe.in' => 'Tipe iuran harus Bulanan atau Insidental.',
        ]);

        $data = [
            'nama_iuran' => $validated['nama_iuran'],
            'nominal' => $validated['nominal'],
            'tipe' => $validated['tipe'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($isSuperAdmin && ! $hasPayments && ! empty($validated['rt_id'])) {
            $data['rt_id'] = $validated['rt_id'];
        }

        $jenisIuran->update($data);

        return redirect()
            ->route('iuran.jenis.index')
            ->with('success', 'Data jenis iuran berhasil diperbarui.');
    }

    public function destroy(JenisIuran $jenisIuran): RedirectResponse
    {
        if ($jenisIuran->pembayaranIuran()->exists()) {
            return redirect()
                ->route('iuran.jenis.index')
                ->with('error', 'Jenis iuran tidak bisa dihapus karena sudah memiliki riwayat pembayaran. Nonaktifkan saja statusnya.');
        }

        $jenisIuran->delete();

        return redirect()
            ->route('iuran.jenis.index')
            ->with('success', 'Jenis iuran berhasil dihapus.');
    }
}

