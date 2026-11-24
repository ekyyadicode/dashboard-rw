<div class="mb-6 border-b border-slate-200 pb-4 overflow-x-auto">
    <div class="inline-flex rounded-xl bg-slate-100 p-1 min-w-max">
        <a
            href="{{ route('iuran.index') }}"
            class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('iuran.index') ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
        >
            <i data-lucide="clipboard-check" class="w-4 h-4"></i>
            <span>Status Iuran per KK</span>
        </a>
        <a
            href="{{ route('iuran.matriks') }}"
            class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('iuran.matriks') ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
        >
            <i data-lucide="grid-3x3" class="w-4 h-4"></i>
            <span>Matriks Tahunan (Jan–Des)</span>
        </a>
        <a
            href="{{ route('iuran.riwayat') }}"
            class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('iuran.riwayat') ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
        >
            <i data-lucide="history" class="w-4 h-4"></i>
            <span>Riwayat Transaksi</span>
        </a>
        <a
            href="{{ route('iuran.jenis.index') }}"
            class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('iuran.jenis.*') ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
        >
            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
            <span>Pengaturan Jenis Iuran</span>
        </a>
    </div>
</div>

