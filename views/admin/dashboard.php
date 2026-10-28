<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Admin Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-white/5 pb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                    SUPER ADMIN PANEL
                </span>
                <span class="text-xs text-slate-400">Pengurusan Keseluruhan Platform Filemai.my</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-1">Papan Pemuka Pentadbir</h1>
        </div>

        <!-- Admin Sub-navigation links -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="/admin/moderation" class="px-3.5 py-2 rounded-xl bg-purple-500/10 hover:bg-purple-500/20 text-purple-300 border border-purple-500/30 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="check-square" class="w-3.5 h-3.5"></i> Moderasi (<?= $kpis['pending_dramas'] ?>)
            </a>
            <a href="/admin/ads" class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 border border-white/10 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="megaphone" class="w-3.5 h-3.5 text-cyan-400"></i> Urus Iklan
            </a>
            <a href="/admin/subscriptions" class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 border border-white/10 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="receipt" class="w-3.5 h-3.5 text-amber-400"></i> Langganan
            </a>
            <a href="/admin/users" class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 border border-white/10 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                <i data-lucide="users" class="w-3.5 h-3.5 text-emerald-400"></i> Pengguna
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        
        <div class="p-4 rounded-2xl bg-slate-900 border border-white/5">
            <span class="text-[11px] font-semibold text-slate-400 block mb-1">Kutipan Langganan</span>
            <div class="text-xl font-black text-amber-400">RM <?= number_format($kpis['total_revenue'], 2) ?></div>
            <span class="text-[10px] text-emerald-400 font-medium">Model RM5/bln</span>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-white/5">
            <span class="text-[11px] font-semibold text-slate-400 block mb-1">Jumlah Tontonan</span>
            <div class="text-xl font-black text-white"><?= number_format($kpis['total_views']) ?></div>
            <span class="text-[10px] text-cyan-400 font-medium">Penstriman video</span>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-white/5">
            <span class="text-[11px] font-semibold text-slate-400 block mb-1">Menunggu Review</span>
            <div class="text-xl font-black <?= $kpis['pending_dramas'] > 0 ? 'text-amber-400' : 'text-slate-400' ?>">
                <?= $kpis['pending_dramas'] ?>
            </div>
            <span class="text-[10px] text-slate-400">Perlu kelulusan</span>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-white/5">
            <span class="text-[11px] font-semibold text-slate-400 block mb-1">Jumlah Siri Drama</span>
            <div class="text-xl font-black text-white"><?= $kpis['total_dramas'] ?></div>
            <span class="text-[10px] text-slate-400">Koleksi siri</span>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-white/5">
            <span class="text-[11px] font-semibold text-slate-400 block mb-1">Pembikin Filem</span>
            <div class="text-xl font-black text-white"><?= $kpis['total_creators'] ?></div>
            <span class="text-[10px] text-slate-400">Creator akaun</span>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900 border border-white/5">
            <span class="text-[11px] font-semibold text-slate-400 block mb-1">Jumlah Pengguna</span>
            <div class="text-xl font-black text-white"><?= $kpis['total_users'] ?></div>
            <span class="text-[10px] text-slate-400">Berdaftar</span>
        </div>

    </div>

    <!-- Moderation Queue (Urgent Attention) -->
    <div class="rounded-3xl bg-slate-900 border border-white/5 overflow-hidden shadow-xl">
        <div class="p-5 sm:p-6 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Barisan Moderasi Drama (Pending Review)</h3>
                    <p class="text-xs text-slate-400">Luluskan drama sebelum disiarkan kepada penonton awam</p>
                </div>
            </div>
            <a href="/admin/moderation" class="text-xs text-cyan-400 hover:underline font-semibold">Lihat Semua Drama &rarr;</a>
        </div>

        <?php if (empty($pendingList)): ?>
            <div class="p-8 text-center text-slate-400 text-xs">
                <i data-lucide="check-circle" class="w-8 h-8 text-emerald-400 mx-auto mb-2"></i>
                Tiada drama baru yang sedang menunggu semakan. Semua telah dimoderasi!
            </div>
        <?php else: ?>
            <div class="divide-y divide-white/5">
                <?php foreach ($pendingList as $p): ?>
                    <div class="p-4 sm:p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-16 rounded-lg bg-slate-800 overflow-hidden border border-white/10 shrink-0">
                                <img src="<?= htmlspecialchars($p['poster_url']) ?>" alt="" class="w-full h-full object-cover">
                            </div>
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-300">
                                    <?= htmlspecialchars($p['genre']) ?>
                                </span>
                                <h4 class="text-sm font-bold text-white mt-1"><?= htmlspecialchars($p['title']) ?></h4>
                                <p class="text-xs text-slate-400">Oleh: <?= htmlspecialchars($p['creator_name']) ?> • <?= $p['episode_count'] ?> Episod</p>
                            </div>
                        </div>

                        <!-- 1-Click Approval Form -->
                        <div class="flex items-center gap-2">
                            <form action="/admin/drama/<?= $p['id'] ?>/status" method="POST">
                                <input type="hidden" name="status" value="published">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500/20 hover:bg-emerald-500 text-emerald-300 hover:text-black font-bold text-xs transition-colors flex items-center gap-1.5">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    Lulus & Siarkan
                                </button>
                            </form>
                            <form action="/admin/drama/<?= $p['id'] ?>/status" method="POST">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="px-4 py-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 font-bold text-xs transition-colors flex items-center gap-1.5">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Subscriptions Log -->
    <div class="rounded-3xl bg-slate-900 border border-white/5 overflow-hidden shadow-xl">
        <div class="p-5 sm:p-6 border-b border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-white">Transaksi Langganan Terbaharu</h3>
                <p class="text-xs text-slate-400">Log pembayaran Pas VIP RM5 yang berjaya</p>
            </div>
            <a href="/admin/subscriptions" class="text-xs text-cyan-400 hover:underline font-semibold">Semua Rekod &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-white/5 text-slate-400 bg-white/[0.02]">
                        <th class="py-3 px-4">Pengguna</th>
                        <th class="py-3 px-4">No. Rujukan</th>
                        <th class="py-3 px-4">Saluran Bayaran</th>
                        <th class="py-3 px-4">Amaun</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Tarikh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    <?php foreach ($recentSubs as $sub): ?>
                        <tr>
                            <td class="py-3 px-4">
                                <div class="font-bold text-white"><?= htmlspecialchars($sub['user_name']) ?></div>
                                <div class="text-[10px] text-slate-500"><?= htmlspecialchars($sub['user_email']) ?></div>
                            </td>
                            <td class="py-3 px-4 font-mono text-cyan-300"><?= htmlspecialchars($sub['transaction_ref']) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($sub['payment_channel']) ?></td>
                            <td class="py-3 px-4 font-bold text-amber-400">RM <?= number_format($sub['amount'], 2) ?></td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300">
                                    <?= strtoupper($sub['status']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-400"><?= date('d M Y, h:i A', strtotime($sub['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
