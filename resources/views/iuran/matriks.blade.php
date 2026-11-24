@extends('layouts.app')

@section('title', 'Matriks Iuran Tahunan')
@section('page-title', 'Iuran Warga')

@section('content')
    @include('iuran._tabs')

    <div
        x-data="{
            showPayModal: false,
            kkId: '',
            kkLabel: '',
            kkNo: '',
            bulanNum: '',
            bulanLabel: '',
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
            openCellModal(kkId, nama, noKk, bulanNum, bulanLabel, defaultNominal, sudahDibayar = 0, sisaTagihan = 0) {
                this.kkId = kkId;
                this.kkLabel = nama;
                this.kkNo = noKk;
                this.bulanNum = bulanNum;
                this.bulanLabel = bulanLabel;
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
        {{-- Filter Bar --}}
        <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-4 sm:p-5">
            <form method="GET" action="{{ route('iuran.matriks') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
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

                <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-4' : 'lg:col-span-5' }}">
                    <label for="jenis_iuran_id" class="block text-xs font-medium text-slate-600 mb-1">Jenis Iuran (Bulanan)</label>
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
                            <option value="">-- Belum ada jenis iuran bulanan --</option>
                        @endforelse
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

                <div class="{{ auth()->user()->isSuperAdmin() ? 'lg:col-span-2' : 'lg:col-span-3' }}">
                    <label for="q" class="block text-xs font-medium text-slate-600 mb-1">Cari KK / Rumah</label>
                    <input
                        id="q"
                        name="q"
                        type="text"
                        value="{{ request('q') }}"
                        placeholder="Nama atau No. Rumah"
                        class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                    >
                </div>

                <div class="lg:col-span-2">
                    <button
                        type="submit"
                        class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 px-3.5 py-2 text-sm font-medium text-white transition"
                    >
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        <span>Tampilkan</span>
                    </button>
                </div>
            </form>
        </div>

        @if($jenisIuranList->isEmpty())
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <i data-lucide="info" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-amber-900">Belum ada Jenis Iuran Bulanan aktif untuk RT ini</h3>
                        <p class="text-xs text-amber-800 mt-1">Matriks 12 bulan menampilkan iuran bertipe <strong>Bulanan</strong>. Tambahkan jenis iuran bulanan di tab Pengaturan Jenis Iuran.</p>
                    </div>
                </div>
                <a
                    href="{{ route('iuran.jenis.index', auth()->user()->isSuperAdmin() ? ['rt_id' => $selectedRtId] : []) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition shrink-0"
                >
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Atur Jenis Iuran</span>
                </a>
            </div>
        @else
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">
                            Matriks {{ $selectedJenis->nama_iuran }} — Tahun {{ $selectedTahun }}
                        </h3>
                        <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-emerald-100 border border-emerald-300"></span> Lunas</span>
                            <span class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded bg-amber-100 border border-amber-300"></span> Bayar Sebagian (&frac12;) — klik untuk lunasi</span>
                            <span class="inline-flex items-center gap-1"><span class="inline-block w-3 h-3 rounded border border-dashed border-slate-300"></span> Belum Bayar (&minus;) — klik untuk catat bayar</span>
                        </div>
                    </div>
                    <div class="inline-flex items-center gap-2 rounded-xl bg-teal-50 border border-teal-200 px-3.5 py-2 text-xs font-semibold text-teal-800 shrink-0">
                        <i data-lucide="wallet" class="w-4 h-4 text-teal-600"></i>
                        <span>Total {{ $selectedTahun }}: Rp {{ number_format($totalTerkumpulTahunan, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-4 py-3.5 text-left sticky left-0 bg-slate-50 z-10 min-w-[200px] border-r border-slate-200">
                                    Rumah &amp; Kepala Keluarga
                                </th>
                                @foreach($bulanSingkat as $num => $singkat)
                                    <th class="px-2 py-3.5 text-center min-w-[56px]">{{ $singkat }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse($kkList as $kk)
                                @php
                                    $namaKepala = $kk->kepalaKeluarga?->nama_lengkap ?? 'Tanpa Kepala Keluarga';
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="px-4 py-3 sticky left-0 bg-white z-10 border-r border-slate-200">
                                        <div class="font-semibold text-slate-900 truncate max-w-[220px]" title="{{ $namaKepala }}">
                                            {{ $namaKepala }}
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            Rumah {{ $kk->nomor_rumah }}
                                        </div>
                                    </td>
                                    @foreach($bulanList as $num => $namaBulan)
                                        @php
                                            $cell = $matriks[$kk->id][$num] ?? null;
                                            $cellStatus = $cell['status'] ?? 'belum';
                                        @endphp
                                        <td class="px-1.5 py-3 text-center">
                                            @if($cellStatus === 'lunas')
                                                <span
                                                    title="Lunas {{ $namaBulan }} {{ $selectedTahun }} — Total Rp {{ number_format($cell['total_dibayar'], 0, ',', '.') }} ({{ $cell['count'] }}x bayar, terakhir {{ $cell['latest_date'] }})"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-200"
                                                >
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </span>
                                            @elseif($cellStatus === 'sebagian')
                                                <button
                                                    type="button"
                                                    @click="openCellModal({{ $kk->id }}, {{ Js::from($namaKepala) }}, {{ Js::from($kk->no_kk) }}, {{ $num }}, {{ Js::from($namaBulan) }}, {{ $cell['sisa_tagihan'] }}, {{ $cell['total_dibayar'] }}, {{ $cell['sisa_tagihan'] }})"
                                                    title="Bayar sebagian {{ $namaBulan }} {{ $selectedTahun }} — Terbayar Rp {{ number_format($cell['total_dibayar'], 0, ',', '.') }}, kurang Rp {{ number_format($cell['sisa_tagihan'], 0, ',', '.') }}. Klik untuk melunasi."
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-800 border border-amber-300 hover:bg-amber-100 hover:border-amber-400 transition font-bold text-xs"
                                                >
                                                    &frac12;
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    @click="openCellModal({{ $kk->id }}, {{ Js::from($namaKepala) }}, {{ Js::from($kk->no_kk) }}, {{ $num }}, {{ Js::from($namaBulan) }}, {{ $selectedJenis->nominal }}, 0, {{ $selectedJenis->nominal }})"
                                                    title="Belum bayar {{ $namaBulan }} {{ $selectedTahun }} — Klik untuk catat bayar"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-dashed border-slate-300 text-slate-400 hover:border-teal-500 hover:bg-teal-50 hover:text-teal-700 transition font-semibold"
                                                >
                                                    &minus;
                                                </button>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="px-5 py-12 text-center text-slate-500">
                                        Belum ada data Kartu Keluarga aktif di RT ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Modal Pembayaran / Pelunasan dari Sel Matriks --}}
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
                            <h3 class="text-base font-semibold text-slate-900" x-text="sudahDibayar > 0 ? 'Pelunasan Iuran' : 'Catat Pembayaran Iuran'"></h3>
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
                            <span class="font-semibold text-teal-700" x-text="bulanLabel + ' {{ $selectedTahun }}'"></span>
                        </div>
                        <template x-if="tarifStandar > 0">
                            <div class="pt-1.5 mt-1.5 border-t border-slate-200 space-y-1">
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
                        <input type="hidden" name="periode_bulan" :value="bulanNum">
                        <input type="hidden" name="periode_tahun" value="{{ $selectedTahun }}">

                        <div>
                            <label for="matriks_nominal" class="block text-sm font-medium text-slate-700 mb-1">
                                Nominal Dibayar (Rp) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="matriks_nominal"
                                name="nominal_dibayar"
                                type="number"
                                min="{{ $selectedJenis->nominal > 0 ? 1 : 0 }}"
                                :max="tarifStandar > 0 ? sisaTagihan : null"
                                step="500"
                                x-model="nominal"
                                required
                                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                            >
                            <p class="mt-1 text-xs text-red-600" x-show="tarifStandar > 0 && Number(nominal) > sisaTagihan">
                                Nominal melebihi sisa tagihan (<span x-text="formatRupiah(sisaTagihan)"></span>).
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="matriks_metode" class="block text-sm font-medium text-slate-700 mb-1">
                                    Metode Bayar <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="matriks_metode"
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
                                <label for="matriks_tanggal" class="block text-sm font-medium text-slate-700 mb-1">
                                    Tanggal Bayar <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="matriks_tanggal"
                                    name="tanggal_bayar"
                                    type="date"
                                    x-model="tanggal"
                                    required
                                    class="block w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="matriks_catatan" class="block text-sm font-medium text-slate-700 mb-1">
                                Catatan <span class="text-slate-400 font-normal">(opsional)</span>
                            </label>
                            <input
                                id="matriks_catatan"
                                name="catatan"
                                type="text"
                                x-model="catatan"
                                placeholder="Catatan opsional"
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
