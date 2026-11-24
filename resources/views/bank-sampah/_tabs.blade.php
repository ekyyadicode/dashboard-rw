<div class="inline-flex rounded-xl bg-slate-100 p-1 self-start">
    <a
        href="{{ route('bank-sampah.index') }}"
        class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('bank-sampah.index') || request()->routeIs('bank-sampah.warga.*') ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
    >
        <i data-lucide="piggy-bank" class="w-4 h-4"></i>
        <span>Saldo Tabungan Warga</span>
    </a>
    <a
        href="{{ route('bank-sampah.riwayat') }}"
        class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition {{ request()->routeIs('bank-sampah.riwayat') ? 'bg-white text-teal-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
    >
        <i data-lucide="history" class="w-4 h-4"></i>
        <span>Riwayat Transaksi</span>
    </a>
</div>

