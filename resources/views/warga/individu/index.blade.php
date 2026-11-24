@extends('layouts.app')

@section('title', 'Semua Data Warga')
@section('page-title', 'Data Warga & Kartu Keluarga')

@section('content')
    @php
        $statusKeluargaLabels = [
            'kepala_keluarga' => 'Kepala Keluarga',
            'istri' => 'Istri',
            'anak' => 'Anak',
            'famili_lain' => 'Famili Lain',
        ];
    @endphp

    {{-- Sub-tab Navigation --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
        <div class="inline-flex rounded-xl bg-slate-100 p-1 self-start">
            <a
                href="{{ route('warga.index') }}"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('warga.index') || request()->routeIs('warga.kk.*') ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
            >
                <i data-lucide="home" class="w-4 h-4"></i>
                <span>Data Kartu Keluarga (KK)</span>
            </a>
            <a
                href="{{ route('warga.individu.index') }}"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('warga.individu.*') ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
            >
                <i data-lucide="users" class="w-4 h-4"></i>
                <span>Semua Data Warga (Individu)</span>
            </a>
        </div>

        <a
            href="{{ route('warga.kk.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition"
        >
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah KK Baru</span>
        </a>
    </div>

    {{-- Filter & Search Bar --}}
    <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-4 sm:p-5">
        <form method="GET" action="{{ route('warga.individu.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                <label for="q" class="block text-xs font-medium text-slate-600 mb-1">Pencarian</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input
                        id="q"
                        name="q"
                        type="text"
                        value="{{ request('q') }}"
                        placeholder="Cari NIK, Nama, atau No. HP..."
                        class="block w-full rounded-xl border border-slate-300 pl-9 pr-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                </div>
            </div>

            @if(auth()->user()->isSuperAdmin())
                <div class="lg:col-span-2">
                    <label for="rt_id" class="block text-xs font-medium text-slate-600 mb-1">Wilayah RT</label>
                    <select
                        id="rt_id"
                        name="rt_id"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                        <option value="">Semua RT</option>
                        @foreach($rts as $rt)
                            <option value="{{ $rt->id }}" {{ request('rt_id') == $rt->id ? 'selected' : '' }}>
                                RT {{ $rt->nomor_rt }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="lg:col-span-2">
                <label for="jenis_kelamin" class="block text-xs font-medium text-slate-600 mb-1">Jenis Kelamin</label>
                <select
                    id="jenis_kelamin"
                    name="jenis_kelamin"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
                    <option value="">Semua JK</option>
                    <option value="L" {{ request('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                    <option value="P" {{ request('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="status_warga" class="block text-xs font-medium text-slate-600 mb-1">Status Warga</label>
                <select
                    id="status_warga"
                    name="status_warga"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status_warga') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="pindah" {{ request('status_warga') === 'pindah' ? 'selected' : '' }}>Pindah</option>
                    <option value="meninggal" {{ request('status_warga') === 'meninggal' ? 'selected' : '' }}>Meninggal</option>
                </select>
            </div>

            <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-1' : 'lg:col-span-2' }}">
                <label for="kategori" class="block text-xs font-medium text-slate-600 mb-1">Kategori Usia</label>
                <select
                    id="kategori"
                    name="kategori"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
                    <option value="">Semua</option>
                    <option value="balita" {{ request('kategori') === 'balita' ? 'selected' : '' }}>Balita (&lt;5 th)</option>
                    <option value="lansia" {{ request('kategori') === 'lansia' ? 'selected' : '' }}>Lansia (&ge;60 th)</option>
                </select>
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 px-3 py-2 text-sm font-medium text-white transition"
                >
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Filter</span>
                </button>
                <a
                    href="{{ route('warga.individu.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3 py-2 text-sm font-medium text-slate-600 transition"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Table List --}}
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">NIK &amp; Nama</th>
                        <th class="px-5 py-3.5 text-center">JK</th>
                        <th class="px-5 py-3.5">Umur / Tgl Lahir</th>
                        <th class="px-5 py-3.5">Status Keluarga</th>
                        <th class="px-5 py-3.5">No. KK &amp; Rumah</th>
                        <th class="px-5 py-3.5">RT</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($wargaList as $warga)
                        @php
                            $usiaBulan = $warga->usia_bulan;
                            $usiaText = $usiaBulan !== null
                                ? ($usiaBulan < 60 ? $usiaBulan.' bln' : floor($usiaBulan / 12).' thn')
                                : '-';
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-900">{{ $warga->nama_lengkap }}</div>
                                <div class="font-mono text-xs text-slate-400">{{ $warga->nik }}</div>
                                @if($warga->no_hp)
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $warga->no_hp }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-semibold {{ $warga->jenis_kelamin === 'L' ? 'bg-sky-50 text-sky-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $warga->jenis_kelamin }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $usiaText }}</div>
                                <div class="text-xs text-slate-400">{{ $warga->tanggal_lahir?->format('d/m/Y') ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-slate-700">
                                {{ $statusKeluargaLabels[$warga->status_keluarga] ?? ucfirst(str_replace('_', ' ', $warga->status_keluarga)) }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($warga->kartuKeluarga)
                                    <a href="{{ route('warga.kk.show', $warga->kartu_keluarga_id) }}" class="font-mono text-xs font-semibold text-teal-700 hover:underline">
                                        {{ $warga->kartuKeluarga->no_kk }}
                                    </a>
                                    <div class="text-xs text-slate-400">Rumah: {{ $warga->kartuKeluarga->nomor_rumah }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                    RT {{ $warga->rt?->nomor_rt ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                @if($warga->status_warga === 'aktif')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                @elseif($warga->status_warga === 'pindah')
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
                                    @if($warga->kartuKeluarga)
                                        <a
                                            href="{{ route('warga.kk.show', $warga->kartuKeluarga) }}"
                                            class="inline-flex items-center gap-1 rounded-lg bg-teal-50 hover:bg-teal-100 border border-teal-200 px-2.5 py-1.5 text-xs font-medium text-teal-700 transition"
                                        >
                                            <i data-lucide="home" class="w-3.5 h-3.5"></i>
                                            <span>KK</span>
                                        </a>
                                    @endif
                                    <a
                                        href="{{ route('warga.anggota.edit', $warga) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-700 transition"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        <span>Edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i data-lucide="users" class="w-10 h-10 text-slate-300 mb-2"></i>
                                    <p class="font-medium text-slate-700">Tidak ada data warga ditemukan</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Tambahkan data KK baru atau sesuaikan filter pencarian Anda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($wargaList->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
                {{ $wargaList->links() }}
            </div>
        @endif
    </div>
@endsection

