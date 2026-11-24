<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public const SETTING_FIELDS = [
        'nama_rw' => 'Nama RW',
        'kelurahan' => 'Nama Kelurahan',
        'kecamatan' => 'Nama Kecamatan',
        'kota' => 'Nama Kota / Kabupaten',
        'nama_ketua_rw' => 'Nama Ketua RW',
    ];

    public function index(): View
    {
        $settings = Setting::whereIn('key', array_keys(self::SETTING_FIELDS))
            ->get()
            ->keyBy('key');

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_rw' => ['required', 'string', 'max:100'],
            'kelurahan' => ['required', 'string', 'max:150'],
            'kecamatan' => ['required', 'string', 'max:150'],
            'kota' => ['required', 'string', 'max:150'],
            'nama_ketua_rw' => ['required', 'string', 'max:150'],
        ], [
            'nama_rw.required' => 'Nama RW wajib diisi.',
            'kelurahan.required' => 'Nama Kelurahan wajib diisi.',
            'kecamatan.required' => 'Nama Kecamatan wajib diisi.',
            'kota.required' => 'Nama Kota / Kabupaten wajib diisi.',
            'nama_ketua_rw.required' => 'Nama Ketua RW wajib diisi.',
        ]);

        foreach (self::SETTING_FIELDS as $key => $description) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $validated[$key],
                    'group' => 'general',
                    'description' => $description,
                ]
            );
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Pengaturan identitas RW berhasil disimpan.');
    }
}

