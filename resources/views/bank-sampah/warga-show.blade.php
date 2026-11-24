@extends('layouts.app')

@section('title', 'Buku Tabungan — '.$warga->nama_lengkap)
@section('page-title', 'Buku Tabungan Bank Sampah')

@section('content')
    <div
        x-data="{
            showSetorModal: false,
            showTarikModal: false,
            tarikNominal: '',
            saldoTersedia: {{ (int) $warga->saldo_bank_sampah }},
            formatRupiah(val) {
                return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
            }
        }"
    >
        {{-- Back Link & Quick Actions --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('bank-sampah.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-teal-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar Saldo Warga</span>
            </a>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="showSetorModal = true"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition"
                >
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Setor Sampah</span>
                </button>
                @if($warga->saldo_bank_sampah > 0)
                    <button
                        type="button"
                        @click="showTarikModal = true"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition"
                    >
                        <i data-lucide="minus-circle" class="w-4 h-4"></i>
                        <span>Tarik Saldo</span>
                    </button>
                @else
                    <button
                        type="button"
                        disabled
                        class="inline-flex items-center gap-1.5 rounded-xl bg-slate-200 px-4 py-2 text-sm font-medium text-slate-400 cursor-not-allowed"
                    >
                        <i data-lucide="minus-circle" class="w-4 h-4"></i>
                        <span>Tarik Saldo</span>
                    </button>
                @endif
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Digital Passbook Header Card --}}
        <div class="mb-6 bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="bg-gradient-to-r from-teal-600 to-emerald-600 px-6 py-5 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 text-xs font-medium text-teal-100 uppercase tracking-wider">
                        <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
                        <span>Buku Tabungan Digital &middot; RT {{ $warga->rt?->nomor_rt ?? '-' }}</span>
                    </div>
                    <h2 class="mt-1 text-xl font-bold">{{ $warga->nama_lengkap }}</h2>
                    <p class="text-xs text-teal-100 font-mono mt-0.5">
                        NIK: {{ $warga->nik }}
                        @if($warga->kartuKeluarga)
                            &middot; Rumah {{ $warga->kartuKeluarga->nomor_rumah }}
                        @endif
                    </p>
                </div>

                <div class="sm:text-right bg-white/15 backdrop-blur-sm rounded-xl px-4 py-3 border border-white/20">
                    <div class="text-xs text-teal-100">Saldo Aktif Saat Ini</div>
                    <div class="text-2xl font-bold tracking-tight">
                        Rp {{ number_format($warga->saldo_bank_sampah, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-200 text-sm">
                <div class="px-6 py-4">
                    <p class="text-xs text-slate-500">Total Sampah Disetor</p>
                    <p class="mt-1 text-base font-bold text-slate-900">{{ number_format($totalBeratSetor, 2, ',', '.') }} kg</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-xs text-slate-500">Total Nominal Setoran</p>
                    <p class="mt-1 text-base font-bold text-emerald-700">+ Rp {{ number_format($totalNominalSetor, 0, ',', '.') }}</p>
                </div>
                <div class="px-6 py-4">
                    <p class="text-xs text-slate-500">Total Penarikan Saldo</p>
                    <p class="mt-1 text-base font-bold text-amber-700">- Rp {{ number_format($totalNominalTarik, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Tabel Mutasi Rekening --}}
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h3 class="text-base font-semibold text-slate-900">Mutasi Transaksi Tabungan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Riwayat keluar-masuk tabungan Bank Sampah milik {{ $warga->nama_lengkap }}.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Tanggal</th>
                            <th class="px-5 py-3.5 text-center">Jenis</th>
                            <th class="px-5 py-3.5 text-right">Berat</th>
                            <th class="px-5 py-3.5 text-right">Mutasi</th>
                            <th class="px-5 py-3.5 text-right">Saldo Akhir</th>
                            <th class="px-5 py-3.5">Catatan &amp; Petugas</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($transaksiList as $trx)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap font-medium text-slate-800">
                                    {{ $trx->tanggal_transaksi?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                    @if($trx->jenis_transaksi === 'setor')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Setor
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
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
                                <td class="px-5 py-3.5">
                                    <div class="text-slate-700">{{ $trx->catatan ?: '-' }}</div>
                                    <div class="text-xs text-slate-400">Petugas: {{ $trx->creator?->name ?? 'Sistem' }}</div>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    @if((int) $trx->id === (int) $latestTransactionId)
                                        <form
                                            method="POST"
                                            action="{{ route('bank-sampah.transaksi.destroy', $trx) }}"
                                            onsubmit="return confirm('Yakin ingin membatalkan transaksi terakhir ini? Saldo akan otomatis disesuaikan kembali.')"
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
                                    @else
                                        <span class="text-xs text-slate-300">&minus;</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                    Belum ada riwayat transaksi tabungan untuk warga ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transaksiList->hasPages())
                <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $transaksiList->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Setor untuk Warga Ini --}}
        <div x-show="showSetorModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" aria-modal="true" role="dialog">
            <div x-show="showSetorModal" x-transition.opacity @click="showSetorModal = false" class="fixed inset-0 bg-slate-900/50"></div>
            <div x-show="showSetorModal" x-transition class="relative z-10 w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Catat Setoran Sampah</h3>
                        <p class="text-xs text-slate-500">{{ $warga->nama_lengkap }}</p>
                    </div>
                    <button type="button" @click="showSetorModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('bank-sampah.setor.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="warga_id" value="{{ $warga->id }}">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="warga_berat_kg" class="block text-sm font-medium text-slate-700 mb-1">
                                Berat (kg) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="warga_berat_kg"
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
                            <label for="warga_setor_nominal" class="block text-sm font-medium text-slate-700 mb-1">
                                Nilai Setoran (Rp) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="warga_setor_nominal"
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
                        <label for="warga_setor_tgl" class="block text-sm font-medium text-slate-700 mb-1">
                            Tanggal Setor <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="warga_setor_tgl"
                            name="tanggal_transaksi"
                            type="date"
                            max="{{ date('Y-m-d') }}"
                            value="{{ date('Y-m-d') }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label for="warga_setor_catatan" class="block text-sm font-medium text-slate-700 mb-1">
                            Catatan <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="warga_setor_catatan"
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

        {{-- Modal Tarik Saldo untuk Warga Ini --}}
        <div x-show="showTarikModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" aria-modal="true" role="dialog">
            <div x-show="showTarikModal" x-transition.opacity @click="showTarikModal = false" class="fixed inset-0 bg-slate-900/50"></div>
            <div x-show="showTarikModal" x-transition class="relative z-10 w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Tarik Saldo Tabungan</h3>
                        <p class="text-xs text-slate-500">{{ $warga->nama_lengkap }}</p>
                    </div>
                    <button type="button" @click="showTarikModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="mb-4 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 flex items-center justify-between">
                    <span class="text-xs font-medium text-amber-800">Saldo Tersedia:</span>
                    <span class="text-sm font-bold text-amber-900">Rp {{ number_format($warga->saldo_bank_sampah, 0, ',', '.') }}</span>
                </div>

                <form method="POST" action="{{ route('bank-sampah.tarik.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="warga_id" value="{{ $warga->id }}">

                    <div>
                        <label for="warga_tarik_nominal" class="block text-sm font-medium text-slate-700 mb-1">
                            Nominal Penarikan (Rp) <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="warga_tarik_nominal"
                            name="nominal"
                            type="number"
                            min="1"
                            max="{{ (int) $warga->saldo_bank_sampah }}"
                            x-model="tarikNominal"
                            required
                            placeholder="Masukkan nominal penarikan"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        <p x-show="Number(tarikNominal) > saldoTersedia" class="mt-1 text-xs text-red-600">
                            Nominal melebihi saldo tersedia.
                        </p>
                    </div>

                    <div>
                        <label for="warga_tarik_tgl" class="block text-sm font-medium text-slate-700 mb-1">
                            Tanggal Penarikan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="warga_tarik_tgl"
                            name="tanggal_transaksi"
                            type="date"
                            max="{{ date('Y-m-d') }}"
                            value="{{ date('Y-m-d') }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label for="warga_tarik_catatan" class="block text-sm font-medium text-slate-700 mb-1">
                            Catatan <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="warga_tarik_catatan"
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
                            :disabled="Number(tarikNominal) > saldoTersedia || saldoTersedia <= 0"
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

