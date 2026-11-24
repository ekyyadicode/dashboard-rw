@extends('layouts.app')

@section('title', 'Status Iuran per KK')
@section('page-title', 'Iuran Warga')

@section('content')
    @include('iuran._tabs')

    <div
        x-data="{
            showPayModal: false,
            kkId: '',
            kkLabel: '',
            kkNo: '',
            tarifStandar: {{ (int) ($selectedJenis?->nominal ?? 0) }},
            sudahDibayar: 0,
            sisaTagihan: {{ (int) ($selectedJenis?->nominal ?? 0) }},
            nominal: '{{ $selectedJenis?->nominal ?? 0 }}',
            metode: 'tunai',
            tanggal: '{{ date('Y-m-d') }}',
            catatan: '',
            formatRupiah(val) {
                return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
            },
            openPayModal(id, nama, noKk, defaultNominal, sudahDibayar = 0, sisaTagihan = 0) {
                this.kkId = id;
                this.kkLabel = nama;
                this.kkNo = noKk;
                this.sudahDibayar = Number(sudahDibayar || 0);
                this.sisaTagihan = Number(sisaTagihan || 0);
                this.nominal = defaultNominal;
                this.metode = 'tunai';
                this.tanggal = '{{ date('Y-m-d') }}';
                this.catatan = '';
                this.showPayModal = true;
            }
        }"
    >
        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold mb-1">Gagal menyimpan pembayaran:</p>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filter Bar --}}
        <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-4 sm:p-5">
            <form method="GET" action="{{ route('iuran.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                @if(auth()->user()->isSuperAdmin())
                    <div class="lg:col-span-2">
                        <label for="rt_id" class="block text-xs font-medium text-slate-600 mb-1">Wilayah RT</label>
                        <select
                            id="rt_id"
                            name="rt_id"
                            onchange="this.form.submit()"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}" {{ $selectedRtId == $rt->id ? 'selected' : '' }}>
                                    RT {{ $rt->nomor_rt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-3' : 'lg:col-span-4' }}">
                    <label for="jenis_iuran_id" class="block text-xs font-medium text-slate-600 mb-1">Jenis Iuran</label>
                    <select
                        id="jenis_iuran_id"
                        name="jenis_iuran_id"
                        onchange="this.form.submit()"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                        @forelse($jenisIuranList as $jenis)
                            <option value="{{ $jenis->id }}" {{ $selectedJenisId == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama_iuran }} (Rp {{ number_format($jenis->nominal, 0, ',', '.') }})
                            </option>
                        @empty
                            <option value="">-- Belum ada jenis iuran --</option>
                        @endforelse
                    </select>
                </div>

                @if(! $selectedJenis || $selectedJenis->tipe === 'bulanan')
                    <div class="lg:col-span-2">
                        <label for="bulan" class="block text-xs font-medium text-slate-600 mb-1">Bulan</label>
                        <select
                            id="bulan"
                            name="bulan"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            @foreach($bulanList as $num => $namaBulan)
                                <option value="{{ $num }}" {{ $selectedBulan === $num ? 'selected' : '' }}>
                                    {{ $namaBulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="lg:col-span-1">
                    <label for="tahun" class="block text-xs font-medium text-slate-600 mb-1">Tahun</label>
                    <select
                        id="tahun"
                        name="tahun"
                        class="block w-full rounded-xl border border-slate-300 bg-white px-2.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                        @foreach($tahunList as $thn)
                            <option value="{{ $thn }}" {{ $selectedTahun === (int) $thn ? 'selected' : '' }}>
                                {{ $thn }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-2' : 'lg:col-span-3' }}">
                    <label for="q" class="block text-xs font-medium text-slate-600 mb-1">Cari KK / Rumah</label>
                    <input
                        id="q"
                        name="q"
                        type="text"
                        value="{{ request('q') }}"
                        placeholder="Nama, No. KK, No. Rumah"
                        class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                </div>

                <div class="lg:col-span-2 flex items-center gap-2">
                    <button
                        type="submit"
                        class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 px-3.5 py-2 text-sm font-medium text-white transition"
                    >
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        <span>Tampilkan</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Banner jika belum ada jenis iuran di RT terpilih --}}
        @if($jenisIuranList->isEmpty())
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <i data-lucide="info" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-amber-900">Belum ada Jenis Iuran aktif untuk RT ini</h3>
                        <p class="text-xs text-amber-800 mt-1">
                            Silakan tambahkan minimal satu Jenis Iuran (misal: Iuran Sampah &amp; Keamanan) terlebih dahulu agar pencatatan pembayaran warga dapat dimulai.
                        </p>
                    </div>
                </div>
                <a
                    href="{{ route('iuran.jenis.index', auth()->user()->isSuperAdmin() ? ['rt_id' => $selectedRtId] : []) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition shrink-0"
                >
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Buat Jenis Iuran</span>
                </a>
            </div>
        @else
            {{-- 4 Kartu Ringkasan --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border border-slate-200 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-slate-500">Total KK Wajib Iuran</span>
                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                            <i data-lucide="home" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-900">{{ number_format($totalKk, 0, ',', '.') }}</div>
                    <p class="text-xs text-slate-400 mt-1">KK aktif (Tetap &amp; Kontrak)</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-slate-500">Sudah Lunas</span>
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-emerald-700">{{ number_format($jumlahLunas, 0, ',', '.') }}</div>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $totalKk > 0 ? round(($jumlahLunas / $totalKk) * 100) : 0 }}% dari total KK
                    </p>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-slate-500">Belum Lunas</span>
                        <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-red-600">{{ number_format($jumlahBelum, 0, ',', '.') }}</div>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ number_format($jumlahSebagian, 0, ',', '.') }} cicilan &middot; {{ number_format($jumlahBelumBayar, 0, ',', '.') }} belum bayar
                    </p>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-medium text-slate-500">Total Terkumpul</span>
                        <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                            <i data-lucide="wallet" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-xl font-bold text-slate-900">Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}</div>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $selectedJenis->tipe === 'bulanan' ? ($bulanList[$selectedBulan].' '.$selectedTahun) : ('Tahun '.$selectedTahun.' (Insidental)') }}
                    </p>
                </div>
            </div>

            {{-- Tabel Status Pembayaran per KK --}}
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">
                            {{ $selectedJenis->nama_iuran }}
                            <span class="text-xs font-normal text-slate-500">
                                — Tarif standar Rp {{ number_format($selectedJenis->nominal, 0, ',', '.') }}
                            </span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Periode: {{ $selectedJenis->tipe === 'bulanan' ? $bulanList[$selectedBulan].' '.$selectedTahun : 'Tahun '.$selectedTahun.' (Insidental)' }}
                            &middot; Mendukung pembayaran penuh maupun bertahap (cicilan).
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3.5">No. Rumah</th>
                                <th class="px-5 py-3.5">Kepala Keluarga &amp; No. KK</th>
                                <th class="px-5 py-3.5">Domisili</th>
                                <th class="px-5 py-3.5">Status Pembayaran</th>
                                <th class="px-5 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse($kkList as $kk)
                                @php
                                    $info = $statusByKk[$kk->id];
                                    $status = $info['status'];
                                    $latest = $info['latest'];
                                    $namaKepala = $kk->kepalaKeluarga?->nama_lengkap ?? 'Tanpa Kepala Keluarga';

                                    $riwayatParams = [
                                        'q' => $kk->no_kk,
                                        'jenis_iuran_id' => $selectedJenis->id,
                                        'tahun' => $selectedTahun,
                                    ];
                                    if ($selectedJenis->tipe === 'bulanan') {
                                        $riwayatParams['bulan'] = $selectedBulan;
                                    }
                                    $riwayatUrl = route('iuran.riwayat', $riwayatParams);
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-5 py-3.5 font-semibold text-slate-900 whitespace-nowrap">
                                        {{ $kk->nomor_rumah }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-slate-900">{{ $namaKepala }}</div>
                                        <div class="font-mono text-xs text-slate-400">{{ $kk->no_kk }}</div>
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
                                    <td class="px-5 py-3.5">
                                        @if($status === 'lunas')
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                                    Lunas
                                                </span>
                                                <span class="text-xs font-semibold text-slate-800">
                                                    Rp {{ number_format($info['total_dibayar'], 0, ',', '.') }}
                                                </span>
                                            </div>
                                            @if($latest)
                                                <div class="text-xs text-slate-400 mt-1">
                                                    {{ $latest->tanggal_bayar?->format('d/m/Y') }} &middot; {{ ucfirst($latest->metode_bayar) }}
                                                    @if($info['list_bayar']->count() > 1)
                                                        &middot; {{ $info['list_bayar']->count() }}x bayar
                                                    @endif
                                                </div>
                                            @endif
                                        @elseif($status === 'sebagian')
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-300">
                                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                                    Kurang Rp {{ number_format($info['sisa_tagihan'], 0, ',', '.') }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-slate-500 mt-1">
                                                Terbayar <strong class="text-slate-700">Rp {{ number_format($info['total_dibayar'], 0, ',', '.') }}</strong>
                                                dari Rp {{ number_format($info['tarif_standar'], 0, ',', '.') }}
                                                @if($latest)
                                                    &middot; Terakhir {{ $latest->tanggal_bayar?->format('d/m/Y') }}
                                                @endif
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                                Belum Bayar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        @if($status === 'lunas')
                                            <a
                                                href="{{ $riwayatUrl }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 transition"
                                            >
                                                <i data-lucide="receipt" class="w-3.5 h-3.5"></i>
                                                <span>Detail / Batalkan</span>
                                            </a>
                                        @elseif($status === 'sebagian')
                                            <div class="inline-flex items-center gap-1.5">
                                                <button
                                                    type="button"
                                                    @click="openPayModal({{ $kk->id }}, {{ Js::from($namaKepala) }}, {{ Js::from($kk->no_kk) }}, {{ $info['sisa_tagihan'] }}, {{ $info['total_dibayar'] }}, {{ $info['sisa_tagihan'] }})"
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition"
                                                >
                                                    <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                                                    <span>Lunasi</span>
                                                </button>
                                                <a
                                                    href="{{ $riwayatUrl }}"
                                                    title="Lihat riwayat cicilan KK ini"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-600 transition"
                                                >
                                                    <i data-lucide="history" class="w-3.5 h-3.5"></i>
                                                    <span>Riwayat</span>
                                                </a>
                                            </div>
                                        @else
                                            <button
                                                type="button"
                                                @click="openPayModal({{ $kk->id }}, {{ Js::from($namaKepala) }}, {{ Js::from($kk->no_kk) }}, {{ $selectedJenis->nominal }}, 0, {{ $selectedJenis->nominal }})"
                                                class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 hover:bg-teal-700 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition"
                                            >
                                                <i data-lucide="banknote" class="w-3.5 h-3.5"></i>
                                                <span>Catat Bayar</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                                        Tidak ada Kartu Keluarga aktif yang ditemukan pada RT ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Modal Catat Pembayaran / Pelunasan (Alpine.js) --}}
            <div
                x-show="showPayModal"
                x-cloak
                class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4"
                aria-modal="true"
                role="dialog"
            >
                <div
                    x-show="showPayModal"
                    x-transition.opacity
                    @click="showPayModal = false"
                    class="fixed inset-0 bg-slate-900/50"
                ></div>

                <div
                    x-show="showPayModal"
                    x-transition
                    class="relative z-10 w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-6"
                >
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900" x-text="sudahDibayar > 0 ? 'Pelunasan / Cicilan Iuran' : 'Catat Pembayaran Iuran'"></h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $selectedJenis->nama_iuran }}</p>
                        </div>
                        <button
                            type="button"
                            @click="showPayModal = false"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100"
                        >
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="mb-4 rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Kepala Keluarga</span>
                            <span class="font-semibold text-slate-900" x-text="kkLabel"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">No. KK</span>
                            <span class="font-mono text-slate-700" x-text="kkNo"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Periode</span>
                            <span class="font-semibold text-teal-700">
                                {{ $selectedJenis->tipe === 'bulanan' ? $bulanList[$selectedBulan].' '.$selectedTahun : 'Tahun '.$selectedTahun.' (Insidental)' }}
                            </span>
                        </div>
                        <template x-if="tarifStandar > 0">
                            <div class="pt-1.5 mt-1.5 border-t border-slate-200 space-y-1">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Tarif Standar</span>
                                    <span class="font-medium text-slate-800" x-text="formatRupiah(tarifStandar)"></span>
                                </div>
                                <div class="flex justify-between" x-show="sudahDibayar > 0">
                                    <span class="text-slate-500">Sudah Terbayar</span>
                                    <span class="font-medium text-emerald-700" x-text="formatRupiah(sudahDibayar)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-semibold text-slate-700">Sisa Tagihan</span>
                                    <span class="font-bold text-amber-700" x-text="formatRupiah(sisaTagihan)"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <form method="POST" action="{{ route('iuran.bayar.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="kartu_keluarga_id" :value="kkId">
                        <input type="hidden" name="jenis_iuran_id" value="{{ $selectedJenis->id }}">
                        @if($selectedJenis->tipe === 'bulanan')
                            <input type="hidden" name="periode_bulan" value="{{ $selectedBulan }}">
                        @endif
                        <input type="hidden" name="periode_tahun" value="{{ $selectedTahun }}">

                        <div>
                            <label for="modal_nominal_dibayar" class="block text-sm font-medium text-slate-700 mb-1">
                                Nominal Dibayar (Rp) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="modal_nominal_dibayar"
                                name="nominal_dibayar"
                                type="number"
                                min="{{ $selectedJenis->nominal > 0 ? 1 : 0 }}"
                                :max="tarifStandar > 0 ? sisaTagihan : null"
                                step="500"
                                x-model="nominal"
                                required
                                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                            >
                            <p class="mt-1 text-xs text-slate-400" x-show="tarifStandar > 0 && Number(nominal) > 0 && Number(nominal) < sisaTagihan">
                                Akan dicatat sebagai <strong>pembayaran sebagian</strong>. Sisa tagihan: <span class="font-semibold text-amber-700" x-text="formatRupiah(sisaTagihan - Number(nominal))"></span>
                            </p>
                            <p class="mt-1 text-xs text-red-600" x-show="tarifStandar > 0 && Number(nominal) > sisaTagihan">
                                Nominal melebihi sisa tagihan (<span x-text="formatRupiah(sisaTagihan)"></span>).
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="modal_metode_bayar" class="block text-sm font-medium text-slate-700 mb-1">
                                    Metode Bayar <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="modal_metode_bayar"
                                    name="metode_bayar"
                                    x-model="metode"
                                    required
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                                >
                                    <option value="tunai">Tunai</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>

                            <div>
                                <label for="modal_tanggal_bayar" class="block text-sm font-medium text-slate-700 mb-1">
                                    Tanggal Bayar <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="modal_tanggal_bayar"
                                    name="tanggal_bayar"
                                    type="date"
                                    x-model="tanggal"
                                    required
                                    class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="modal_catatan" class="block text-sm font-medium text-slate-700 mb-1">
                                Catatan <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <input
                                id="modal_catatan"
                                name="catatan"
                                type="text"
                                x-model="catatan"
                                placeholder="Misal: Cicilan tahap 1 / Pelunasan"
                                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                            >
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                            <button
                                type="button"
                                @click="showPayModal = false"
                                class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2 text-sm font-medium text-slate-700 transition"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                :disabled="tarifStandar > 0 && Number(nominal) > sisaTagihan"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:bg-slate-300 disabled:cursor-not-allowed px-4 py-2 text-sm font-semibold text-white shadow-sm transition"
                            >
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span>Simpan Pembayaran</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
