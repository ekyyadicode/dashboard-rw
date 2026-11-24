<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $namaRw = \App\Models\Setting::getValue('nama_rw', 'RW 02');
        $kelurahan = \App\Models\Setting::getValue('kelurahan', 'Kelurahan');
    @endphp
    <title>@yield('title', 'Dashboard') — {{ $namaRw }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex">
        {{-- Mobile Sidebar Backdrop --}}
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
        ></div>

        {{-- Left Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
        >
            {{-- Sidebar Header --}}
            <div class="h-16 px-5 flex items-center justify-between border-b border-slate-200">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-lg bg-teal-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-slate-900 text-sm truncate">{{ $namaRw }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ $kelurahan }}</div>
                    </div>
                </a>
                <button
                    type="button"
                    @click="sidebarOpen = false"
                    class="lg:hidden p-1.5 rounded-lg text-slate-500 hover:bg-slate-100"
                    aria-label="Tutup menu"
                >
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            {{-- Sidebar Navigation --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
                {{-- Menu Utama --}}
                <div>
                    <ul class="space-y-1">
                        <li>
                            <a
                                href="{{ route('dashboard') }}"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                            >
                                <i data-lucide="layout-dashboard" class="w-4 h-4 shrink-0"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Administrasi RW (Super Admin Only) --}}
                @if(auth()->user()->isSuperAdmin())
                    <div>
                        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            Administrasi RW
                        </p>
                        <ul class="space-y-1">
                            <li>
                                <a
                                    href="{{ route('admin.rts.index') }}"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.rts.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                                >
                                    <i data-lucide="map-pin" class="w-4 h-4 shrink-0"></i>
                                    <span>Data Wilayah RT</span>
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('admin.users.index') }}"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                                >
                                    <i data-lucide="shield-check" class="w-4 h-4 shrink-0"></i>
                                    <span>Akun Pengurus</span>
                                </a>
                            </li>
                            <li>
                                <a
                                    href="{{ route('admin.settings.index') }}"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('admin.settings.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                                >
                                    <i data-lucide="settings" class="w-4 h-4 shrink-0"></i>
                                    <span>Pengaturan RW</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                @endif

                {{-- Operasional RT --}}
                @php
                    $user = auth()->user();
                    $hasAnyOperationalPermission = $user->hasPermission('warga')
                        || $user->hasPermission('iuran')
                        || $user->hasPermission('bank_sampah')
                        || $user->hasPermission('posyandu');
                @endphp

                @if($hasAnyOperationalPermission)
                    <div>
                        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            Operasional RT
                        </p>
                        <ul class="space-y-1">
                            @if($user->hasPermission('warga'))
                                <li>
                                    <a
                                        href="{{ route('warga.index') }}"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('warga.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                                    >
                                        <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                                        <span>Data Warga &amp; KK</span>
                                    </a>
                                </li>
                            @endif

                            @if($user->hasPermission('iuran'))
                                <li>
                                    <a
                                        href="{{ route('iuran.index') }}"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('iuran.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                                    >
                                        <i data-lucide="wallet" class="w-4 h-4 shrink-0"></i>
                                        <span>Iuran Warga</span>
                                    </a>
                                </li>
                            @endif

                            @if($user->hasPermission('bank_sampah'))
                                <li>
                                    <a
                                        href="{{ route('bank-sampah.index') }}"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('bank-sampah.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                                    >
                                        <i data-lucide="recycle" class="w-4 h-4 shrink-0"></i>
                                        <span>Bank Sampah</span>
                                    </a>
                                </li>
                            @endif

                            @if($user->hasPermission('posyandu'))
                                <li>
                                    <a
                                        href="{{ route('posyandu.index') }}"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('posyandu.*') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}"
                                    >
                                        <i data-lucide="baby" class="w-4 h-4 shrink-0"></i>
                                        <span>Posyandu Balita</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif
            </nav>

            {{-- Sidebar Footer --}}
            <div class="p-4 border-t border-slate-200 text-xs text-slate-400">
                Sistem Informasi {{ $namaRw }}
            </div>
        </aside>

        {{-- Main Content Wrapper --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Top Navbar --}}
            <header class="h-16 bg-white border-b border-slate-200 px-4 sm:px-6 flex items-center justify-between sticky top-0 z-20">
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        @click="sidebarOpen = true"
                        class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100"
                        aria-label="Buka menu"
                    >
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <h1 class="text-base sm:text-lg font-semibold text-slate-900 truncate">
                        @yield('page-title', 'Dashboard')
                    </h1>
                </div>

                <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                    <div class="hidden sm:flex flex-col items-end">
                        <span class="text-sm font-medium text-slate-800 leading-tight">{{ auth()->user()->name }}</span>
                        @if(auth()->user()->isSuperAdmin())
                            <span class="mt-0.5 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-teal-50 text-teal-700 border border-teal-200">
                                Super Admin RW
                            </span>
                        @else
                            <span class="mt-0.5 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Pengurus RT {{ auth()->user()->rt?->nomor_rt }}
                            </span>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs sm:text-sm font-medium text-slate-600 hover:text-red-600 hover:bg-red-50 border border-slate-200 transition"
                        >
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            {{-- Page Body --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                {{-- Mobile User Badge --}}
                <div class="sm:hidden mb-4 flex items-center justify-between bg-white border border-slate-200 rounded-lg px-3.5 py-2.5">
                    <span class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</span>
                    @if(auth()->user()->isSuperAdmin())
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-teal-50 text-teal-700 border border-teal-200">
                            Super Admin RW
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Pengurus RT {{ auth()->user()->rt?->nomor_rt }}
                        </span>
                    @endif
                </div>

                {{-- Flash Alert: Success --}}
                @if(session('success'))
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        class="mb-6 flex items-start justify-between gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                    >
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                {{-- Flash Alert: Error --}}
                @if(session('error'))
                    <div
                        x-data="{ show: true }"
                        x-show="show"
                        class="mb-6 flex items-start justify-between gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"
                    >
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 shrink-0"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                        <button type="button" @click="show = false" class="text-red-600 hover:text-red-800">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>

