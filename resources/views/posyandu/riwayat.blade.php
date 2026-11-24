@extends('layouts.app')

@section('title', 'Riwayat Pemeriksaan Posyandu')
@section('page-title', 'Posyandu Balita')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
        @include('posyandu._tabs')
    </div>

    {{-- Filter Bar --}}
    <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-4 sm:p-5">
        <form method="GET" action="{{ route('posyandu.riwayat') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-4' : 'lg:col-span-6' }}">
                <label for="q" class="block text-xs font-medium text-slate-600 mb-1">Pencarian</label>
                <input
                    id="q"
                    name="q"
                    type="text"
                    value="{{ request('q') }}"
                    placeholder="Nama Balita, Nama Ibu, NIK, Imunisasi, atau Catatan..."
                    class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
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
                <label for="bulan" class="block text-xs font-medium text-slate-600 mb-1">Bulan</label>
                <select
                    id="bulan"
                    name="bulan"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
                    <option value="all" {{ $selectedBulan === 'all' ? 'selected' : '' }}>Semua Bulan</option>
                    @foreach($bulanList as $num => $namaBulan)
                        <option value="{{ $num }}" {{ (string) $num === $selectedBulan ? 'selected' : '' }}>
                            {{ $namaBulan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="tahun" class="block text-xs font-medium text-slate-600 mb-1">Tahun</label>
                <select
                    id="tahun"
                    name="tahun"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
                    @foreach($tahunList as $thn)
                        <option value="{{ $thn }}" {{ $selectedTahun === (int) $thn ? 'selected' : '' }}>
                            {{ $thn }}
                        </option>
                    @endforeach
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
                    href="{{ route('posyandu.riwayat') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3 py-2 text-sm font-medium text-slate-600 transition"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Table Riwayat Pemeriksaan --}}
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Tgl Periksa</th>
                        <th class="px-5 py-3.5">Nama Balita &amp; Ibu</th>
                        <th class="px-5 py-3.5">Usia Saat Periksa</th>
                        <th class="px-5 py-3.5 text-right">Berat (kg)</th>
                        <th class="px-5 py-3.5 text-right">Tinggi (cm)</th>
                        <th class="px-5 py-3.5">Imunisasi &amp; Catatan</th>
                        <th class="px-5 py-3.5">Petugas</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($riwayatList as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5 whitespace-nowrap font-medium text-slate-800">
                                {{ $item->tanggal_periksa?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($item->balita)
                                    <a href="{{ route('posyandu.balita.show', $item->balita) }}" class="font-semibold text-slate-900 hover:text-teal-700 hover:underline">
                                        {{ $item->balita->nama_balita }}
                                    </a>
                                    <div class="text-xs text-slate-400">
                                        Ibu: {{ $item->balita->nama_ibu }} &middot; RT {{ $item->rt?->nomor_rt ?? '-' }}
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ $item->usia_format }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap font-semibold text-slate-900">
                                {{ number_format((float) $item->berat_badan_kg, 2, ',', '.') }} kg
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap font-semibold text-slate-900">
                                {{ number_format((float) $item->tinggi_badan_cm, 2, ',', '.') }} cm
                            </td>
                            <td class="px-5 py-3.5">
                                @if($item->imunisasi)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200">
                                        {{ $item->imunisasi }}
                                    </span>
                                @endif
                                @if($item->catatan)
                                    <div class="text-xs text-slate-500 {{ $item->imunisasi ? 'mt-1' : '' }}">{{ $item->catatan }}</div>
                                @endif
                                @if(! $item->imunisasi && ! $item->catatan)
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                {{ $item->creator?->name ?? 'Sistem' }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <form
                                    method="POST"
                                    action="{{ route('posyandu.periksa.destroy', $item) }}"
                                    onsubmit="return confirm('Yakin ingin menghapus catatan pemeriksaan ini?')"
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                                Belum ada catatan pemeriksaan Posyandu untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($riwayatList->hasPages())
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
                {{ $riwayatList->links() }}
            </div>
        @endif
    </div>
@endsection
