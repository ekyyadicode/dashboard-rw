<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RtController extends Controller
{
    public function index(): View
    {
        $rts = Rt::orderBy('nomor_rt')
            ->withCount([
                'kartuKeluarga as kk_count',
                'warga as warga_count',
                'users as users_count',
            ])
            ->get();

        return view('admin.rts.index', compact('rts'));
    }

    public function create(): View
    {
        return view('admin.rts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_rt' => ['required', 'string', 'max:10', 'unique:rts,nomor_rt'],
            'nama_ketua_rt' => ['nullable', 'string', 'max:255'],
            'no_hp_ketua' => ['nullable', 'string', 'max:20'],
        ], [
            'nomor_rt.required' => 'Nomor RT wajib diisi.',
            'nomor_rt.unique' => 'Nomor RT sudah terdaftar.',
        ]);

        Rt::create($validated);

        return redirect()
            ->route('admin.rts.index')
            ->with('success', 'Data wilayah RT berhasil ditambahkan.');
    }

    public function edit(Rt $rt): View
    {
        return view('admin.rts.edit', compact('rt'));
    }

    public function update(Request $request, Rt $rt): RedirectResponse
    {
        $validated = $request->validate([
            'nomor_rt' => ['required', 'string', 'max:10', Rule::unique('rts', 'nomor_rt')->ignore($rt->id)],
            'nama_ketua_rt' => ['nullable', 'string', 'max:255'],
            'no_hp_ketua' => ['nullable', 'string', 'max:20'],
        ], [
            'nomor_rt.required' => 'Nomor RT wajib diisi.',
            'nomor_rt.unique' => 'Nomor RT sudah digunakan oleh wilayah lain.',
        ]);

        $rt->update($validated);

        return redirect()
            ->route('admin.rts.index')
            ->with('success', 'Data wilayah RT berhasil diperbarui.');
    }

    public function destroy(Rt $rt): RedirectResponse
    {
        $hasKk = $rt->kartuKeluarga()->exists();
        $hasUsers = $rt->users()->exists();

        if ($hasKk || $hasUsers) {
            $reasons = [];
            if ($hasKk) {
                $reasons[] = 'data Kartu Keluarga';
            }
            if ($hasUsers) {
                $reasons[] = 'akun Pengurus';
            }

            return redirect()
                ->route('admin.rts.index')
                ->with('error', 'RT '.$rt->nomor_rt.' tidak dapat dihapus karena masih memiliki '.implode(' dan ', $reasons).' terkait.');
        }

        $rt->delete();

        return redirect()
            ->route('admin.rts.index')
            ->with('success', 'Data wilayah RT '.$rt->nomor_rt.' berhasil dihapus.');
    }
}

