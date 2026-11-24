@extends('layouts.app')

@section('title', 'Data Kartu Keluarga')
@section('page-title', 'Data Warga & Kartu Keluarga')

@section('content')
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
        <form method="GET" action="{{ route('warga.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-4' : 'lg:col-span-6' }}">
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
                        placeholder="Cari Nama, NIK, No. KK, atau No. Rumah..."
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
                <label for="status_domisili" class="block text-xs font-medium text-slate-600 mb-1">Domisili</label>
                <select
                    id="status_domisili"
                    name="status_domisili"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
                    <option value="">Semua Domisili</option>
                    <option value="tetap" {{ request('status_domisili') === 'tetap' ? 'selected' : '' }}>Tetap</option>
                    <option value="kontrak" {{ request('status_domisili') === 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="status_aktif" class="block text-xs font-medium text-slate-600 mb-1">Status KK</label>
                <select
                    id="status_aktif"
                    name="status_aktif"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status_aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="pindah" {{ request('status_aktif') === 'pindah' ? 'selected' : '' }}>Pindah</option>
                </select>
            </div>

            <div class="lg:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 px-3.5 py-2 text-sm font-medium text-white transition"
                >
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Filter</span>
                </button>
                <a
                    href="{{ route('warga.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3 py-2 text-sm font-medium text-slate-600 transition"
                    title="Reset Filter"
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
                        <th class="px-5 py-3.5">No. KK</th>
                        <th class="px-5 py-3.5">Kepala Keluarga</th>
                        <th class="px-5 py-3.5">Alamat &amp; No. Rumah</th>
                        <th class="px-5 py-3.5">RT</th>
                        <th class="px-5 py-3.5">Domisili</th>
                        <th class="px-5 py-3.5 text-center">Anggota</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($kartuKeluargaList as $kk)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5 font-mono text-xs font-semibold text-teal-700 whitespace-nowrap">
                                <a href="{{ route('warga.kk.show', $kk) }}" class="hover:text-teal-900 hover:underline">
                                    {{ $kk->no_kk }}
                                </a>
                            </td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('warga.kk.show', $kk) }}" class="font-semibold text-slate-900 hover:text-teal-700 hover:underline">
                                    {{ $kk->kepalaKeluarga?->nama_lengkap ?? '-' }}
                                </a>
                                @if($kk->kepalaKeluarga?->no_hp)
                                    <div class="text-xs text-slate-400">{{ $kk->kepalaKeluarga->no_hp }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="text-slate-700 max-w-xs truncate" title="{{ $kk->alamat }}">{{ $kk->alamat }}</div>
                                <div class="text-xs font-medium text-slate-500">No. Rumah: {{ $kk->nomor_rumah }}</div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                    RT {{ $kk->rt?->nomor_rt ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($kk->status_domisili === 'tetap')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200">
                                        Tetap
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        Kontrak
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                    <i data-lucide="users" class="w-3 h-3"></i>
                                    {{ $kk->warga_count }} org
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                @if($kk->status_aktif === 'aktif')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Pindah
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a
                                        href="{{ route('warga.kk.show', $kk) }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition"
                                    >
                                        <i data-lucide="users" class="w-3.5 h-3.5"></i>
                                        <span>Anggota ({{ $kk->warga_count }})</span>
                                    </a>
                                    <a
                                        href="{{ route('warga.kk.edit', $kk) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-700 transition"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        <span>Edit KK</span>
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('warga.kk.destroy', $kk) }}"
                                        onsubmit="return confirm('Yakin ingin menghapus Kartu Keluarga {{ $kk->no_kk }} beserta seluruh anggotanya?')"
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
                            <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center">
                                    <i data-lucide="folder-open" class="w-10 h-10 text-slate-300 mb-2"></i>
                                    <p class="font-medium text-slate-700">Belum ada data Kartu Keluarga</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Silakan klik tombol <strong>Tambah KK Baru</strong> atau ubah filter pencarian.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($kartuKeluargaList->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
                {{ $kartuKeluargaList->links() }}
            </div>
        @endif
    </div>
@endsection

