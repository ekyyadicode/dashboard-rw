@extends('layouts.app')

@section('title', 'Edit Data Warga')
@section('page-title', 'Edit Data Anggota Warga')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-5">
            <a href="{{ route('warga.kk.show', $warga->kartu_keluarga_id) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-teal-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Detail Kartu Keluarga</span>
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <div class="pb-4 mb-5 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900">Edit Warga: {{ $warga->nama_lengkap }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    No. KK: <span class="font-mono font-medium text-slate-700">{{ $warga->kartuKeluarga?->no_kk }}</span>
                    &middot; Wilayah RT {{ $warga->rt?->nomor_rt ?? '-' }}
                </p>
            </div>

            <form method="POST" action="{{ route('warga.anggota.update', $warga) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nik" class="block text-sm font-medium text-slate-700 mb-1.5">
                            NIK (16 Digit) <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="nik"
                            name="nik"
                            type="text"
                            inputmode="numeric"
                            maxlength="16"
                            value="{{ old('nik', $warga->nik) }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('nik')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama_lengkap" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="nama_lengkap"
                            name="nama_lengkap"
                            type="text"
                            value="{{ old('nama_lengkap', $warga->nama_lengkap) }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('nama_lengkap')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="jenis_kelamin" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="jenis_kelamin"
                            name="jenis_kelamin"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="L" {{ old('jenis_kelamin', $warga->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                            <option value="P" {{ old('jenis_kelamin', $warga->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                        </select>
                        @error('jenis_kelamin')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tanggal_lahir" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Tanggal Lahir <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="tanggal_lahir"
                            name="tanggal_lahir"
                            type="date"
                            max="{{ date('Y-m-d') }}"
                            value="{{ old('tanggal_lahir', $warga->tanggal_lahir?->format('Y-m-d')) }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('tanggal_lahir')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="status_keluarga" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Status Keluarga <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status_keluarga"
                            name="status_keluarga"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="kepala_keluarga" {{ old('status_keluarga', $warga->status_keluarga) === 'kepala_keluarga' ? 'selected' : '' }}>Kepala Keluarga</option>
                            <option value="istri" {{ old('status_keluarga', $warga->status_keluarga) === 'istri' ? 'selected' : '' }}>Istri</option>
                            <option value="anak" {{ old('status_keluarga', $warga->status_keluarga) === 'anak' ? 'selected' : '' }}>Anak</option>
                            <option value="famili_lain" {{ old('status_keluarga', $warga->status_keluarga) === 'famili_lain' ? 'selected' : '' }}>Famili Lain</option>
                        </select>
                        @error('status_keluarga')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="no_hp" class="block text-sm font-medium text-slate-700 mb-1.5">
                            No. HP <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="no_hp"
                            name="no_hp"
                            type="text"
                            value="{{ old('no_hp', $warga->no_hp) }}"
                            placeholder="081234567890"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('no_hp')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status_warga" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Status Warga <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status_warga"
                            name="status_warga"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="aktif" {{ old('status_warga', $warga->status_warga) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="pindah" {{ old('status_warga', $warga->status_warga) === 'pindah' ? 'selected' : '' }}>Pindah</option>
                            <option value="meninggal" {{ old('status_warga', $warga->status_warga) === 'meninggal' ? 'selected' : '' }}>Meninggal</option>
                        </select>
                        @error('status_warga')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <a
                        href="{{ route('warga.kk.show', $warga->kartu_keluarga_id) }}"
                        class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                    >
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Perbarui Data Warga</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

