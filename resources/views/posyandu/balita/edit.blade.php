@extends('layouts.app')

@section('title', 'Edit Data Balita')
@section('page-title', 'Edit Data Balita Posyandu')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-5">
            <a href="{{ route('posyandu.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-teal-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar Balita</span>
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-slate-900 mb-1">Edit Balita: {{ $balita->nama_balita }}</h2>
            <p class="text-xs text-slate-500 mb-6">Perbarui profil balita, data orang tua, atau status keaktifan Posyandu.</p>

            <form method="POST" action="{{ route('posyandu.balita.update', $balita) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                                    <option value="{{ $rt->id }}" {{ old('rt_id', $balita->rt_id) == $rt->id ? 'selected' : '' }}>
                                        RT {{ $rt->nomor_rt }} {{ $rt->nama_ketua_rt ? '('.$rt->nama_ketua_rt.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('rt_id')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Wilayah RT</label>
                            <input
                                type="text"
                                value="RT {{ $balita->rt?->nomor_rt }}"
                                disabled
                                class="block w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-600"
                            >
                        </div>
                    @endif

                    <div>
                        <label for="status_aktif" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Status Balita <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status_aktif"
                            name="status_aktif"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="aktif" {{ old('status_aktif', $balita->status_aktif) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="lulus" {{ old('status_aktif', $balita->status_aktif) === 'lulus' ? 'selected' : '' }}>Lulus (&gt; 5 Tahun)</option>
                            <option value="pindah" {{ old('status_aktif', $balita->status_aktif) === 'pindah' ? 'selected' : '' }}>Pindah</option>
                        </select>
                        @error('status_aktif')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_balita" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nama Lengkap Balita <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="nama_balita"
                            name="nama_balita"
                            type="text"
                            value="{{ old('nama_balita', $balita->nama_balita) }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('nama_balita')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nik" class="block text-sm font-medium text-slate-700 mb-1.5">
                            NIK Balita <span class="text-slate-400 font-normal">(opsional, 16 digit)</span>
                        </label>
                        <input
                            id="nik"
                            name="nik"
                            type="text"
                            inputmode="numeric"
                            maxlength="16"
                            value="{{ old('nik', $balita->nik) }}"
                            placeholder="Kosongkan jika belum ada NIK"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('nik')
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
                            <option value="L" {{ old('jenis_kelamin', $balita->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                            <option value="P" {{ old('jenis_kelamin', $balita->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
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
                            value="{{ old('tanggal_lahir', $balita->tanggal_lahir?->format('Y-m-d')) }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('tanggal_lahir')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_ibu" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nama Ibu <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="nama_ibu"
                            name="nama_ibu"
                            type="text"
                            value="{{ old('nama_ibu', $balita->nama_ibu) }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('nama_ibu')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nama_ayah" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nama Ayah <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="nama_ayah"
                            name="nama_ayah"
                            type="text"
                            value="{{ old('nama_ayah', $balita->nama_ayah) }}"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('nama_ayah')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="no_hp_ortu" class="block text-sm font-medium text-slate-700 mb-1.5">
                        No. HP Orang Tua <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input
                        id="no_hp_ortu"
                        name="no_hp_ortu"
                        type="text"
                        value="{{ old('no_hp_ortu', $balita->no_hp_ortu) }}"
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                    @error('no_hp_ortu')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="alamat" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Alamat / Nomor Rumah <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <textarea
                        id="alamat"
                        name="alamat"
                        rows="2"
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >{{ old('alamat', $balita->alamat) }}</textarea>
                    @error('alamat')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <a
                        href="{{ route('posyandu.index') }}"
                        class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                    >
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Perbarui Data Balita</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

