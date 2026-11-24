@extends('layouts.app')

@section('title', 'Posyandu Balita')
@section('page-title', 'Posyandu Balita')

@section('content')
    <div
        x-data="{
            showPeriksaModal: {{ $errors->any() ? 'true' : 'false' }},
            balitaId: '{{ old('balita_id', '') }}',
            imunisasiText: {{ Js::from(old('imunisasi', '')) }},
            openModal(id = '') {
                this.balitaId = id ? String(id) : '';
                this.imunisasiText = '';
                this.showPeriksaModal = true;
            },
            toggleChip(chip) {
                let parts = this.imunisasiText
                    .split(',')
                    .map(s => s.trim())
                    .filter(Boolean);
                const idx = parts.findIndex(p => p.toLowerCase() === chip.toLowerCase());
                if (idx >= 0) {
                    parts.splice(idx, 1);
                } else {
                    parts.push(chip);
                }
                this.imunisasiText = parts.join(', ');
            },
            hasChip(chip) {
                return this.imunisasiText
                    .split(',')
                    .map(s => s.trim().toLowerCase())
                    .includes(chip.toLowerCase());
            }
        }"
    >
        {{-- Sub-tab & Tombol Aksi Utama --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
            @include('posyandu._tabs')

            <div class="flex flex-wrap items-center gap-2.5">
                <a
                    href="{{ route('posyandu.balita.create') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-teal-600 bg-white hover:bg-teal-50 px-4 py-2.5 text-sm font-semibold text-teal-700 transition"
                >
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Tambah Data Balita</span>
                </a>
                <button
                    type="button"
                    @click="openModal()"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                >
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Catat Pemeriksaan Baru</span>
                </button>
            </div>
        </div>

        {{-- 4 Kartu Statistik Ringkasan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500">Total Balita Aktif</span>
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                        <i data-lucide="baby" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-slate-900">{{ number_format($totalBalita, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">Balita terdaftar aktif Posyandu</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500">Sudah Ditimbang Bulan Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-emerald-700">{{ number_format($sudahDitimbangBulanIni, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">
                    {{ $totalBalita > 0 ? round(($sudahDitimbangBulanIni / $totalBalita) * 100) : 0 }}% cakupan {{ now()->translatedFormat('F Y') }}
                </p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500">Belum Ditimbang Bulan Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i data-lucide="clock" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-amber-600">{{ number_format($belumDitimbangBulanIni, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">Perlu penimbangan bulan ini</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500">Total Kunjungan Bulan Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                        <i data-lucide="activity" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-2xl font-bold text-slate-900">{{ number_format($totalKunjunganBulanIni, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">Catatan pemeriksaan masuk</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-4 sm:p-5">
            <form method="GET" action="{{ route('posyandu.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-4' : 'lg:col-span-6' }}">
                    <label for="q" class="block text-xs font-medium text-slate-600 mb-1">Pencarian Balita</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input
                            id="q"
                            name="q"
                            type="text"
                            value="{{ request('q') }}"
                            placeholder="Cari Nama Balita, NIK, Ibu/Ayah, atau Alamat..."
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
                    <label for="status_aktif" class="block text-xs font-medium text-slate-600 mb-1">Status Balita</label>
                    <select
                        id="status_aktif"
                        name="status_aktif"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                        <option value="aktif" {{ $selectedStatusAktif === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="lulus" {{ $selectedStatusAktif === 'lulus' ? 'selected' : '' }}>Lulus (&gt;5 thn)</option>
                        <option value="pindah" {{ $selectedStatusAktif === 'pindah' ? 'selected' : '' }}>Pindah</option>
                        <option value="all" {{ $selectedStatusAktif === 'all' ? 'selected' : '' }}>Semua Status</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="status_bulan_ini" class="block text-xs font-medium text-slate-600 mb-1">Timbang Bulan Ini</label>
                    <select
                        id="status_bulan_ini"
                        name="status_bulan_ini"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                        <option value="">Semua</option>
                        <option value="sudah" {{ request('status_bulan_ini') === 'sudah' ? 'selected' : '' }}>Sudah Ditimbang</option>
                        <option value="belum" {{ request('status_bulan_ini') === 'belum' ? 'selected' : '' }}>Belum Ditimbang</option>
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
                        href="{{ route('posyandu.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3 py-2 text-sm font-medium text-slate-600 transition"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabel Daftar Balita --}}
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Balita &amp; NIK</th>
                            <th class="px-5 py-3.5">JK &amp; Umur Akurat</th>
                            <th class="px-5 py-3.5">Ibu / Ayah &amp; Alamat</th>
                            <th class="px-5 py-3.5">Pemeriksaan Terakhir</th>
                            <th class="px-5 py-3.5 text-center">Status Bulan Ini</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($balitaList as $balita)
                            @php
                                $lastCheck = $balita->pemeriksaanTerakhir;
                                $sudahBulanIni = $checkedThisMonthIds->has($balita->id);
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('posyandu.balita.show', $balita) }}" class="font-semibold text-slate-900 hover:text-teal-700 hover:underline">
                                            {{ $balita->nama_balita }}
                                        </a>
                                        @if($balita->status_aktif !== 'aktif')
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600">
                                                {{ ucfirst($balita->status_aktif) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="font-mono text-xs text-slate-400">
                                        {{ $balita->nik ?: 'NIK belum diisi' }}
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-semibold {{ $balita->jenis_kelamin === 'L' ? 'bg-sky-50 text-sky-700' : 'bg-rose-50 text-rose-700' }}">
                                            {{ $balita->jenis_kelamin }}
                                        </span>
                                        <span class="font-semibold text-slate-800">{{ $balita->usia_format }}</span>
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5">Lahir: {{ $balita->tanggal_lahir?->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="font-medium text-slate-800">
                                        Ibu: {{ $balita->nama_ibu }}
                                        @if($balita->nama_ayah)
                                            <span class="text-slate-400 font-normal">/ Ayah: {{ $balita->nama_ayah }}</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5 max-w-xs truncate">
                                        RT {{ $balita->rt?->nomor_rt ?? '-' }}
                                        @if($balita->alamat)
                                            &middot; {{ $balita->alamat }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if($lastCheck)
                                        <div class="font-semibold text-slate-800">
                                            {{ number_format((float) $lastCheck->berat_badan_kg, 2, ',', '.') }} kg
                                            <span class="text-slate-300 mx-1">|</span>
                                            {{ number_format((float) $lastCheck->tinggi_badan_cm, 2, ',', '.') }} cm
                                        </div>
                                        <div class="text-xs text-slate-400 mt-0.5">
                                            Tgl: {{ $lastCheck->tanggal_periksa?->format('d/m/Y') }} ({{ $lastCheck->usia_bulan }} bln)
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                            Belum pernah ditimbang
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @if($sudahBulanIni)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                            Sudah Bulan Ini
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                            Belum Bulan Ini
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            @click="openModal({{ $balita->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg bg-teal-600 hover:bg-teal-700 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition"
                                        >
                                            <i data-lucide="scale" class="w-3.5 h-3.5"></i>
                                            <span>Catat Timbang</span>
                                        </button>
                                        <a
                                            href="{{ route('posyandu.balita.show', $balita) }}"
                                            class="inline-flex items-center gap-1 rounded-lg bg-sky-50 hover:bg-sky-100 border border-sky-200 px-2.5 py-1.5 text-xs font-medium text-sky-700 transition"
                                        >
                                            <i data-lucide="line-chart" class="w-3.5 h-3.5"></i>
                                            <span>Grafik</span>
                                        </a>
                                        <a
                                            href="{{ route('posyandu.balita.edit', $balita) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-700 transition"
                                        >
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </a>
                                        <form
                                            method="POST"
                                            action="{{ route('posyandu.balita.destroy', $balita) }}"
                                            onsubmit="return confirm('Yakin ingin menghapus data balita {{ addslashes($balita->nama_balita) }} beserta riwayat pemeriksaannya?')"
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
                                <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <i data-lucide="baby" class="w-10 h-10 text-slate-300 mb-2"></i>
                                        <p class="font-medium text-slate-700">Belum ada data balita yang terdaftar</p>
                                        <p class="text-xs text-slate-400 mt-0.5">Klik tombol <strong>Tambah Data Balita</strong> di atas untuk mendaftarkan balita Posyandu.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($balitaList->hasPages())
                <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $balitaList->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Input Pemeriksaan Posyandu --}}
        <div
            x-show="showPeriksaModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
            aria-modal="true"
            role="dialog"
        >
            <div x-show="showPeriksaModal" x-transition.opacity @click="showPeriksaModal = false" class="fixed inset-0 bg-slate-900/50"></div>
            <div x-show="showPeriksaModal" x-transition class="relative z-10 w-full max-w-lg bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                            <i data-lucide="stethoscope" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Catat Pemeriksaan Posyandu</h3>
                            <p class="text-xs text-slate-500">Usia bulan anak dihitung otomatis secara eksak saat tanggal periksa.</p>
                        </div>
                    </div>
                    <button type="button" @click="showPeriksaModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                @if($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs text-red-700">
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('posyandu.periksa.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="periksa_balita_id" class="block text-sm font-medium text-slate-700 mb-1">
                            Pilih Balita <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="periksa_balita_id"
                            name="balita_id"
                            x-model="balitaId"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="">-- Pilih Balita --</option>
                            @foreach($balitaOptions as $opt)
                                <option value="{{ $opt->id }}">
                                    {{ $opt->nama_balita }} ({{ $opt->usia_format }} — RT {{ $opt->rt?->nomor_rt }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="periksa_tanggal" class="block text-sm font-medium text-slate-700 mb-1">
                            Tanggal Pemeriksaan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="periksa_tanggal"
                            name="tanggal_periksa"
                            type="date"
                            max="{{ date('Y-m-d') }}"
                            value="{{ old('tanggal_periksa', date('Y-m-d')) }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="periksa_berat" class="block text-sm font-medium text-slate-700 mb-1">
                                Berat Badan (kg) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="periksa_berat"
                                name="berat_badan_kg"
                                type="number"
                                step="0.01"
                                min="1"
                                max="50"
                                value="{{ old('berat_badan_kg') }}"
                                required
                                placeholder="Contoh: 9.40"
                                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                            >
                        </div>

                        <div>
                            <label for="periksa_tinggi" class="block text-sm font-medium text-slate-700 mb-1">
                                Tinggi Badan (cm) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="periksa_tinggi"
                                name="tinggi_badan_cm"
                                type="number"
                                step="0.01"
                                min="30"
                                max="150"
                                value="{{ old('tinggi_badan_cm') }}"
                                required
                                placeholder="Contoh: 76.50"
                                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="periksa_imunisasi" class="block text-sm font-medium text-slate-700 mb-1">
                            Imunisasi / Vitamin <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="periksa_imunisasi"
                            name="imunisasi"
                            type="text"
                            x-model="imunisasiText"
                            placeholder="Klik pilihan cepat di bawah atau ketik manual"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            @foreach($imunisasiChips as $chip)
                                <button
                                    type="button"
                                    @click="toggleChip({{ Js::from($chip) }})"
                                    :class="hasChip({{ Js::from($chip) }})
                                        ? 'bg-teal-600 text-white border-teal-600'
                                        : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200'"
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border transition"
                                >
                                    + {{ $chip }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="periksa_catatan" class="block text-sm font-medium text-slate-700 mb-1">
                            Catatan Kader <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="periksa_catatan"
                            name="catatan"
                            type="text"
                            value="{{ old('catatan') }}"
                            placeholder="Contoh: Gizi baik, aktif, nafsu makan meningkat"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button type="button" @click="showPeriksaModal = false" class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 transition">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Pemeriksaan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
