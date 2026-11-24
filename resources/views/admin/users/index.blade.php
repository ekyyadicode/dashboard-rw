@extends('layouts.app')

@section('title', 'Akun Pengurus')
@section('page-title', 'Manajemen Akun Pengurus')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Daftar Akun Pengguna</h2>
            <p class="text-sm text-slate-500">Atur akun Super Admin RW, Pengurus RT, hak akses fitur, dan status aktif.</p>
        </div>
        <a
            href="{{ route('admin.users.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition"
        >
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Tambah Akun</span>
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Nama</th>
                        <th class="px-5 py-3.5">Username</th>
                        <th class="px-5 py-3.5">Role &amp; Wilayah</th>
                        <th class="px-5 py-3.5">Hak Akses Fitur</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-semibold text-slate-900 flex items-center gap-2">
                                    <span>{{ $user->name }}</span>
                                    @if($user->id === auth()->id())
                                        <span class="px-1.5 py-0.5 text-[10px] font-medium rounded bg-slate-100 text-slate-600">Anda</span>
                                    @endif
                                </div>
                                @if($user->email)
                                    <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono text-xs text-slate-700">
                                {{ $user->username }}
                            </td>
                            <td class="px-5 py-3.5">
                                @if($user->isSuperAdmin())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-50 text-teal-700 border border-teal-200">
                                        Super Admin RW
                                    </span>
                                @else
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                            Pengurus
                                        </span>
                                        <span class="text-xs text-slate-500">
                                            RT {{ $user->rt?->nomor_rt ?? '-' }}
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($user->isSuperAdmin())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-teal-50 text-teal-700">
                                        Semua Fitur
                                    </span>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($user->permissions ?? [] as $perm)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/70">
                                                {{ $availablePermissions[$perm] ?? $perm }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400 italic">Tidak ada fitur</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($user->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a
                                        href="{{ route('admin.users.edit', $user) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 transition"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        <span>Edit</span>
                                    </a>

                                    @if($user->id !== auth()->id())
                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.destroy', $user) }}"
                                            onsubmit="return confirm('Yakin ingin menghapus akun {{ $user->name }}?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 px-3 py-1.5 text-xs font-medium text-red-700 transition"
                                            >
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    @else
                                        <button
                                            type="button"
                                            disabled
                                            title="Akun yang sedang dipakai login tidak dapat dihapus"
                                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-400 cursor-not-allowed"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Hapus</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                Belum ada akun pengguna terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

