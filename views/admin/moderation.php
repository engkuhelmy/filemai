<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="/admin" class="hover:text-cyan-400 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Admin
        </a>
        <span>/</span>
        <span class="text-white">Moderasi Drama</span>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Pusat Moderasi & Kelulusan Karya</h1>
            <p class="text-xs text-slate-400">Semak dan tentukan status kelulusan siri drama dari content creator.</p>
        </div>
    </div>

    <div class="rounded-3xl bg-slate-900 border border-white/5 overflow-hidden shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-slate-400 bg-white/[0.02]">
                        <th class="py-3 px-4">Poster & Siri</th>
                        <th class="py-3 px-4">Pembikin Filem</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Episod</th>
                        <th class="py-3 px-4">Featured</th>
                        <th class="py-3 px-4">Status Semasa</th>
                        <th class="py-3 px-4 text-right">Tindakan Moderasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5 text-slate-300">
                    <?php foreach ($dramas as $d): ?>
                        <tr class="hover:bg-white/[0.02]">
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-16 rounded-lg overflow-hidden bg-slate-800 shrink-0 border border-white/10">
                                        <img src="<?= htmlspecialchars($d['poster_url']) ?>" alt="" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-white text-sm"><?= htmlspecialchars($d['title']) ?></h4>
                                        <p class="text-[11px] text-slate-500 line-clamp-1 max-w-xs"><?= htmlspecialchars($d['synopsis']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4 font-semibold text-white">
                                <?= htmlspecialchars($d['creator_name']) ?>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2 py-0.5 rounded bg-white/5 text-cyan-300 text-xs">
                                    <?= htmlspecialchars($d['genre']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-4 font-semibold text-white">
                                <?= $d['episode_count'] ?> Ep
                            </td>
                            <td class="py-4 px-4">
                                <form action="/admin/drama/<?= $d['id'] ?>/feature" method="POST">
                                    <button type="submit" 
                                            class="p-1.5 rounded-lg text-xs font-bold transition-colors <?= $d['featured'] ? 'bg-amber-500 text-black' : 'bg-white/5 text-slate-500 hover:text-white' ?>"
                                            title="Toggle Featured di Halaman Utama">
                                        <i data-lucide="star" class="w-4 h-4 <?= $d['featured'] ? 'fill-current' : '' ?>"></i>
                                    </button>
                                </form>
                            </td>
                            <td class="py-4 px-4">
                                <?php if ($d['status'] === 'published'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                        Published
                                    </span>
                                <?php elseif ($d['status'] === 'pending'): ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/30">
                                        Pending Review
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/30">
                                        Rejected
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <?php if ($d['status'] !== 'published'): ?>
                                        <form action="/admin/drama/<?= $d['id'] ?>/status" method="POST">
                                            <input type="hidden" name="status" value="published">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-500/20 hover:bg-emerald-500 text-emerald-300 hover:text-black text-xs font-semibold">
                                                Luluskan
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($d['status'] !== 'rejected'): ?>
                                        <form action="/admin/drama/<?= $d['id'] ?>/status" method="POST">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-semibold">
                                                Tolak
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <a href="/drama/<?= $d['slug'] ?>" target="_blank" class="p-1 rounded bg-white/5 hover:bg-white/10 text-slate-300" title="Pratonton">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
