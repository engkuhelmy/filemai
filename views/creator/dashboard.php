<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-white/5 pb-6">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                    CREATOR STUDIO
                </span>
                <span class="text-xs text-slate-400">Portal Pembikin Siri Filemai</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-1">Pengurusan Kandungan Drama</h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="/creator/drama/new" 
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl gradient-brand text-white font-bold text-xs shadow-lg shadow-cyan-500/20 hover:opacity-95 transition-all">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Cipta Siri / Filem Baru
            </a>
        </div>
    </div>

    <!-- Analytics KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <div class="p-5 rounded-2xl bg-slate-900 border border-white/5 shadow-lg flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jumlah Tontonan</span>
                <h3 class="text-2xl font-black text-white"><?= number_format($totalViews) ?></h3>
                <span class="text-[11px] text-emerald-400 flex items-center gap-1">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> +14.2% minggu ini
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                <i data-lucide="eye" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900 border border-white/5 shadow-lg flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Siri / Drama Anda</span>
                <h3 class="text-2xl font-black text-white"><?= $dramaCount ?></h3>
                <span class="text-[11px] text-slate-400">Koleksi karya berdaftar</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                <i data-lucide="film" class="w-6 h-6"></i>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900 border border-white/5 shadow-lg flex items-center justify-between">
            <div class="space-y-1">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jumlah Episod Terbit</span>
                <h3 class="text-2xl font-black text-white"><?= $totalEpisodes ?></h3>
                <span class="text-[11px] text-slate-400">Episod aktif distrim</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- My Dramas List -->
    <div class="rounded-3xl bg-slate-900 border border-white/5 overflow-hidden shadow-xl">
        <div class="p-5 sm:p-6 border-b border-white/5 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white">Senarai Siri Drama Anda</h3>
                <p class="text-xs text-slate-400">Urus metadata tajuk, poster menegak, dan muat naik episod baru</p>
            </div>
        </div>

        <?php if (empty($dramas)): ?>
            <div class="p-12 text-center text-slate-400">
                <i data-lucide="film" class="w-10 h-10 text-slate-600 mx-auto mb-3"></i>
                <p class="font-semibold text-white mb-1">Anda belum menerbitkan sebarang siri drama</p>
                <p class="text-xs mb-4">Mula muat naik siri drama menegak pertama anda sekarang.</p>
                <a href="/creator/drama/new" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl gradient-brand text-white text-xs font-bold">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Cipta Siri Sekarang
                </a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs sm:text-sm">
                    <thead>
                        <tr class="border-b border-white/5 text-slate-400 bg-white/[0.02]">
                            <th class="py-3 px-4">Poster & Tajuk Siri</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Episod</th>
                            <th class="py-3 px-4">Tontonan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-slate-300">
                        <?php foreach ($dramas as $d): ?>
                            <tr class="hover:bg-white/[0.02] transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-16 rounded-lg overflow-hidden bg-slate-800 shrink-0 border border-white/10">
                                            <img src="<?= htmlspecialchars($d['poster_url']) ?>" alt="" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-white text-sm line-clamp-1"><?= htmlspecialchars($d['title']) ?></h4>
                                            <span class="text-[11px] text-slate-500">/drama/<?= htmlspecialchars($d['slug']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-0.5 rounded bg-white/5 border border-white/10 text-cyan-300 text-xs">
                                        <?= htmlspecialchars($d['genre']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 font-semibold text-white">
                                    <?= $d['episode_count'] ?> Episod
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-200">
                                    <?= number_format($d['views_count']) ?>
                                </td>
                                <td class="py-4 px-4">
                                    <?php if ($d['status'] === 'published'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                            <i data-lucide="check-circle" class="w-3 h-3"></i> Bersiaran
                                        </span>
                                    <?php elseif ($d['status'] === 'pending'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Menunggu Kelulusan
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-red-500/10 text-red-400 border border-red-500/30">
                                            <i data-lucide="x-circle" class="w-3 h-3"></i> Ditolak
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="/creator/drama/<?= $d['id'] ?>" 
                                           class="px-3 py-1.5 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-xs font-semibold transition-colors flex items-center gap-1">
                                            <i data-lucide="layers" class="w-3 h-3"></i> Urus Episod
                                        </a>
                                        <a href="/drama/<?= $d['slug'] ?>" target="_blank"
                                           class="p-1.5 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white transition-colors" title="Lihat di Web">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>
