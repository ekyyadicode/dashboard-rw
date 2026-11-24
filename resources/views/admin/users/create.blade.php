@extends('layouts.app')

@section('title', 'Tambah Akun Pengurus')
@section('page-title', 'Tambah Akun Pengurus')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-5">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-teal-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar Akun</span>
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6" x-data="{ role: '{{ old('role', 'pengurus') }}' }">
            <h2 class="text-base font-semibold text-slate-900 mb-1">Form Akun Baru</h2>
            <p class="text-xs text-slate-500 mb-6">Buat akun untuk Super Admin RW atau Pengurus RT beserta hak akses fiturnya.</p>

            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            placeholder="Nama lengkap"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('name')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Username / No. HP <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            required
                            placeholder="Contoh: pengurus02"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('username')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Email <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            placeholder="nama@contoh.id"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            minlength="6"
                            placeholder="Minimal 6 karakter"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="role" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="role"
                            name="role"
                            x-model="role"
                            required
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="pengurus">Pengurus RT</option>
                            <option value="super_admin">Super Admin RW</option>
                        </select>
                        @error('role')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="rt_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Wilayah RT <span class="text-red-500" x-show="role === 'pengurus'">*</span>
                        </label>
                        <select
                            id="rt_id"
                            name="rt_id"
                            :disabled="role === 'super_admin'"
                            :required="role === 'pengurus'"
                            class="block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 disabled:bg-slate-100 disabled:text-slate-400 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                            <option value="">-- Pilih Wilayah RT --</option>
                            @foreach($rts as $rt)
                                <option value="{{ $rt->id }}" {{ old('rt_id') == $rt->id ? 'selected' : '' }}>
                                    RT {{ $rt->nomor_rt }} {{ $rt->nama_ketua_rt ? '('.$rt->nama_ketua_rt.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-400" x-show="role === 'super_admin'">
                            Super Admin RW memiliki akses ke seluruh wilayah RT.
                        </p>
                        @error('rt_id')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Hak Akses Fitur Operasional
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        @php
                            $oldPermissions = old('permissions', array_keys($availablePermissions));
                        @endphp
                        @foreach($availablePermissions as $key => $label)
                            <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 hover:bg-slate-50 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $key }}"
                                    {{ in_array($key, $oldPermissions) ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-600"
                                >
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('permissions')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-1">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}
                            class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-600"
                        >
                        <span class="text-sm font-medium text-slate-700">Akun Aktif (dapat digunakan untuk login)</span>
                    </label>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3 border-t border-slate-100">
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-4 py-2.5 text-sm font-medium text-slate-700 transition"
                    >
                        Batal
                    </a>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition"
                    >
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan Akun</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

