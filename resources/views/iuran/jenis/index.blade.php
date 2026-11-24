@extends('layouts.app')

@section('title', 'Pengaturan Jenis Iuran')
@section('page-title', 'Iuran Warga')

@section('content')
    @include('iuran._tabs')

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Form Tambah Cepat Jenis Iuran --}}
        <div class="lg:col-span-4 bg-white border border-slate-200 rounded-2xl p-5">
            <h2 class="text-base font-semibold text-slate-900 mb-1">Tambah Jenis Iuran</h2>
            <p class="text-xs text-slate-500 mb-5">Atur nama iuran, nominal standar, dan sifat tagihan per RT.</p>

            <form method="POST" action="{{ route('iuran.jenis.store') }}" class="space-y-4">
                @csrf

                @if(auth()->user()->isSuperAdmin())
                    <div>
                        <label for="rt_id" class="block text-sm font-medium text-slate-700 mb-1">
                            Wilayah RT <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="rt_id"
                            name="rt_id"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="">-- Pilih RT --</option>
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}" {{ old('rt_id', request('rt_id')) == $rt->id ? 'selected' : '' }}>
                                    RT {{ $rt->nomor_rt }} {{ $rt->nama_ketua_rt ? '('.$rt->nama_ketua_rt.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('rt_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Wilayah RT</label>
                        <input
                            type="text"
                            value="RT {{ auth()->user()->rt?->nomor_rt }}"
                            disabled
                            class="block w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2 text-sm text-slate-600"
                        >
                    </div>
                @endif

                <div>
                    <label for="nama_iuran" class="block text-sm font-medium text-slate-700 mb-1">
                        Nama Iuran <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="nama_iuran"
                        name="nama_iuran"
                        type="text"
                        value="{{ old('nama_iuran') }}"
                        required
                        placeholder="Contoh: Iuran Sampah & Keamanan"
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                    @error('nama_iuran')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nominal" class="block text-sm font-medium text-slate-700 mb-1">
                        Nominal (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="nominal"
                        name="nominal"
                        type="number"
                        min="0"
                        step="500"
                        value="{{ old('nominal') }}"
                        required
                        placeholder="Contoh: 25000"
                        class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                    @error('nominal')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tipe" class="block text-sm font-medium text-slate-700 mb-1">
                        Tipe Iuran <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="tipe"
                        name="tipe"
                        required
                        class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                        <option value="bulanan" {{ old('tipe', 'bulanan') === 'bulanan' ? 'selected' : '' }}>Bulanan (Rutin tiap bulan)</option>
                        <option value="insidental" {{ old('tipe') === 'insidental' ? 'selected' : '' }}>Insidental (Sewaktu-waktu / Kegiatan)</option>
                    </select>
                    @error('tipe')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-1">
                    <input type="hidden" name="is_active" value="0">
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-600"
                        >
                        <span class="text-sm text-slate-700">Aktif digunakan</span>
                    </label>
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                >
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Simpan Jenis Iuran</span>
                </button>
            </form>
        </div>

        {{-- Daftar Master Jenis Iuran --}}
        <div class="lg:col-span-8 bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Daftar Master Jenis Iuran</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar jenis iuran yang terdaftar pada wilayah RT.</p>
                </div>

                @if(auth()->user()->isSuperAdmin())
                    <form method="GET" action="{{ route('iuran.jenis.index') }}" class="flex items-center gap-2">
                        <select
                            name="rt_id"
                            onchange="this.form.submit()"
                            class="rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-700 focus:border-teal-600 focus:outline-none"
                        >
                            <option value="">Semua RT</option>
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}" {{ request('rt_id') == $rt->id ? 'selected' : '' }}>
                                    RT {{ $rt->nomor_rt }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">RT</th>
                            <th class="px-5 py-3.5">Nama Iuran</th>
                            <th class="px-5 py-3.5 text-right">Nominal</th>
                            <th class="px-5 py-3.5 text-center">Tipe</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($jenisIuranList as $jenis)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                        RT {{ $jenis->rt?->nomor_rt ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-slate-900">{{ $jenis->nama_iuran }}</div>
                                    <div class="text-xs text-slate-400">{{ $jenis->pembayaran_iuran_count }} transaksi tercatat</div>
                                </td>
                                <td class="px-5 py-3.5 text-right font-semibold text-slate-800 whitespace-nowrap">
                                    Rp {{ number_format($jenis->nominal, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @if($jenis->tipe === 'bulanan')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200">
                                            Bulanan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                            Insidental
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @if($jenis->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a
                                            href="{{ route('iuran.jenis.edit', $jenis) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-700 transition"
                                        >
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('iuran.jenis.destroy', $jenis) }}"
                                            onsubmit="return confirm('Yakin ingin menghapus jenis iuran {{ addslashes($jenis->nama_iuran) }}?')"
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
                                <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                    Belum ada jenis iuran terdaftar. Silakan tambahkan menggunakan form di sebelah kiri.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

