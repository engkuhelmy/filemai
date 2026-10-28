<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <!-- Breadcrumb & Nav -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="/creator" class="hover:text-cyan-400 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Studio
        </a>
        <span>/</span>
        <span class="text-white truncate max-w-sm"><?= htmlspecialchars($drama['title']) ?></span>
    </div>

    <!-- Drama Overview Banner -->
    <div class="rounded-3xl bg-slate-900 border border-white/10 p-6 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-16 h-24 rounded-xl overflow-hidden bg-slate-800 shrink-0 border border-white/20">
                <img src="<?= htmlspecialchars($drama['poster_url']) ?>" alt="" class="w-full h-full object-cover">
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white/10 text-cyan-300">
                        <?= htmlspecialchars($drama['genre']) ?>
                    </span>
                    <span class="text-xs text-slate-400"><?= count($episodes) ?> Episod</span>
                    <span class="text-xs text-slate-400">• <?= number_format($drama['views_count']) ?> Views</span>
                </div>
                <h2 class="text-xl font-extrabold text-white"><?= htmlspecialchars($drama['title']) ?></h2>
                <p class="text-xs text-slate-400 line-clamp-1 max-w-lg"><?= htmlspecialchars($drama['synopsis']) ?></p>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="/drama/<?= $drama['slug'] ?>" target="_blank"
               class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-slate-200 text-xs font-semibold border border-white/10 transition-colors flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                Lihat di Web
            </a>
        </div>
    </div>

    <!-- Grid: Add Episode Form (Left) & Existing Episodes List (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Upload Episode Form (5 cols) -->
        <div class="lg:col-span-5 rounded-3xl bg-slate-900 border border-white/10 p-6 shadow-xl space-y-5">
            <div class="border-b border-white/5 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="upload-cloud" class="w-4 h-4 text-cyan-400"></i>
                    Muat Naik Episod Baru
                </h3>
                <p class="text-xs text-slate-400">Tambah episod bersiri mengikut urutan nombor</p>
            </div>

            <form action="/creator/drama/<?= $drama['id'] ?>/episode/store" method="POST" enctype="multipart/form-data" class="space-y-4">
                
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-1 space-y-1">
                        <label class="block text-xs font-bold text-slate-300">No. Ep <span class="text-red-400">*</span></label>
                        <input type="number" name="episode_number" value="<?= count($episodes) + 1 ?>" required min="1"
                               class="w-full bg-slate-950 text-white text-xs px-3 py-2.5 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                    </div>
                    <div class="col-span-2 space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Tajuk Episod <span class="text-red-400">*</span></label>
                        <input type="text" name="title" value="Episod <?= count($episodes) + 1 ?>" required
                               class="w-full bg-slate-950 text-white text-xs px-3 py-2.5 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                    </div>
                </div>

                <!-- Video File Upload or URL -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300">Fail Video (MP4 / WebM / CDN)</label>
                    <input type="file" name="video_file" accept="video/mp4,video/webm" 
                           class="w-full text-xs text-slate-400 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-500/10 file:text-cyan-300 hover:file:bg-cyan-500/20 cursor-pointer bg-slate-950 p-2 rounded-xl border border-white/10">
                    
                    <div class="pt-1">
                        <label class="block text-[11px] text-slate-400 mb-1">Atau Gunakan URL Video CDN / Cloudflare R2 / BunnyCDN:</label>
                        <input type="url" name="video_url" placeholder="https://cdn.example.com/ep1.mp4" 
                               class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 items-center">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Durasi (Saat)</label>
                        <input type="number" name="duration" value="60" min="5" max="3600"
                               class="w-full bg-slate-950 text-white text-xs px-3 py-2.5 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                    </div>
                    
                    <div class="pt-5">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-300">
                            <input type="checkbox" name="is_free_preview" value="1" checked class="rounded text-cyan-500 focus:ring-0">
                            <span>Tonton Percuma</span>
                        </label>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full py-3 rounded-xl gradient-brand text-white font-bold text-xs shadow-lg shadow-cyan-500/20 hover:opacity-95 transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    Simpan & Terbit Episod
                </button>

            </form>
        </div>

        <!-- Episodes List (7 cols) -->
        <div class="lg:col-span-7 rounded-3xl bg-slate-900 border border-white/10 p-6 shadow-xl space-y-4">
            <div class="border-b border-white/5 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-white">Episod Yang Telah Dimuat Naik</h3>
                    <p class="text-xs text-slate-400">Jumlah <?= count($episodes) ?> episod berdaftar</p>
                </div>
            </div>

            <?php if (empty($episodes)): ?>
                <div class="p-8 text-center text-slate-400 text-xs">
                    Belum ada episod. Gunakan borang di sebelah untuk memuat naik episod pertama!
                </div>
            <?php else: ?>
                <div class="space-y-2.5">
                    <?php foreach ($episodes as $ep): ?>
                        <div class="p-3.5 rounded-2xl bg-slate-950/70 border border-white/5 flex items-center justify-between gap-3 hover:border-white/10 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg gradient-brand text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    <?= $ep['episode_number'] ?>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-white truncate"><?= htmlspecialchars($ep['title']) ?></h4>
                                    <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-400">
                                        <span><?= gmdate('i:s', $ep['duration']) ?> min</span>
                                        <span>•</span>
                                        <span><?= number_format($ep['views_count']) ?> tontonan</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <?php if ($ep['is_free_preview']): ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Percuma
                                    </span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        VIP Sahaja
                                    </span>
                                <?php endif; ?>

                                <a href="/watch/<?= $ep['id'] ?>" target="_blank"
                                   class="p-2 rounded-lg bg-white/5 hover:bg-cyan-500 text-slate-300 hover:text-white transition-colors" title="Uji Tonton">
                                    <i data-lucide="play" class="w-3.5 h-3.5 fill-current"></i>
                                </a>

                                <a href="/creator/episode/<?= $ep['id'] ?>/delete" 
                                   onclick="return confirm('Adakah anda pasti mahu memadam episod ini?')"
                                   class="p-2 rounded-lg bg-white/5 hover:bg-red-500/20 text-slate-400 hover:text-red-400 transition-colors" title="Padam Episod">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

    </div>

</div>
