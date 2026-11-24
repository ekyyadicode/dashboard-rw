@extends('layouts.app')

@section('title', 'Tambah Kartu Keluarga')
@section('page-title', 'Tambah Kartu Keluarga Baru')

@section('content')
    @php
        $initialAnggota = old('anggota');
        if (! is_array($initialAnggota) || count($initialAnggota) === 0) {
            $initialAnggota = [[
                'nik' => '',
                'nama_lengkap' => '',
                'jenis_kelamin' => '',
                'tanggal_lahir' => '',
                'status_keluarga' => 'kepala_keluarga',
                'no_hp' => '',
            ]];
        } else {
            $initialAnggota = array_values($initialAnggota);
        }

        $validationErrors = $errors->getMessages();
    @endphp

    <div
        class="max-w-4xl"
        x-data="{
            anggota: {{ Js::from($initialAnggota) }},
            errors: {{ Js::from($validationErrors) }},
            addRow() {
                const defaultStatus = this.anggota.length === 1 ? 'istri' : 'anak';
                this.anggota.push({
                    nik: '',
                    nama_lengkap: '',
                    jenis_kelamin: '',
                    tanggal_lahir: '',
                    status_keluarga: defaultStatus,
                    no_hp: ''
                });
            },
            removeRow(index) {
                if (this.anggota.length > 1 && index > 0) {
                    this.anggota.splice(index, 1);
                }
            },
            getError(index, field) {
                const key = `anggota.${index}.${field}`;
                return this.errors[key] ? this.errors[key][0] : null;
            }
        }"
    >
        <div class="mb-5">
            <a href="{{ route('warga.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-teal-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar Kartu Keluarga</span>
            </a>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold mb-1">Periksa kembali input Anda:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('warga.kk.store') }}" class="space-y-6">
            @csrf

            {{-- Bagian 1: Data Kartu Keluarga & Alamat --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                        <i data-lucide="home" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Bagian 1: Data Kartu Keluarga &amp; Alamat</h2>
                        <p class="text-xs text-slate-500">Masukkan nomor KK 16 digit dan informasi tempat tinggal.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if(auth()->user()->isSuperAdmin())
                            <div>
                                <label for="rt_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Wilayah RT <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="rt_id"
                                    name="rt_id"
                                    required
                                    class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                                >
                                    <option value="">-- Pilih Wilayah RT --</option>
                                    @foreach($rts as $rt)
                                        <option value="{{ $rt->id }}" {{ old('rt_id') == $rt->id ? 'selected' : '' }}>
                                            RT {{ $rt->nomor_rt }} {{ $rt->nama_ketua_rt ? '('.$rt->nama_ketua_rt.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('rt_id')
                                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @else
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Wilayah RT</label>
                                <input
                                    type="text"
                                    value="RT {{ auth()->user()->rt?->nomor_rt }}"
                                    disabled
                                    class="block w-full rounded-xl border border-slate-200 bg-slate-100 px-3.5 py-2.5 text-sm text-slate-600"
                                >
                            </div>
                        @endif

                        <div>
                            <label for="no_kk" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Nomor KK (16 Digit) <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="no_kk"
                                name="no_kk"
                                type="text"
                                inputmode="numeric"
                                maxlength="16"
                                value="{{ old('no_kk') }}"
                                required
                                placeholder="Contoh: 3671010101010001"
                                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-mono text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                            >
                            @error('no_kk')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="alamat" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Alamat Lengkap <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            id="alamat"
                            name="alamat"
                            rows="2"
                            required
                            placeholder="Nama jalan, gang, atau blok rumah"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="nomor_rumah" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Nomor Rumah <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="nomor_rumah"
                                name="nomor_rumah"
                                type="text"
                                value="{{ old('nomor_rumah') }}"
                                required
                                placeholder="Contoh: A-12 / No. 5"
                                class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                            >
                            @error('nomor_rumah')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status_domisili" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Status Domisili <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="status_domisili"
                                name="status_domisili"
                                required
                                class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                            >
                                <option value="tetap" {{ old('status_domisili', 'tetap') === 'tetap' ? 'selected' : '' }}>Tetap</option>
                                <option value="kontrak" {{ old('status_domisili') === 'kontrak' ? 'selected' : '' }}>Kontrak</option>
                            </select>
                            @error('status_domisili')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status_aktif" class="block text-sm font-medium text-slate-700 mb-1.5">
                                Status Aktif <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="status_aktif"
                                name="status_aktif"
                                required
                                class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                            >
                                <option value="aktif" {{ old('status_aktif', 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="pindah" {{ old('status_aktif') === 'pindah' ? 'selected' : '' }}>Pindah</option>
                            </select>
                            @error('status_aktif')
                                <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian 2: Daftar Anggota Keluarga (Dynamic Alpine.js) --}}
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-4 mb-5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-slate-900">
                                Bagian 2: Daftar Anggota Keluarga
                                <span class="ml-1.5 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-teal-50 text-teal-700" x-text="anggota.length + ' orang'"></span>
                            </h2>
                            <p class="text-xs text-slate-500">Anda bisa langsung menginput Kepala Keluarga, Istri, Anak, dan anggota lain sekaligus dalam satu kali simpan.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <template x-for="(item, index) in anggota" :key="index">
                        <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
                            <div class="flex items-center justify-between mb-4 pb-2.5 border-b border-slate-200/80">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold"
                                        x-text="index + 1"
                                    ></span>
                                    <span class="text-sm font-semibold text-slate-800" x-text="index === 0 ? 'Anggota 1 (Utama / Kepala Keluarga)' : 'Anggota ' + (index + 1)"></span>
                                </div>

                                <button
                                    type="button"
                                    x-show="index > 0"
                                    @click="removeRow(index)"
                                    class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 px-2.5 py-1 text-xs font-medium text-red-700 transition"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    <span>Hapus Baris</span>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">
                                        NIK (16 Digit) <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="16"
                                        required
                                        :name="`anggota[${index}][nik]`"
                                        x-model="item.nik"
                                        placeholder="16 digit NIK"
                                        class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-mono text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                                    >
                                    <p x-show="getError(index, 'nik')" x-text="getError(index, 'nik')" class="mt-1 text-xs text-red-600"></p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">
                                        Nama Lengkap <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        required
                                        :name="`anggota[${index}][nama_lengkap]`"
                                        x-model="item.nama_lengkap"
                                        placeholder="Sesuai KTP / KK"
                                        class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                                    >
                                    <p x-show="getError(index, 'nama_lengkap')" x-text="getError(index, 'nama_lengkap')" class="mt-1 text-xs text-red-600"></p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">
                                        Status Keluarga <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        required
                                        :name="`anggota[${index}][status_keluarga]`"
                                        x-model="item.status_keluarga"
                                        class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                                    >
                                        <option value="kepala_keluarga">Kepala Keluarga</option>
                                        <option value="istri">Istri</option>
                                        <option value="anak">Anak</option>
                                        <option value="famili_lain">Famili Lain</option>
                                    </select>
                                    <p x-show="getError(index, 'status_keluarga')" x-text="getError(index, 'status_keluarga')" class="mt-1 text-xs text-red-600"></p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">
                                        Jenis Kelamin <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        required
                                        :name="`anggota[${index}][jenis_kelamin]`"
                                        x-model="item.jenis_kelamin"
                                        class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                                    >
                                        <option value="">-- Pilih --</option>
                                        <option value="L">Laki-laki (L)</option>
                                        <option value="P">Perempuan (P)</option>
                                    </select>
                                    <p x-show="getError(index, 'jenis_kelamin')" x-text="getError(index, 'jenis_kelamin')" class="mt-1 text-xs text-red-600"></p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">
                                        Tanggal Lahir <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="date"
                                        max="{{ date('Y-m-d') }}"
                                        required
                                        :name="`anggota[${index}][tanggal_lahir]`"
                                        x-model="item.tanggal_lahir"
                                        class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                                    >
                                    <p x-show="getError(index, 'tanggal_lahir')" x-text="getError(index, 'tanggal_lahir')" class="mt-1 text-xs text-red-600"></p>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">
                                        No. HP <span class="text-slate-400 font-normal">(opsional)</span>
                                    </label>
                                    <input
                                        type="text"
                                        :name="`anggota[${index}][no_hp]`"
                                        x-model="item.no_hp"
                                        placeholder="081234567890"
                                        class="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                                    >
                                    <p x-show="getError(index, 'no_hp')" x-text="getError(index, 'no_hp')" class="mt-1 text-xs text-red-600"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Tombol Tambah Baris Anggota --}}
                <div class="mt-4">
                    <button
                        type="button"
                        @click="addRow()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border-2 border-dashed border-teal-300 bg-teal-50/60 hover:bg-teal-50 hover:border-teal-500 px-4 py-2.5 text-sm font-semibold text-teal-700 transition"
                    >
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        <span>+ Tambah Baris Anggota</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a
                    href="{{ route('warga.index') }}"
                    class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 transition"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                >
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span x-text="'Simpan KK & ' + anggota.length + ' Anggota'">Simpan Kartu Keluarga</span>
                </button>
            </div>
        </form>
    </div>
@endsection
