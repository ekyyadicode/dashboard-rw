@extends('layouts.app')

@section('title', 'Buku KIA — '.$balita->nama_balita)
@section('page-title', 'Buku KIA & Grafik Tumbuh Kembang')

@section('content')
    <div
        x-data="{
            showPeriksaModal: {{ $errors->any() ? 'true' : 'false' }},
            imunisasiText: {{ Js::from(old('imunisasi', '')) }},
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
        {{-- Back Link & Quick Actions --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('posyandu.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-teal-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar Balita</span>
            </a>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('posyandu.balita.edit', $balita) }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-3.5 py-2 text-sm font-medium text-slate-700 transition"
                >
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                    <span>Edit Balita</span>
                </a>
                <button
                    type="button"
                    @click="showPeriksaModal = true"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition"
                >
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Catat Timbang</span>
                </button>
            </div>
        </div>

        {{-- Kartu Profil Balita --}}
        <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 pb-5 border-b border-slate-100">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl {{ $balita->jenis_kelamin === 'L' ? 'bg-sky-50 text-sky-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center shrink-0">
                        <i data-lucide="baby" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-slate-900">{{ $balita->nama_balita }}</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $balita->jenis_kelamin === 'L' ? 'bg-sky-50 text-sky-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $balita->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $balita->status_aktif === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                {{ ucfirst($balita->status_aktif) }}
                            </span>
                        </div>
                        <p class="font-mono text-xs text-slate-400 mt-1">
                            NIK: {{ $balita->nik ?: 'Belum diisi' }}
                        </p>
                    </div>
                </div>

                <div class="inline-flex items-center gap-2 rounded-xl bg-teal-50 border border-teal-200 px-4 py-2.5 self-start">
                    <i data-lucide="cake" class="w-4 h-4 text-teal-600"></i>
                    <span class="text-sm font-bold text-teal-800">Usia: {{ $balita->usia_format }}</span>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-slate-400">Tanggal Lahir</p>
                    <p class="mt-1 font-semibold text-slate-900">{{ $balita->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Orang Tua</p>
                    <p class="mt-1 font-semibold text-slate-900">Ibu: {{ $balita->nama_ibu }}</p>
                    @if($balita->nama_ayah)
                        <p class="text-xs text-slate-500">Ayah: {{ $balita->nama_ayah }}</p>
                    @endif
                    @if($balita->no_hp_ortu)
                        <p class="text-xs text-slate-400 mt-0.5">HP: {{ $balita->no_hp_ortu }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-slate-400">Wilayah &amp; Alamat</p>
                    <p class="mt-1 font-semibold text-slate-900">RT {{ $balita->rt?->nomor_rt ?? '-' }}</p>
                    <p class="text-xs text-slate-500">{{ $balita->alamat ?: '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Grafik Garis Perkembangan (Chart.js CDN) --}}
        <div class="mb-6 bg-white border border-slate-200 rounded-2xl p-6">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-slate-900">Grafik Tumbuh Kembang Anak</h3>
                <p class="text-xs text-slate-500 mt-0.5">Perkembangan Berat Badan (kg) dan Tinggi Badan (cm) berdasarkan usia bulan.</p>
            </div>

            @if($riwayat->isEmpty())
                <div class="py-12 text-center text-slate-500 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                    <i data-lucide="line-chart" class="w-10 h-10 text-slate-300 mx-auto mb-2"></i>
                    <p class="text-sm font-medium text-slate-700">Belum ada data penimbangan untuk ditampilkan di grafik</p>
                    <p class="text-xs text-slate-400 mt-0.5">Klik tombol <strong>Catat Timbang</strong> di atas untuk mulai merekam perkembangan anak.</p>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/40 p-4">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-teal-700 mb-3">Berat Badan (kg)</h4>
                        <div class="relative h-64">
                            <canvas id="chartBeratBadan"></canvas>
                        </div>
                    </div>
                    <div class="rounded-xl border border-slate-100 bg-slate-50/40 p-4">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-sky-700 mb-3">Tinggi Badan (cm)</h4>
                        <div class="relative h-64">
                            <canvas id="chartTinggiBadan"></canvas>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Tabel Riwayat Penimbangan --}}
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200">
                <h3 class="text-base font-semibold text-slate-900">Riwayat Penimbangan &amp; Imunisasi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Menampilkan selisih perubahan berat dan tinggi dibanding pengukuran sebelumnya.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Tanggal</th>
                            <th class="px-5 py-3.5">Usia Saat Periksa</th>
                            <th class="px-5 py-3.5">Berat Badan</th>
                            <th class="px-5 py-3.5">Tinggi Badan</th>
                            <th class="px-5 py-3.5">Imunisasi &amp; Catatan</th>
                            <th class="px-5 py-3.5">Petugas</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        @forelse($riwayat as $item)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-5 py-3.5 whitespace-nowrap font-medium text-slate-800">
                                    {{ $item->tanggal_periksa?->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                        {{ $item->usia_format }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-900">
                                            {{ number_format((float) $item->berat_badan_kg, 2, ',', '.') }} kg
                                        </span>
                                        @if($item->berat_diff !== null)
                                            @if($item->berat_diff > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    +{{ number_format($item->berat_diff, 2, ',', '.') }} kg
                                                </span>
                                            @elseif($item->berat_diff < 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                                    {{ number_format($item->berat_diff, 2, ',', '.') }} kg
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                                                    Tetap
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-900">
                                            {{ number_format((float) $item->tinggi_badan_cm, 2, ',', '.') }} cm
                                        </span>
                                        @if($item->tinggi_diff !== null)
                                            @if($item->tinggi_diff > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    +{{ number_format($item->tinggi_diff, 2, ',', '.') }} cm
                                                </span>
                                            @elseif($item->tinggi_diff < 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                                    {{ number_format($item->tinggi_diff, 2, ',', '.') }} cm
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                                                    Tetap
                                                </span>
                                            @endif
                                        @endif
                                    </div>
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
                                <td colspan="7" class="px-5 py-10 text-center text-slate-500">
                                    Belum ada riwayat penimbangan untuk {{ $balita->nama_balita }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Catat Timbang untuk Balita Ini --}}
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
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Catat Pemeriksaan Baru</h3>
                        <p class="text-xs text-slate-500">{{ $balita->nama_balita }} ({{ $balita->usia_format }})</p>
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
                    <input type="hidden" name="balita_id" value="{{ $balita->id }}">

                    <div>
                        <label for="balita_tanggal" class="block text-sm font-medium text-slate-700 mb-1">
                            Tanggal Pemeriksaan <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="balita_tanggal"
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
                            <label for="balita_berat" class="block text-sm font-medium text-slate-700 mb-1">
                                Berat Badan (kg) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="balita_berat"
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
                            <label for="balita_tinggi" class="block text-sm font-medium text-slate-700 mb-1">
                                Tinggi Badan (cm) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="balita_tinggi"
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
                        <label for="balita_imunisasi" class="block text-sm font-medium text-slate-700 mb-1">
                            Imunisasi / Vitamin <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="balita_imunisasi"
                            name="imunisasi"
                            type="text"
                            x-model="imunisasiText"
                            placeholder="Klik pilihan cepat atau ketik manual"
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
                        <label for="balita_catatan" class="block text-sm font-medium text-slate-700 mb-1">
                            Catatan Kader <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="balita_catatan"
                            name="catatan"
                            type="text"
                            value="{{ old('catatan') }}"
                            placeholder="Contoh: Gizi baik"
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

    @if($riwayat->isNotEmpty())
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const labels = @json($chartLabels);
                const beratData = @json($chartBerat);
                const tinggiData = @json($chartTinggi);

                const commonOptions = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 10,
                            cornerRadius: 8,
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11 } },
                        },
                        y: {
                            beginAtZero: false,
                            grid: { color: '#e2e8f0' },
                            ticks: { font: { size: 11 } },
                        },
                    },
                };

                const beratCtx = document.getElementById('chartBeratBadan');
                if (beratCtx) {
                    new Chart(beratCtx, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Berat Badan (kg)',
                                data: beratData,
                                borderColor: '#0d9488',
                                backgroundColor: 'rgba(13, 148, 136, 0.12)',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBackgroundColor: '#0d9488',
                                tension: 0.3,
                                fill: true,
                            }],
                        },
                        options: commonOptions,
                    });
                }

                const tinggiCtx = document.getElementById('chartTinggiBadan');
                if (tinggiCtx) {
                    new Chart(tinggiCtx, {
                        type: 'line',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Tinggi Badan (cm)',
                                data: tinggiData,
                                borderColor: '#0284c7',
                                backgroundColor: 'rgba(2, 132, 199, 0.12)',
                                borderWidth: 2.5,
                                pointRadius: 4,
                                pointBackgroundColor: '#0284c7',
                                tension: 0.3,
                                fill: true,
                            }],
                        },
                        options: commonOptions,
                    });
                }
            });
        </script>
    @endif
@endsection
