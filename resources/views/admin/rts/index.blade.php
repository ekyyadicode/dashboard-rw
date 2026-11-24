@extends('layouts.app')

@section('title', 'Data Wilayah RT')
@section('page-title', 'Data Wilayah RT')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Daftar Wilayah RT</h2>
            <p class="text-sm text-slate-500">Kelola daftar RT beserta informasi ketua RT di lingkungan RW.</p>
        </div>
        <a
            href="{{ route('admin.rts.create') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition"
        >
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Wilayah RT</span>
        </a>
    </div>

    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Nomor RT</th>
                        <th class="px-5 py-3.5">Nama Ketua RT</th>
                        <th class="px-5 py-3.5">No. HP Ketua</th>
                        <th class="px-5 py-3.5 text-center">Jumlah KK</th>
                        <th class="px-5 py-3.5 text-center">Akun Pengurus</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($rts as $rt)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-5 py-3.5 font-semibold text-slate-900">
                                RT {{ $rt->nomor_rt }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">
                                {{ $rt->nama_ketua_rt ?: '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $rt->no_hp_ketua ?: '-' }}
                            </td>
                            <td class="px-5 py-3.5 text-center font-medium text-slate-700">
                                {{ $rt->kk_count }}
                            </td>
                            <td class="px-5 py-3.5 text-center font-medium text-slate-700">
                                {{ $rt->users_count }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2">
                                    <a
                                        href="{{ route('admin.rts.edit', $rt) }}"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 px-3 py-1.5 text-xs font-medium text-slate-700 transition"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        <span>Edit</span>
                                    </a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.rts.destroy', $rt) }}"
                                        onsubmit="return confirm('Yakin ingin menghapus wilayah RT {{ $rt->nomor_rt }}?')"
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-slate-500">
                                Belum ada data wilayah RT. Klik <strong>Tambah Wilayah RT</strong> untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

