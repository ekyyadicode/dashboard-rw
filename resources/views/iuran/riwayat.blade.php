@extends('layouts.app')

@section('title', 'Riwayat Transaksi Iuran')
@section('page-title', 'Iuran Warga')

@section('content')
    @include('iuran._tabs')

    {{-- Filter Bar --}}
    <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-4 sm:p-5">
        <form method="GET" action="{{ route('iuran.riwayat') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                <label for="q" class="block text-xs font-medium text-slate-600 mb-1">Pencarian</label>
                <input
                    id="q"
                    name="q"
                    type="text"
                    value="{{ request('q') }}"
                    placeholder="Nama KK, No. KK, Rumah, Catatan"
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

            <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-2' : 'lg:col-span-3' }}">
                <label for="jenis_iuran_id" class="block text-xs font-medium text-slate-600 mb-1">Jenis Iuran</label>
                <select
                    id="jenis_iuran_id"
                    name="jenis_iuran_id"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
                    <option value="">Semua Jenis</option>
                    @foreach($jenisIuranList as $jenis)
                        <option value="{{ $jenis->id }}" {{ request('jenis_iuran_id') == $jenis->id ? 'selected' : '' }}>
                            {{ $jenis->nama_iuran }} @if(auth()->user()->isSuperAdmin()) (RT {{ $jenis->rt?->nomor_rt }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="bulan" class="block text-xs font-medium text-slate-600 mb-1">Periode Bulan</label>
                <select
                    id="bulan"
                    name="bulan"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
                    <option value="">Semua Bulan</option>
                    @foreach($bulanList as $num => $namaBulan)
                        <option value="{{ $num }}" {{ (int) request('bulan') === $num ? 'selected' : '' }}>
                            {{ $namaBulan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-1">
                <label for="tahun" class="block text-xs font-medium text-slate-600 mb-1">Tahun</label>
                <select
                    id="tahun"
                    name="tahun"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
                    <option value="">Semua</option>
                    @foreach($tahunList as $thn)
                        <option value="{{ $thn }}" {{ (int) request('tahun') === (int) $thn ? 'selected' : '' }}>
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
                    href="{{ route('iuran.riwayat') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3 py-2 text-sm font-medium text-slate-600 transition"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Table Riwayat --}}
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Tgl Bayar</th>
                        <th class="px-5 py-3.5">Kepala Keluarga &amp; Rumah</th>
                        <th class="px-5 py-3.5">Jenis Iuran &amp; Periode</th>
                        <th class="px-5 py-3.5">Metode</th>
                        <th class="px-5 py-3.5 text-right">Nominal</th>
                        <th class="px-5 py-3.5">Dicatat Oleh</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($riwayatList as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <div class="font-medium text-slate-900">{{ $item->tanggal_bayar?->format('d/m/Y') ?? '-' }}</div>
                                <div class="text-xs text-slate-400">RT {{ $item->rt?->nomor_rt ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-900">
                                    {{ $item->kartuKeluarga?->kepalaKeluarga?->nama_lengkap ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-400">
                                    Rumah {{ $item->kartuKeluarga?->nomor_rumah ?? '-' }} &middot;
                                    <span class="font-mono">{{ $item->kartuKeluarga?->no_kk ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-medium text-slate-800">{{ $item->jenisIuran?->nama_iuran ?? '-' }}</div>
                                <div class="text-xs text-slate-500">
                                    @if($item->periode_bulan)
                                        {{ $bulanList[$item->periode_bulan] ?? $item->periode_bulan }} {{ $item->periode_tahun }}
                                    @else
                                        Insidental {{ $item->periode_tahun }}
                                    @endif
                                    @if($item->catatan)
                                        &middot; <span class="italic text-slate-400">{{ $item->catatan }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($item->metode_bayar === 'transfer')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-50 text-sky-700 border border-sky-200">
                                        Transfer
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Tunai
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-slate-900 whitespace-nowrap">
                                Rp {{ number_format($item->nominal_dibayar, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-600 whitespace-nowrap">
                                {{ $item->creator?->name ?? 'Sistem' }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <form
                                    method="POST"
                                    action="{{ route('iuran.pembayaran.destroy', $item) }}"
                                    onsubmit="return confirm('Yakin ingin membatalkan transaksi pembayaran ini?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 text-xs font-medium text-red-700 transition"
                                    >
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Batalkan</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                Belum ada riwayat transaksi pembayaran iuran.
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

