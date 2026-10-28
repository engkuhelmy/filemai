<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="/admin" class="hover:text-cyan-400 flex items-center gap-1">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali ke Admin
        </a>
        <span>/</span>
        <span class="text-white">Pengurusan Iklan</span>
    </div>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-white">Iklan Interstitial Platform (In-House Ads)</h1>
            <p class="text-xs text-slate-400">Urus iklan video pendek (10-20 saat) yang dipaparkan kepada penonton pelan percuma.</p>
        </div>
    </div>

    <!-- Grid: Add New Ad (Left) & Active Ads (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Add Ad Form -->
        <div class="lg:col-span-5 rounded-3xl bg-slate-900 border border-white/10 p-6 shadow-xl space-y-4">
            <div class="border-b border-white/5 pb-3">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-cyan-400"></i>
                    Daftar Iklan Baharu
                </h3>
            </div>

            <form action="/admin/ad/store" method="POST" enctype="multipart/form-data" class="space-y-4">
                
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-300">Kempen / Tajuk Iklan <span class="text-red-400">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: MiQa Cloud Web Hosting..." 
                           class="w-full bg-slate-950 text-white text-xs px-3 py-2.5 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-300">Nama Penaja (Sponsor Name)</label>
                    <input type="text" name="sponsor_name" placeholder="Contoh: MiQaOS Cloud" 
                           class="w-full bg-slate-950 text-white text-xs px-3 py-2.5 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                </div>

                <!-- Video File or URL -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-300">Fail Video Iklan (MP4 / WebM)</label>
                    <input type="file" name="video_file" accept="video/mp4,video/webm" 
                           class="w-full text-xs text-slate-400 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-cyan-500/10 file:text-cyan-300 hover:file:bg-cyan-500/20 cursor-pointer bg-slate-950 p-2 rounded-xl border border-white/10">
                    
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Atau URL Video Iklan:</label>
                        <input type="url" name="video_url" placeholder="https://cdn.example.com/ad.mp4" 
                               class="w-full bg-slate-950 text-white text-xs px-3 py-2 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-300">Tempoh Countdown (Saat)</label>
                        <input type="number" name="duration_seconds" value="15" min="5" max="60" 
                               class="w-full bg-slate-950 text-white text-xs px-3 py-2.5 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                    </div>
                    <div class="pt-5">
                        <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-300">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded text-cyan-500 focus:ring-0">
                            <span>Status Aktif</span>
                        </label>
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-slate-300">Pautan Sasaran (Click URL)</label>
                    <input type="url" name="click_url" placeholder="https://..." 
                           class="w-full bg-slate-950 text-white text-xs px-3 py-2.5 rounded-xl border border-white/10 focus:outline-none focus:border-cyan-500/50">
                </div>

                <button type="submit" 
                        class="w-full py-3 rounded-xl gradient-brand text-white font-bold text-xs shadow-lg shadow-cyan-500/20 hover:opacity-95 transition-all flex items-center justify-center gap-1.5">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Simpan & Aktifkan Iklan
                </button>

            </form>
        </div>

        <!-- Ads Table -->
        <div class="lg:col-span-7 rounded-3xl bg-slate-900 border border-white/5 p-6 shadow-xl space-y-4">
            <div class="border-b border-white/5 pb-3">
                <h3 class="text-base font-bold text-white">Senarai Iklan Dalam Giliran</h3>
                <p class="text-xs text-slate-400">Prestasi paparan dan klik iklan penaja</p>
            </div>

            <div class="space-y-3">
                <?php foreach ($ads as $ad): ?>
                    <div class="p-4 rounded-2xl bg-slate-950/70 border border-white/5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/30">
                                    <?= $ad['duration_seconds'] ?>s Countdown
                                </span>
                                <span class="text-xs text-slate-400 font-semibold"><?= htmlspecialchars($ad['sponsor_name'] ?? 'In-House') ?></span>
                            </div>
                            <h4 class="text-sm font-bold text-white"><?= htmlspecialchars($ad['title']) ?></h4>
                            <div class="flex items-center gap-3 text-xs text-slate-400">
                                <span>Impressions: <strong class="text-cyan-400"><?= number_format($ad['views_count']) ?></strong></span>
                                <span>•</span>
                                <span>Klik: <strong class="text-emerald-400"><?= number_format($ad['clicks_count']) ?></strong></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <!-- Toggle Active -->
                            <form action="/admin/ad/<?= $ad['id'] ?>/toggle" method="POST">
                                <button type="submit" 
                                        class="px-3 py-1 rounded-full text-xs font-bold transition-colors <?= $ad['is_active'] ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-500' ?>">
                                    <?= $ad['is_active'] ? 'Aktif' : 'Tidak Aktif' ?>
                                </button>
                            </form>

                            <!-- Delete -->
                            <a href="/admin/ad/<?= $ad['id'] ?>/delete" 
                               onclick="return confirm('Padam iklan ini?')"
                               class="p-1.5 rounded-lg bg-white/5 hover:bg-red-500/20 text-slate-400 hover:text-red-400 transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>
