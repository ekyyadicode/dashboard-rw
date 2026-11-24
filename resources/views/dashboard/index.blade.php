@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    {{-- Greeting Header --}}
    <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg sm:text-xl font-bold text-slate-900">
                Selamat datang, {{ auth()->user()->name }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                @if(auth()->user()->isSuperAdmin())
                    Ringkasan data kependudukan, iuran, bank sampah, dan posyandu seluruh wilayah {{ \App\Models\Setting::getValue('nama_rw', 'RW 02') }}.
                @else
                    Ringkasan data wilayah <span class="font-medium text-teal-700">RT {{ auth()->user()->rt?->nomor_rt }}</span> — {{ \App\Models\Setting::getValue('nama_rw', 'RW 02') }}.
                @endif
            </p>
        </div>
        <div class="inline-flex items-center gap-2 text-xs font-medium text-slate-500 bg-slate-50 border border-slate-200 px-3.5 py-2 rounded-xl self-start sm:self-auto">
            <i data-lucide="calendar" class="w-4 h-4 text-teal-600"></i>
            <span>{{ now()->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    {{-- Summary Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 mb-8">
        {{-- Total KK Aktif --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-500">KK Aktif</span>
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <i data-lucide="home" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900">{{ number_format($totalKkAktif, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">Kartu Keluarga aktif</p>
            </div>
        </div>

        {{-- Total Warga Aktif --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-500">Warga Aktif</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900">{{ number_format($totalWargaAktif, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">Jiwa tercatat aktif</p>
            </div>
        </div>

        {{-- Pemasukan Iuran Bulan Ini --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-500">Iuran Bulan Ini</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-bold text-slate-900">Rp {{ number_format($pemasukanIuranBulanIni, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">Periode {{ now()->translatedFormat('F Y') }}</p>
            </div>
        </div>

        {{-- Total Saldo Bank Sampah --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-500">Saldo Bank Sampah</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="recycle" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <div class="text-xl font-bold text-slate-900">Rp {{ number_format($totalSaldoBankSampah, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">Total saldo warga</p>
            </div>
        </div>

        {{-- Total Balita Terdaftar --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-5 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-500">Balita Terdaftar</span>
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="baby" class="w-4 h-4"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-bold text-slate-900">{{ number_format($totalBalita, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">Usia &le; 5 tahun aktif</p>
            </div>
        </div>
    </div>

    {{-- Super Admin: Tabel Ringkasan Per RT --}}
    @if(auth()->user()->isSuperAdmin())
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Ringkasan Per Wilayah RT</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi Kartu Keluarga, Warga, dan Pengurus aktif pada setiap RT.</p>
                </div>
                <a
                    href="{{ route('admin.rts.index') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-teal-700 hover:text-teal-800"
                >
                    <span>Kelola Wilayah RT</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Nomor RT</th>
                            <th class="px-5 py-3.5">Nama Ketua RT</th>
                            <th class="px-5 py-3.5 text-center">KK Aktif</th>
                            <th class="px-5 py-3.5 text-center">Warga Aktif</th>
                            <th class="px-5 py-3.5 text-center">Pengurus Aktif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($rtSummaries as $rt)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3.5 font-semibold text-slate-900">
                                    RT {{ $rt->nomor_rt }}
                                </td>
                                <td class="px-5 py-3.5 text-slate-700">
                                    {{ $rt->nama_ketua_rt ?: '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-center font-medium text-slate-800">
                                    {{ number_format($rt->kk_aktif_count, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-center font-medium text-slate-800">
                                    {{ number_format($rt->warga_aktif_count, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200">
                                        {{ number_format($rt->pengurus_aktif_count, 0, ',', '.') }} akun
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                                    Belum ada data wilayah RT.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection

