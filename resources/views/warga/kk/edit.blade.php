@extends('layouts.app')

@section('title', 'Edit Kartu Keluarga')
@section('page-title', 'Edit Data Kartu Keluarga')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-5">
            <a href="{{ route('warga.kk.show', $kartuKeluarga) }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-teal-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Detail Kartu Keluarga</span>
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-slate-900 mb-1">Edit KK: {{ $kartuKeluarga->no_kk }}</h2>
            <p class="text-xs text-slate-500 mb-6">Perbarui nomor KK, alamat tempat tinggal, atau status domisili keluarga.</p>

            <form method="POST" action="{{ route('warga.kk.update', $kartuKeluarga) }}" class="space-y-5">
                @csrf
                @method('PUT')

                @if(auth()->user()->isSuperAdmin())
                    <div>
                        <label for="rt_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Wilayah RT <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="rt_id"
                            name="rt_id"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}" {{ old('rt_id', $kartuKeluarga->rt_id) == $rt->id ? 'selected' : '' }}>
                                    RT {{ $rt->nomor_rt }} {{ $rt->nama_ketua_rt ? '('.$rt->nama_ketua_rt.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-400">Mengubah wilayah RT akan otomatis memindahkan wilayah RT seluruh anggota keluarga di KK ini.</p>
                        @error('rt_id')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label for="no_kk" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Nomor KK (16 Digit) <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="no_kk"
                        name="no_kk"
                        type="text"
                        inputmode="numeric"
                        maxlength="16"
                        value="{{ old('no_kk', $kartuKeluarga->no_kk) }}"
                        required
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                    @error('no_kk')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="alamat" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Alamat Lengkap <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="3"
                        required
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >{{ old('alamat', $kartuKeluarga->alamat) }}</textarea>
                    @error('alamat')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="nomor_rumah" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nomor Rumah <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="nomor_rumah"
                            name="nomor_rumah"
                            type="text"
                            value="{{ old('nomor_rumah', $kartuKeluarga->nomor_rumah) }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('nomor_rumah')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status_domisili" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Status Domisili <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status_domisili"
                            name="status_domisili"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="tetap" {{ old('status_domisili', $kartuKeluarga->status_domisili) === 'tetap' ? 'selected' : '' }}>Tetap</option>
                            <option value="kontrak" {{ old('status_domisili', $kartuKeluarga->status_domisili) === 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                        </select>
                        @error('status_domisili')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status_aktif" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Status Aktif <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status_aktif"
                            name="status_aktif"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="aktif" {{ old('status_aktif', $kartuKeluarga->status_aktif) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="pindah" {{ old('status_aktif', $kartuKeluarga->status_aktif) === 'pindah' ? 'selected' : '' }}>Pindah</option>
                        </select>
                        @error('status_aktif')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <a
                        href="{{ route('warga.kk.show', $kartuKeluarga) }}"
                        class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                    >
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Perbarui Kartu Keluarga</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

