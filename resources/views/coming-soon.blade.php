@extends('layouts.app')

@section('title', $moduleName ?? 'Segera Hadir')
@section('page-title', $moduleName ?? 'Segera Hadir')

@section('content')
    <div class="bg-white border border-slate-200 rounded-2xl p-8 sm:p-12 text-center max-w-xl mx-auto mt-6">
        <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center mx-auto mb-4">
            <i data-lucide="{{ $icon ?? 'construction' }}" class="w-7 h-7"></i>
        </div>
        <h2 class="text-xl font-bold text-slate-900">
            Modul {{ $moduleName ?? 'Operasional' }}
        </h2>
        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
            {{ $description ?? 'Halaman ini sedang dalam tahap pengembangan dan akan segera tersedia.' }}
        </p>
        <div class="mt-6">
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition"
            >
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>
@endsection

