<?php
use App\Auth;
$isPremium = Auth::isPremium();
?>

<div class="max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12 py-8 space-y-10 pt-24 sm:pt-28">

    <!-- Breadcrumb & Back -->
    <div class="flex items-center gap-2 text-xs text-slate-400">
        <a href="/" class="hover:text-white flex items-center gap-1 transition-colors">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            Kembali
        </a>
        <span>/</span>
        <span class="text-slate-500"><?= htmlspecialchars($drama['genre']) ?></span>
        <span>/</span>
        <span class="text-white truncate max-w-xs font-semibold"><?= htmlspecialchars($drama['title']) ?></span>
    </div>

    <!-- Drama Hero Card (Netflix Backdrop Style) -->
    <div class="relative rounded-3xl overflow-hidden bg-[#141414] border border-white/10 p-6 sm:p-10 shadow-2xl">
        
        <!-- Backdrop Image -->
        <div class="absolute inset-0 z-0">
            <img src="<?= htmlspecialchars($drama['banner_url'] ?? $drama['poster_url']) ?>" alt="" 
                 class="w-full h-full object-cover opacity-25 blur-xl scale-110">
            <div class="absolute inset-0 bg-gradient-to-t md:bg-gradient-to-r from-[#0b0c0f] via-[#0b0c0f]/90 to-[#0b0c0f]/60"></div>
        </div>

        <div class="relative z-10 grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
            
            <!-- Vertical Poster (Left Column) -->
            <div class="md:col-span-4 lg:col-span-3 flex justify-center">
                <div class="w-56 sm:w-64 aspect-vertical rounded-2xl overflow-hidden shadow-2xl border border-white/20 relative group">
                    <img src="<?= htmlspecialchars($drama['poster_url']) ?>" alt="<?= htmlspecialchars($drama['title']) ?>" 
                         class="w-full h-full object-cover">
                    <div class="absolute top-3 left-3">
                        <span class="px-2.5 py-1 rounded text-xs font-black bg-[#E50914] text-white">
                            <?= htmlspecialchars($drama['genre']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Drama Info (Right Column) -->
            <div class="md:col-span-8 lg:col-span-9 space-y-5">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5 mb-2 text-xs font-semibold">
                        <span class="px-2.5 py-0.5 rounded-full bg-[#E50914]/20 text-[#E50914] border border-[#E50914]/40 font-bold">
                            9:16 Vertical Drama
                        </span>
                        <span class="flex items-center gap-1 text-amber-400 font-extrabold">
                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i> 7.5
                        </span>
                        <span class="text-slate-400">• <?= count($episodes) ?> Episod</span>
                        <span class="text-slate-400">• <?= number_format($drama['views_count']) ?> tontonan</span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight uppercase font-['Bebas_Neue','Inter',sans-serif]">
                        <?= htmlspecialchars($drama['title']) ?>
                    </h1>
                </div>

                <!-- Creator Card -->
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-white/5 border border-white/5 max-w-sm">
                    <div class="w-9 h-9 rounded-full bg-[#E50914] flex items-center justify-center font-bold text-white text-xs">
                        <?= substr($drama['creator_name'], 0, 1) ?>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[11px] text-slate-400">Diterbitkan oleh:</span>
                            <i data-lucide="badge-check" class="w-3.5 h-3.5 text-cyan-400"></i>
                        </div>
                        <span class="text-xs font-bold text-white"><?= htmlspecialchars($drama['creator_name']) ?></span>
                    </div>
                </div>

                <!-- Synopsis -->
                <div>
                    <h3 class="text-xs uppercase tracking-wider font-bold text-slate-400 mb-1.5">Sinopsis Siri:</h3>
                    <p class="text-sm text-slate-300 leading-relaxed max-w-3xl">
                        <?= nl2br(htmlspecialchars($drama['synopsis'])) ?>
                    </p>
                </div>

                <!-- Action Buttons: Watch & VIP -->
                <?php if (!empty($episodes)): ?>
                    <div class="pt-3 flex flex-wrap items-center gap-3">
                        <a href="/watch/<?= $episodes[0]['id'] ?>" 
                           class="inline-flex items-center gap-2 px-8 py-3.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white font-extrabold text-sm shadow-xl shadow-red-950/80 hover:scale-105 transition-all">
                            <i data-lucide="play" class="w-4 h-4 fill-white"></i>
                            Tonton Episod 1
                        </a>

                        <?php if (!$isPremium): ?>
                            <a href="/subscribe" 
                               class="inline-flex items-center gap-2 px-6 py-3.5 rounded-full bg-white/10 hover:bg-white/20 text-amber-400 font-bold text-sm border border-amber-500/30 transition-all">
                                <i data-lucide="crown" class="w-4 h-4 fill-amber-400"></i>
                                Langgan VIP Tanpa Iklan (RM5)
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>

    <!-- Episodes Grid -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
            <h2 class="text-lg sm:text-xl font-bold text-white flex items-center gap-2">
                <span>Senarai Episod</span>
                <span class="text-xs text-slate-400 font-normal">(<?= count($episodes) ?> Episod)</span>
            </h2>

            <div class="text-xs text-slate-400 flex items-center gap-3">
                <span class="flex items-center gap-1 text-emerald-400 font-semibold">
                    <i data-lucide="unlock" class="w-3.5 h-3.5"></i> Percuma
                </span>
                <span class="flex items-center gap-1 text-amber-400 font-semibold">
                    <i data-lucide="crown" class="w-3.5 h-3.5"></i> VIP / Iklan 15s
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($episodes as $ep): ?>
                <a href="/watch/<?= $ep['id'] ?>" 
                   class="group p-4 rounded-2xl bg-[#181818] hover:bg-[#222222] border border-white/[0.06] hover:border-[#E50914]/50 transition-all flex items-center justify-between gap-4">
                    
                    <div class="flex items-center gap-3.5 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-white/10 group-hover:bg-[#E50914] group-hover:text-white flex items-center justify-center font-black text-sm text-white shrink-0 transition-colors">
                            <?= $ep['episode_number'] ?>
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-white group-hover:text-[#E50914] transition-colors truncate">
                                <?= htmlspecialchars($ep['title']) ?>
                            </h4>
                            <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-400">
                                <span><?= gmdate('i:s', $ep['duration']) ?> min</span>
                                <span>•</span>
                                <span><?= number_format($ep['views_count']) ?> views</span>
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        <?php if ($ep['is_free_preview']): ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Percuma
                            </span>
                        <?php elseif ($isPremium): ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center gap-1">
                                <i data-lucide="check" class="w-2.5 h-2.5"></i> VIP
                            </span>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 flex items-center gap-1">
                                <i data-lucide="crown" class="w-2.5 h-2.5 fill-amber-400"></i> Iklan
                            </span>
                        <?php endif; ?>

                        <div class="w-8 h-8 rounded-full bg-white/5 group-hover:bg-[#E50914] text-slate-400 group-hover:text-white flex items-center justify-center transition-colors">
                            <i data-lucide="play" class="w-3.5 h-3.5 fill-current ml-0.5"></i>
                        </div>
                    </div>

                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Related Dramas -->
    <?php if (!empty($related)): ?>
        <div class="space-y-4 pt-6 border-t border-white/[0.08]">
            <h3 class="text-lg font-bold text-white">Siri Serupa Yang Menarik</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <?php foreach ($related as $rel): ?>
                    <a href="/drama/<?= $rel['slug'] ?>" class="group block">
                        <div class="aspect-vertical rounded-xl overflow-hidden bg-[#181818] border border-white/[0.08] netflix-card relative">
                            <img src="<?= htmlspecialchars($rel['poster_url']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>" 
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-x-0 bottom-0 p-3 bg-gradient-to-t from-black via-black/80 to-transparent">
                                <h5 class="text-xs font-bold text-white group-hover:text-[#E50914] truncate"><?= htmlspecialchars($rel['title']) ?></h5>
                                <span class="text-[10px] text-slate-400"><?= number_format($rel['views_count']) ?> views</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>
