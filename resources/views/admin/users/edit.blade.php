@extends('layouts.app')

@section('title', 'Edit Akun Pengurus')
@section('page-title', 'Edit Akun Pengurus')

@section('content')
    @php
        $isSelf = $user->id === auth()->id();
    @endphp
    <div class="max-w-2xl">
        <div class="mb-5">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-teal-700 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Daftar Akun</span>
            </a>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6" x-data="{ role: '{{ old('role', $user->role) }}' }">
            <h2 class="text-base font-semibold text-slate-900 mb-1">Edit Akun: {{ $user->name }}</h2>
            <p class="text-xs text-slate-500 mb-6">Perbarui data akun, wilayah RT, hak akses fitur, atau status aktif pengguna.</p>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name', $user->name) }}"
                            required
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
                            value="{{ old('username', $user->username) }}"
                            required
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
                            value="{{ old('email', $user->email) }}"
                            class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 focus:outline-none"
                        >
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Password Baru <span class="text-slate-400 font-normal">(opsional)</span>
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            minlength="6"
                            placeholder="Kosongkan jika tidak diubah"
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
                            <option value="pengurus" {{ $isSelf ? 'disabled' : '' }}>Pengurus RT</option>
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
                                <option value="{{ $rt->id }}" {{ old('rt_id', $user->rt_id) == $rt->id ? 'selected' : '' }}>
                                    RT {{ $rt->nomor_rt }} {{ $rt->nama_ketua_rt ? '('.$rt->nama_ketua_rt.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-400" x-show="role === 'super_admin'">
                            Super Admin RW otomatis memiliki akses ke seluruh wilayah RT.
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
                            $selectedPermissions = old('permissions', $user->permissions ?? []);
                        @endphp
                        @foreach($availablePermissions as $key => $label)
                            <label class="flex items-center gap-2.5 rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-700 hover:bg-slate-50 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $key }}"
                                    {{ in_array($key, $selectedPermissions) ? 'checked' : '' }}
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
                    @if($isSelf)
                        <input type="hidden" name="is_active" value="1">
                        <label class="inline-flex items-center gap-2.5 select-none opacity-70 cursor-not-allowed">
                            <input
                                type="checkbox"
                                checked
                                disabled
                                class="h-4 w-4 rounded border-slate-300 text-teal-600"
                            >
                            <span class="text-sm font-medium text-slate-700">Akun Aktif (akun Anda sendiri tidak dapat dinonaktifkan)</span>
                        </label>
                    @else
                        <label class="inline-flex items-center gap-2.5 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-600"
                            >
                            <span class="text-sm font-medium text-slate-700">Akun Aktif (dapat digunakan untuk login)</span>
                        </label>
                    @endif
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
                        <span>Perbarui Akun</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

