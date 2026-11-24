@extends('layouts.app')

@section('title', 'Riwayat Bank Sampah')
@section('page-title', 'Bank Sampah')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
        @include('bank-sampah._tabs')
    </div>

    {{-- Filter Bar --}}
    <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-4 sm:p-5">
        <form method="GET" action="{{ route('bank-sampah.riwayat') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                <label for="q" class="block text-xs font-medium text-slate-600 mb-1">Pencarian Warga</label>
                <input
                    id="q"
                    name="q"
                    type="text"
                    value="{{ request('q') }}"
                    placeholder="Nama, NIK, atau Catatan..."
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
                <label for="jenis_transaksi" class="block text-xs font-medium text-slate-600 mb-1">Jenis Transaksi</label>
                <select
                    id="jenis_transaksi"
                    name="jenis_transaksi"
                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                >
                    <option value="">Semua Jenis</option>
                    <option value="setor" {{ request('jenis_transaksi') === 'setor' ? 'selected' : '' }}>Setor</option>
                    <option value="tarik" {{ request('jenis_transaksi') === 'tarik' ? 'selected' : '' }}>Tarik</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label for="bulan" class="block text-xs font-medium text-slate-600 mb-1">Bulan</label>
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

            <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-2' : 'lg:col-span-3' }} flex items-center gap-2">
                <button
                    type="submit"
                    class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 px-3 py-2 text-sm font-medium text-white transition"
                >
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Filter</span>
                </button>
                <a
                    href="{{ route('bank-sampah.riwayat') }}"
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
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Nama Warga &amp; RT</th>
                        <th class="px-5 py-3.5 text-center">Jenis</th>
                        <th class="px-5 py-3.5 text-right">Berat</th>
                        <th class="px-5 py-3.5 text-right">Nominal</th>
                        <th class="px-5 py-3.5 text-right">Saldo Akhir</th>
                        <th class="px-5 py-3.5">Petugas</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($riwayatList as $trx)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5 whitespace-nowrap font-medium text-slate-800">
                                {{ $trx->tanggal_transaksi?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($trx->warga)
                                    <a href="{{ route('bank-sampah.warga.show', $trx->warga) }}" class="font-semibold text-slate-900 hover:text-teal-700 hover:underline">
                                        {{ $trx->warga->nama_lengkap }}
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                                <div class="text-xs text-slate-400">
                                    RT {{ $trx->rt?->nomor_rt ?? '-' }}
                                    @if($trx->catatan)
                                        &middot; <span class="italic">{{ $trx->catatan }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                @if($trx->jenis_transaksi === 'setor')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3"></i>
                                        Setor
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                        Tarik
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap text-slate-700">
                                {{ $trx->berat_kg !== null ? number_format((float) $trx->berat_kg, 2, ',', '.').' kg' : '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap font-semibold {{ $trx->jenis_transaksi === 'setor' ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $trx->jenis_transaksi === 'setor' ? '+ ' : '- ' }}Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap font-semibold text-slate-900">
                                Rp {{ number_format($trx->saldo_akhir, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                {{ $trx->creator?->name ?? 'Sistem' }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <form
                                    method="POST"
                                    action="{{ route('bank-sampah.transaksi.destroy', $trx) }}"
                                    onsubmit="return confirm('Yakin ingin membatalkan transaksi ini? Saldo warga akan otomatis disesuaikan kembali.')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 text-xs font-medium text-red-700 transition"
                                    >
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        <span>Batalkan</span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                                Belum ada riwayat transaksi Bank Sampah.
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

