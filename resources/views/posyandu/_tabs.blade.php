<div class="inline-flex rounded-xl bg-slate-100 p-1 self-start">
    <a
        href="{{ route('posyandu.index') }}"
        class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('posyandu.index') || request()->routeIs('posyandu.balita.*') ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
    >
        <i data-lucide="baby" class="w-4 h-4"></i>
        <span>Daftar Balita &amp; Input Timbang</span>
    </a>
    <a
        href="{{ route('posyandu.riwayat') }}"
        class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('posyandu.riwayat') ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
    >
        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
        <span>Riwayat Pemeriksaan Bulanan</span>
    </a>
</div>

