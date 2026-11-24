@extends('layouts.app')

@section('title', 'Bank Sampah')
@section('page-title', 'Bank Sampah')

@section('content')
    <div
        x-data="{
            showSetorModal: false,
            showTarikModal: false,
            setorWargaId: '',
            tarikWargaId: '',
            tarikNominal: '',
            saldoMap: {{ Js::from($wargaSaldoMap) }},
            formatRupiah(val) {
                const n = Number(val || 0);
                return 'Rp ' + n.toLocaleString('id-ID');
            },
            get selectedTarikSaldo() {
                if (!this.tarikWargaId) return 0;
                return Number(this.saldoMap[String(this.tarikWargaId)] || 0);
            },
            openSetor(wargaId = '') {
                this.setorWargaId = wargaId ? String(wargaId) : '';
                this.showSetorModal = true;
            },
            openTarik(wargaId = '') {
                this.tarikWargaId = wargaId ? String(wargaId) : '';
                this.tarikNominal = '';
                this.showTarikModal = true;
            }
        }"
    >
        {{-- Top Sub-tabs + Primary Action Buttons --}}
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-4">
            @include('bank-sampah._tabs')

            <div class="flex flex-wrap items-center gap-2.5">
                <button
                    type="button"
                    @click="openSetor()"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                >
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Catat Setor Sampah</span>
                </button>
                <button
                    type="button"
                    @click="openTarik()"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                >
                    <i data-lucide="minus-circle" class="w-4 h-4"></i>
                    <span>Tarik Saldo</span>
                </button>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold mb-1">Periksa kembali input transaksi:</p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- 4 Kartu Statistik Ringkasan --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500">Total Saldo Tabungan</span>
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                        <i data-lucide="piggy-bank" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-slate-900">Rp {{ number_format($totalSaldo, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">Saldo aktif seluruh warga</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500">Berat Sampah Bulan Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="scale" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-slate-900">{{ number_format($beratBulanIni, 2, ',', '.') }} kg</div>
                <p class="text-xs text-slate-400 mt-1">Periode {{ now()->translatedFormat('F Y') }}</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500">Setoran Bulan Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                        <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-emerald-700">Rp {{ number_format($setoranBulanIni, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">Total nominal masuk</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-slate-500">Penarikan Bulan Ini</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="text-xl font-bold text-amber-700">Rp {{ number_format($penarikanBulanIni, 0, ',', '.') }}</div>
                <p class="text-xs text-slate-400 mt-1">Total pencairan saldo</p>
            </div>
        </div>

        {{-- Filter & Pencarian --}}
        <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-4 sm:p-5">
            <form method="GET" action="{{ route('bank-sampah.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-4' : 'lg:col-span-6' }}">
                    <label for="q" class="block text-xs font-medium text-slate-600 mb-1">Pencarian Warga</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </span>
                        <input
                            id="q"
                            name="q"
                            type="text"
                            value="{{ request('q') }}"
                            placeholder="Cari Nama, NIK, atau No. Rumah..."
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

                <div class="lg:col-span-3 flex items-center h-[38px]">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="hanya_berisi"
                            value="1"
                            {{ request()->boolean('hanya_berisi') ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-600"
                        >
                        <span>Hanya yang punya saldo</span>
                    </label>
                </div>

                <div class="lg:col-span-3 flex items-center gap-2">
                    <button
                        type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 px-3.5 py-2 text-sm font-medium text-white transition"
                    >
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        <span>Filter</span>
                    </button>
                    <a
                        href="{{ route('bank-sampah.index') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3 py-2 text-sm font-medium text-slate-600 transition"
                    >
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabel Saldo Warga --}}
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Nama Warga &amp; NIK</th>
                            <th class="px-5 py-3.5">Alamat / No. Rumah &amp; RT</th>
                            <th class="px-5 py-3.5 text-right">Saldo Tabungan</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($wargaList as $warga)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('bank-sampah.warga.show', $warga) }}" class="font-semibold text-slate-900 hover:text-teal-700 hover:underline">
                                        {{ $warga->nama_lengkap }}
                                    </a>
                                    <div class="font-mono text-xs text-slate-400">{{ $warga->nik }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                            RT {{ $warga->rt?->nomor_rt ?? '-' }}
                                        </span>
                                        <span class="text-slate-700 font-medium">Rumah {{ $warga->kartuKeluarga?->nomor_rumah ?? '-' }}</span>
                                    </div>
                                    <div class="text-xs text-slate-400 mt-0.5 max-w-xs truncate">{{ $warga->kartuKeluarga?->alamat ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <span class="text-base font-bold {{ $warga->saldo_bank_sampah > 0 ? 'text-teal-700' : 'text-slate-400' }}">
                                        Rp {{ number_format($warga->saldo_bank_sampah, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            @click="openSetor({{ $warga->id }})"
                                            class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition"
                                        >
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                            <span>Setor</span>
                                        </button>

                                        @if($warga->saldo_bank_sampah > 0)
                                            <button
                                                type="button"
                                                @click="openTarik({{ $warga->id }})"
                                                class="inline-flex items-center gap-1 rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-200 px-2.5 py-1.5 text-xs font-semibold text-amber-700 transition"
                                            >
                                                <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                                                <span>Tarik</span>
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                disabled
                                                title="Saldo kosong"
                                                class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-100 px-2.5 py-1.5 text-xs font-medium text-slate-400 cursor-not-allowed"
                                            >
                                                <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                                                <span>Tarik</span>
                                            </button>
                                        @endif

                                        <a
                                            href="{{ route('bank-sampah.warga.show', $warga) }}"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-700 transition"
                                        >
                                            <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                                            <span>Buku Tabungan</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                                    Tidak ada data warga aktif yang ditemukan.
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

        {{-- Modal 1: Setor Sampah --}}
        <div
            x-show="showSetorModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
            aria-modal="true"
            role="dialog"
        >
            <div x-show="showSetorModal" x-transition.opacity @click="showSetorModal = false" class="fixed inset-0 bg-slate-900/50"></div>
            <div x-show="showSetorModal" x-transition class="relative z-10 w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                            <i data-lucide="recycle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Catat Setoran Sampah</h3>
                            <p class="text-xs text-slate-500">Saldo warga akan bertambah secara otomatis.</p>
                        </div>
                    </div>
                    <button type="button" @click="showSetorModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('bank-sampah.setor.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="setor_warga_id" class="block text-sm font-medium text-slate-700 mb-1">
                            Pilih Warga <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="setor_warga_id"
                            name="warga_id"
                            x-model="setorWargaId"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="">-- Pilih Warga --</option>
                            @foreach($wargaOptions as $opt)
                                <option value="{{ $opt->id }}">
                                    {{ $opt->nama_lengkap }} (RT {{ $opt->rt?->nomor_rt }} — Saldo Rp {{ number_format($opt->saldo_bank_sampah, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="setor_berat_kg" class="block text-sm font-medium text-slate-700 mb-1">
                                Berat (kg) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="setor_berat_kg"
                                name="berat_kg"
                                type="number"
                                step="0.01"
                                min="0.01"
                                required
                                placeholder="Contoh: 2.50"
                                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                            >
                        </div>

                        <div>
                            <label for="setor_nominal" class="block text-sm font-medium text-slate-700 mb-1">
                                Nilai Setoran (Rp) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="setor_nominal"
                                name="nominal"
                                type="number"
                                min="1"
                                step="100"
                                required
                                placeholder="Contoh: 15000"
                                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                            >
                        </div>
                    </div>

                    <div>
                        <label for="setor_tanggal" class="block text-sm font-medium text-slate-700 mb-1">
                            Tanggal Setor <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="setor_tanggal"
                            name="tanggal_transaksi"
                            type="date"
                            max="{{ date('Y-m-d') }}"
                            value="{{ date('Y-m-d') }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label for="setor_catatan" class="block text-sm font-medium text-slate-700 mb-1">
                            Catatan <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="setor_catatan"
                            name="catatan"
                            type="text"
                            placeholder="Contoh: Kardus & botol plastik"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button type="button" @click="showSetorModal = false" class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 transition">
                            Batal
                        </button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Setoran</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal 2: Tarik Saldo --}}
        <div
            x-show="showTarikModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
            aria-modal="true"
            role="dialog"
        >
            <div x-show="showTarikModal" x-transition.opacity @click="showTarikModal = false" class="fixed inset-0 bg-slate-900/50"></div>
            <div x-show="showTarikModal" x-transition class="relative z-10 w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                            <i data-lucide="banknote" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Tarik Saldo Tabungan</h3>
                            <p class="text-xs text-slate-500">Penarikan tidak boleh melebihi saldo tersedia.</p>
                        </div>
                    </div>
                    <button type="button" @click="showTarikModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('bank-sampah.tarik.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label for="tarik_warga_id" class="block text-sm font-medium text-slate-700 mb-1">
                            Pilih Warga <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="tarik_warga_id"
                            name="warga_id"
                            x-model="tarikWargaId"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="">-- Pilih Warga --</option>
                            @foreach($wargaOptions as $opt)
                                <option value="{{ $opt->id }}">
                                    {{ $opt->nama_lengkap }} (RT {{ $opt->rt?->nomor_rt }} — Saldo Rp {{ number_format($opt->saldo_bank_sampah, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Info Saldo Tersedia Dinamis --}}
                    <div x-show="tarikWargaId" class="rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 flex items-center justify-between">
                        <span class="text-xs font-medium text-amber-800">Saldo Tersedia:</span>
                        <span class="text-sm font-bold text-amber-900" x-text="formatRupiah(selectedTarikSaldo)"></span>
                    </div>

                    <div>
                        <label for="tarik_nominal" class="block text-sm font-medium text-slate-700 mb-1">
                            Nominal Penarikan (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="tarik_nominal"
                            name="nominal"
                            type="number"
                            min="1"
                            :max="selectedTarikSaldo > 0 ? selectedTarikSaldo : null"
                            x-model="tarikNominal"
                            required
                            placeholder="Masukkan nominal penarikan"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        <p
                            x-show="tarikWargaId && Number(tarikNominal) > selectedTarikSaldo"
                            class="mt-1 text-xs text-red-600"
                        >
                            Nominal melebihi saldo tersedia (<span x-text="formatRupiah(selectedTarikSaldo)"></span>).
                        </p>
                    </div>

                    <div>
                        <label for="tarik_tanggal" class="block text-sm font-medium text-slate-700 mb-1">
                            Tanggal Penarikan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="tarik_tanggal"
                            name="tanggal_transaksi"
                            type="date"
                            max="{{ date('Y-m-d') }}"
                            value="{{ date('Y-m-d') }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label for="tarik_catatan" class="block text-sm font-medium text-slate-700 mb-1">
                            Catatan <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="tarik_catatan"
                            name="catatan"
                            type="text"
                            placeholder="Contoh: Pencairan tunai"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                    </div>

                    <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                        <button type="button" @click="showTarikModal = false" class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 transition">
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="tarikWargaId && (selectedTarikSaldo <= 0 || Number(tarikNominal) > selectedTarikSaldo)"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:bg-slate-300 disabled:cursor-not-allowed px-4 py-2 text-sm font-semibold text-white shadow-sm transition"
                        >
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Simpan Penarikan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

