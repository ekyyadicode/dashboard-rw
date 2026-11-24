@extends('layouts.app')

@section('title', 'Pengaturan RW')
@section('page-title', 'Pengaturan Identitas RW')

@section('content')
    <div class="max-w-2xl">
        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <div class="mb-6">
                <h2 class="text-base font-semibold text-slate-900">Identitas Wilayah RW</h2>
                <p class="text-xs text-slate-500 mt-0.5">Informasi ini ditampilkan pada halaman login, sidebar, dan laporan administrasi RW.</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="nama_rw" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Nama RW <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="nama_rw"
                        name="nama_rw"
                        type="text"
                        value="{{ old('nama_rw', $settings['nama_rw']->value ?? '') }}"
                        required
                        placeholder="Contoh: RW 02"
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                    @error('nama_rw')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="kelurahan" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Kelurahan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="kelurahan"
                            name="kelurahan"
                            type="text"
                            value="{{ old('kelurahan', $settings['kelurahan']->value ?? '') }}"
                            required
                            placeholder="Nama kelurahan"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('kelurahan')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="kecamatan" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Kecamatan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="kecamatan"
                            name="kecamatan"
                            type="text"
                            value="{{ old('kecamatan', $settings['kecamatan']->value ?? '') }}"
                            required
                            placeholder="Nama kecamatan"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('kecamatan')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="kota" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Kota / Kabupaten <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="kota"
                        name="kota"
                        type="text"
                        value="{{ old('kota', $settings['kota']->value ?? '') }}"
                        required
                        placeholder="Contoh: Kota Tangerang"
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                    @error('kota')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama_ketua_rw" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Nama Ketua RW <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="nama_ketua_rw"
                        name="nama_ketua_rw"
                        type="text"
                        value="{{ old('nama_ketua_rw', $settings['nama_ketua_rw']->value ?? '') }}"
                        required
                        placeholder="Nama lengkap Ketua RW"
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                    @error('nama_ketua_rw')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-3 flex items-center justify-end border-t border-slate-100">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                    >
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Pengaturan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

