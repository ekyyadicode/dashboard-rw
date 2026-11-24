@extends('layouts.app')

@section('title', 'Edit Wilayah RT')
@section('page-title', 'Edit Wilayah RT')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-5">
            <a href="{{ route('admin.rts.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-teal-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar Wilayah RT</span>
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-slate-900 mb-1">Edit Wilayah RT {{ $rt->nomor_rt }}</h2>
            <p class="text-xs text-slate-500 mb-6">Perbarui nomor RT atau informasi kontak ketua RT.</p>

            <form method="POST" action="{{ route('admin.rts.update', $rt) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="nomor_rt" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Nomor RT <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="nomor_rt"
                        name="nomor_rt"
                        type="text"
                        value="{{ old('nomor_rt', $rt->nomor_rt) }}"
                        required
                        placeholder="Contoh: 001"
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                    @error('nomor_rt')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nama_ketua_rt" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Nama Ketua RT <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input
                        id="nama_ketua_rt"
                        name="nama_ketua_rt"
                        type="text"
                        value="{{ old('nama_ketua_rt', $rt->nama_ketua_rt) }}"
                        placeholder="Nama lengkap ketua RT"
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                    @error('nama_ketua_rt')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="no_hp_ketua" class="block text-sm font-medium text-slate-700 mb-1.5">
                        No. HP Ketua RT <span class="text-slate-400 font-normal">(opsional)</span>
                    </label>
                    <input
                        id="no_hp_ketua"
                        name="no_hp_ketua"
                        type="text"
                        value="{{ old('no_hp_ketua', $rt->no_hp_ketua) }}"
                        placeholder="Contoh: 081234567890"
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                    @error('no_hp_ketua')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <a
                        href="{{ route('admin.rts.index') }}"
                        class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                    >
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Perbarui Wilayah RT</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

