<div class="max-w-md mx-auto px-4 py-16 text-center space-y-6">

    <div class="w-20 h-20 rounded-full bg-emerald-500/20 border-2 border-emerald-500/40 text-emerald-400 flex items-center justify-center mx-auto shadow-2xl shadow-emerald-500/20 animate-bounce">
        <i data-lucide="check" class="w-10 h-10 stroke-[3]"></i>
    </div>

    <div class="space-y-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
            <i data-lucide="crown" class="w-3.5 h-3.5 fill-amber-400"></i>
            STATUS VIP AKTIF
        </span>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Tahniah, <?= htmlspecialchars($user['name']) ?>!</h1>
        <p class="text-slate-400 text-sm">
            Langganan Pas VIP Filemai anda berjaya diaktifkan untuk tempoh 30 hari bermula sekarang.
        </p>
    </div>

    <div class="rounded-2xl bg-slate-900 border border-white/10 p-5 text-left text-xs space-y-3">
        <div class="flex items-center justify-between text-slate-400">
            <span>No. Rujukan Transaksi:</span>
            <span class="font-mono text-white font-bold"><?= htmlspecialchars($ref) ?></span>
        </div>
        <div class="flex items-center justify-between text-slate-400">
            <span>Jumlah Pembayaran:</span>
            <span class="text-emerald-400 font-bold">RM 5.00 (Berjaya)</span>
        </div>
        <div class="flex items-center justify-between text-slate-400">
            <span>Tarikh Luput Pas VIP:</span>
            <span class="text-white font-medium"><?= date('d F Y', strtotime('+30 days')) ?></span>
        </div>
    </div>

    <div class="pt-2">
        <a href="/" 
           class="w-full py-4 rounded-xl gradient-brand text-white font-extrabold text-sm shadow-xl shadow-cyan-500/25 hover:opacity-95 transition-all flex items-center justify-center gap-2">
            <i data-lucide="play" class="w-4 h-4 fill-white"></i>
            Mula Tonton Tanpa Iklan Sekarang
        </a>
    </div>

</div>
