@extends('layouts.app')

@section('title', 'Edit Jenis Iuran')
@section('page-title', 'Edit Jenis Iuran')

@section('content')
    @include('iuran._tabs')

    <div class="max-w-2xl">
        <div class="mb-5">
            <a href="{{ route('iuran.jenis.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-teal-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Pengaturan Jenis Iuran</span>
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h2 class="text-base font-semibold text-slate-900 mb-1">Edit Jenis Iuran: {{ $jenisIuran->nama_iuran }}</h2>
            <p class="text-xs text-slate-500 mb-6">Perbarui nama, nominal standar, tipe, atau status aktif jenis iuran.</p>

            <form method="POST" action="{{ route('iuran.jenis.update', $jenisIuran) }}" class="space-y-5">
                @csrf
                @method('PUT')

                @if(auth()->user()->isSuperAdmin() && $jenisIuran->pembayaran_iuran_count === 0)
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
                                <option value="{{ $rt->id }}" {{ old('rt_id', $jenisIuran->rt_id) == $rt->id ? 'selected' : '' }}>
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
                            value="RT {{ $jenisIuran->rt?->nomor_rt }}"
                            disabled
                            class="block w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-600"
                        >
                        @if($jenisIuran->pembayaran_iuran_count > 0)
                            <p class="mt-1 text-xs text-slate-400">Wilayah RT tidak dapat diubah karena sudah memiliki {{ $jenisIuran->pembayaran_iuran_count }} riwayat pembayaran.</p>
                        @endif
                    </div>
                @endif

                <div>
                    <label for="nama_iuran" class="block text-sm font-medium text-slate-700 mb-1.5">
                        Nama Iuran <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="nama_iuran"
                        name="nama_iuran"
                        type="text"
                        value="{{ old('nama_iuran', $jenisIuran->nama_iuran) }}"
                        required
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                    @error('nama_iuran')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nominal" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nominal (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="nominal"
                            name="nominal"
                            type="number"
                            min="0"
                            step="500"
                            value="{{ old('nominal', $jenisIuran->nominal) }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('nominal')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="tipe" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Tipe Iuran <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="tipe"
                            name="tipe"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="bulanan" {{ old('tipe', $jenisIuran->tipe) === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                            <option value="insidental" {{ old('tipe', $jenisIuran->tipe) === 'insidental' ? 'selected' : '' }}>Insidental</option>
                        </select>
                        @error('tipe')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-1">
                    <input type="hidden" name="is_active" value="0">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', $jenisIuran->is_active ? '1' : '0') == '1' ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-600"
                        >
                        <span class="text-sm font-medium text-slate-700">Aktif digunakan</span>
                    </label>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <a
                        href="{{ route('iuran.jenis.index') }}"
                        class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                    >
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Perbarui Jenis Iuran</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

