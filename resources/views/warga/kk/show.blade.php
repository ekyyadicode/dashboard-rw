@extends('layouts.app')

@section('title', 'Detail Kartu Keluarga')
@section('page-title', 'Detail Kartu Keluarga & Anggota')

@section('content')
    @php
        $statusKeluargaLabels = [
            'kepala_keluarga' => ['label' => 'Kepala Keluarga', 'class' => 'bg-teal-50 text-teal-700 border-teal-200'],
            'istri' => ['label' => 'Istri', 'class' => 'bg-sky-50 text-sky-700 border-sky-200'],
            'anak' => ['label' => 'Anak', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'famili_lain' => ['label' => 'Famili Lain', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
        ];
    @endphp

    <div>
        {{-- Top Back Link & Quick Actions --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('warga.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-teal-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar Kartu Keluarga</span>
            </a>
            <div class="flex items-center gap-2">
                <a
                    href="{{ route('warga.kk.edit', $kartuKeluarga) }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3.5 py-2 text-sm font-medium text-slate-700 transition"
                >
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                    <span>Edit Info KK</span>
                </a>
                <a
                    href="#form-tambah-anggota"
                    onclick="setTimeout(() => document.getElementById('tambah_nik')?.focus(), 150)"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition"
                >
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>+ Tambah Anggota</span>
                </a>
            </div>
        </div>

        {{-- 1. Kartu Informasi Ringkas KK --}}
        <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 pb-5 border-b border-slate-100">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nomor Kartu Keluarga</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                            RT {{ $kartuKeluarga->rt?->nomor_rt ?? '-' }}
                        </span>
                    </div>
                    <h2 class="mt-1 text-xl font-bold font-mono text-slate-900">{{ $kartuKeluarga->no_kk }}</h2>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @if($kartuKeluarga->status_domisili === 'tetap')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200">
                            Domisili Tetap
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                            Domisili Kontrak
                        </span>
                    @endif

                    @if($kartuKeluarga->status_aktif === 'aktif')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Aktif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                            Pindah
                        </span>
                    @endif
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs font-medium text-slate-400">Kepala Keluarga</p>
                    <p class="mt-1 font-semibold text-slate-900">
                        {{ $kartuKeluarga->kepalaKeluarga?->nama_lengkap ?? '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400">Nomor Rumah</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $kartuKeluarga->nomor_rumah }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400">Jumlah Anggota</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $kartuKeluarga->warga->count() }} Orang</p>
                </div>
                <div class="sm:col-span-3">
                    <p class="text-xs font-medium text-slate-400">Alamat Lengkap</p>
                    <p class="mt-1 text-slate-700">{{ $kartuKeluarga->alamat }}</p>
                </div>
            </div>
        </div>

        {{-- 2. Tabel Daftar Seluruh Anggota Keluarga --}}
        <div class="mb-6 bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Daftar Anggota Keluarga ({{ $kartuKeluarga->warga->count() }} Orang)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Diurutkan berdasarkan status hubungan dalam keluarga dan tanggal lahir.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">NIK</th>
                            <th class="px-5 py-3.5">Nama Lengkap</th>
                            <th class="px-5 py-3.5 text-center">JK</th>
                            <th class="px-5 py-3.5">Tgl Lahir / Umur</th>
                            <th class="px-5 py-3.5">Status Keluarga</th>
                            <th class="px-5 py-3.5">No. HP</th>
                            <th class="px-5 py-3.5 text-center">Status Warga</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($kartuKeluarga->warga as $anggota)
                            @php
                                $usiaBulan = $anggota->usia_bulan;
                                $usiaText = $usiaBulan !== null
                                    ? ($usiaBulan < 60 ? $usiaBulan.' bln' : floor($usiaBulan / 12).' thn')
                                    : '-';
                                $stKel = $statusKeluargaLabels[$anggota->status_keluarga]
                                    ?? ['label' => ucfirst(str_replace('_', ' ', $anggota->status_keluarga)), 'class' => 'bg-slate-100 text-slate-700 border-slate-200'];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-700 whitespace-nowrap">
                                    {{ $anggota->nik }}
                                </td>
                                <td class="px-5 py-3.5 font-semibold text-slate-900">
                                    {{ $anggota->nama_lengkap }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold {{ $anggota->jenis_kelamin === 'L' ? 'bg-sky-50 text-sky-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $anggota->jenis_kelamin }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">{{ $usiaText }}</div>
                                    <div class="text-xs text-slate-400">{{ $anggota->tanggal_lahir?->format('d/m/Y') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $stKel['class'] }}">
                                        {{ $stKel['label'] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-slate-600 whitespace-nowrap">
                                    {{ $anggota->no_hp ?: '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @if($anggota->status_warga === 'aktif')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    @elseif($anggota->status_warga === 'pindah')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                            Pindah
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            Meninggal
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a
                                            href="{{ route('warga.anggota.edit', $anggota) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-700 transition"
                                        >
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('warga.anggota.destroy', $anggota) }}"
                                            onsubmit="return confirm('Yakin ingin menghapus anggota {{ addslashes($anggota->nama_lengkap) }}?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 text-xs font-medium text-red-700 transition"
                                            >
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-slate-500">
                                    Belum ada anggota keluarga tercatat pada KK ini. Gunakan form di bawah untuk menambahkan anggota keluarga.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 3. Form Langsung Tambah Anggota Keluarga Baru (Susulan / Anak Baru Lahir) --}}
        <div id="form-tambah-anggota" class="bg-white border border-slate-200 rounded-2xl p-6 scroll-mt-20">
            <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-900">+ Tambah Anggota Keluarga Baru</h3>
                    <p class="text-xs text-slate-500">Tambahkan istri, anak baru lahir, atau famili lain ke dalam KK {{ $kartuKeluarga->no_kk }} (RT {{ $kartuKeluarga->rt?->nomor_rt }}).</p>
                </div>
            </div>

            @if($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700">
                    <p class="font-semibold mb-1">Gagal menambahkan anggota keluarga:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('warga.anggota.store', $kartuKeluarga) }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label for="tambah_nik" class="block text-sm font-medium text-slate-700 mb-1.5">
                            NIK (16 Digit) <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="tambah_nik"
                            name="nik"
                            type="text"
                            inputmode="numeric"
                            maxlength="16"
                            value="{{ old('nik') }}"
                            required
                            placeholder="16 digit NIK"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('nik')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tambah_nama_lengkap" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="tambah_nama_lengkap"
                            name="nama_lengkap"
                            type="text"
                            value="{{ old('nama_lengkap') }}"
                            required
                            placeholder="Nama lengkap sesuai KTP/KK"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('nama_lengkap')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tambah_status_keluarga" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Status Keluarga <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="tambah_status_keluarga"
                            name="status_keluarga"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="istri" {{ old('status_keluarga') === 'istri' ? 'selected' : '' }}>Istri</option>
                            <option value="anak" {{ old('status_keluarga', 'anak') === 'anak' ? 'selected' : '' }}>Anak</option>
                            <option value="famili_lain" {{ old('status_keluarga') === 'famili_lain' ? 'selected' : '' }}>Famili Lain</option>
                            <option value="kepala_keluarga" {{ old('status_keluarga') === 'kepala_keluarga' ? 'selected' : '' }}>Kepala Keluarga</option>
                        </select>
                        @error('status_keluarga')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tambah_jenis_kelamin" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="tambah_jenis_kelamin"
                            name="jenis_kelamin"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="">-- Pilih --</option>
                            <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                            <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                        </select>
                        @error('jenis_kelamin')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tambah_tanggal_lahir" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Tanggal Lahir <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="tambah_tanggal_lahir"
                            name="tanggal_lahir"
                            type="date"
                            max="{{ date('Y-m-d') }}"
                            value="{{ old('tanggal_lahir') }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('tanggal_lahir')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tambah_no_hp" class="block text-sm font-medium text-slate-700 mb-1.5">
                            No. HP <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="tambah_no_hp"
                            name="no_hp"
                            type="text"
                            value="{{ old('no_hp') }}"
                            placeholder="Contoh: 081234567890"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('no_hp')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end border-t border-slate-100">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                    >
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>Simpan Anggota Baru</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
