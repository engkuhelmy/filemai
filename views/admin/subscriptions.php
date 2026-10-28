<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="/admin" class="hover:text-cyan-400 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Admin
        </a>
        <span>/</span>
        <span class="text-white">Rekod Langganan</span>
    </div>

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Log Transaksi Langganan VIP</h1>
            <p class="text-xs text-slate-400">Rekod pembayaran pelan bulanan RM5 dan status pengesahan gerbang pembayaran.</p>
        </div>
        <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center gap-3">
            <i data-lucide="wallet" class="w-5 h-5 text-amber-400"></i>
            <div>
                <span class="text-[10px] text-slate-400 uppercase tracking-wider block font-semibold">Jumlah Terkumpul</span>
                <span class="text-lg font-black text-amber-400">RM <?= number_format($totalRevenue, 2) ?></span>
            </div>
        </div>
    </div>

    <div class="rounded-3xl bg-slate-900 border border-white/5 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-slate-400 bg-white/[0.02]">
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">No. Rujukan</th>
                        <th class="py-3 px-4">Saluran / Gerbang</th>
                        <th class="py-3 px-4">Amaun</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Tarikh Bayaran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    <?php foreach ($subscriptions as $s): ?>
                        <tr class="hover:bg-white/[0.02]">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white"><?= htmlspecialchars($s['user_name']) ?></div>
                                <div class="text-[11px] text-slate-500"><?= htmlspecialchars($s['user_email']) ?></div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-cyan-300 font-semibold"><?= htmlspecialchars($s['transaction_ref']) ?></td>
                            <td class="py-3.5 px-4"><?= htmlspecialchars($s['payment_channel']) ?></td>
                            <td class="py-3.5 px-4 font-bold text-amber-400">RM <?= number_format($s['amount'], 2) ?></td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold <?= $s['status'] === 'success' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/10 text-amber-400' ?>">
                                    <?= strtoupper($s['status']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400"><?= date('d M Y, h:i A', strtotime($s['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
