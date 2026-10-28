<?php
$featured = $heroDramas[0] ?? [
    'id' => 2,
    'title' => 'Dendam Pewaris Yang Dibuang',
    'slug' => 'dendam-pewaris-yang-dibuang',
    'synopsis' => 'Dibuang oleh keluarga konglomeratnya 10 tahun lalu dan dihina sebagai pengemis jalanan. Kini, sang naga tidur bangkit kembali sebagai penguasa kewangan global terkuat untuk menuntut setiap titis air mata dan maruah yang telah dirampas!',
    'poster_url' => '/uploads/posters/dendam-pewaris.jpg',
    'banner_url' => '/uploads/posters/dendam-billboard.jpg',
    'trailer_url' => '/uploads/videos/sample_reel.mp4',
    'genre' => 'Action',
    'episode_count' => 3,
    'first_episode_id' => 5,
    'views_count' => 28922,
    'rating' => '8.9',
    'hook' => '🔥 #1 Paling Trending di Malaysia',
];
?>

<div class="space-y-10 pb-16" x-data="netflixHome(<?= htmlspecialchars(json_encode($heroDramas), ENT_QUOTES, 'UTF-8') ?>)">

    <!-- ======================================================== -->
    <!-- NETFLIX HERO BILLBOARD SECTION WITH 5S TRAILER TEASER -->
    <!-- ======================================================== -->
    <div class="relative w-full h-[75vh] sm:h-[82vh] lg:h-[88vh] min-h-[520px] max-h-[900px] overflow-hidden select-none">
        
        <!-- Background Media Layer: 5-Second Video Trailer Teaser + High-Res Billboard Poster Fallback -->
        <div class="absolute inset-0 z-0">
            <!-- 5s Auto-play Muted Video Trailer Teaser -->
            <video x-ref="trailerVideo"
                   autoplay muted playsinline
                   :src="currentHero?.trailer_url || '/uploads/videos/sample_reel.mp4'"
                   class="w-full h-full object-cover object-center absolute inset-0 transition-opacity duration-1000"
                   :class="trailerActive ? 'opacity-85' : 'opacity-0'">
            </video>

            <!-- High-Res Billboard Poster (Crossfades seamlessly when teaser completes) -->
            <img :src="currentHero?.banner_url || '<?= htmlspecialchars($featured['banner_url']) ?>'" 
                 :alt="currentHero?.title || '<?= htmlspecialchars($featured['title']) ?>'" 
                 class="w-full h-full object-cover object-center sm:object-top transition-opacity duration-1000"
                 :class="trailerActive ? 'opacity-30' : 'opacity-100'">
            
            <!-- Netflix Cinematic Vignette Gradients -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#0b0c0f] via-[#0b0c0f]/85 sm:via-[#0b0c0f]/60 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 h-52 bg-gradient-to-t from-[#0b0c0f] via-[#0b0c0f]/80 to-transparent"></div>
        </div>

        <!-- 5-Second Trailer Teaser Pill (Top Right) -->
        <div class="absolute top-24 right-4 sm:right-12 z-20">
            <div x-show="trailerActive" x-transition.opacity.duration.300ms class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-black/60 backdrop-blur-md border border-white/15 text-[11px] font-bold text-white shadow-xl">
                <span class="w-2 h-2 rounded-full bg-[#E50914] animate-ping"></span>
                <span>Trailer Teaser (<span x-text="trailerCountdown">5</span>s)</span>
                <button @click="toggleMute()" class="p-1 hover:text-red-400 text-slate-300 transition-colors">
                    <i :data-lucide="isMuted ? 'volume-x' : 'volume-2'" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>

        <!-- Hero Content Overlay (SSR Pre-rendered with Dynamic Alpine.js updates) -->
        <div class="relative z-10 max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12 h-full flex flex-col justify-center pt-16 sm:pt-20">
            
            <div class="max-w-2xl space-y-4 sm:space-y-5">
                
                <!-- Hook Badge & Duration Row -->
                <div class="space-y-1.5 text-xs sm:text-sm text-slate-300 font-medium">
                    <div class="flex items-center gap-2 text-slate-400">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#E50914]/20 border border-[#E50914]/40 text-[#E50914] font-black text-[11px] uppercase tracking-wider"
                              x-text="currentHero?.hook || '<?= htmlspecialchars($featured['hook'] ?? '🔥 #1 Paling Trending di Malaysia') ?>'">
                            <?= htmlspecialchars($featured['hook'] ?? '🔥 #1 Paling Trending di Malaysia') ?>
                        </span>
                        <span>•</span>
                        <span>Duration: <strong class="text-white" x-text="(currentHero?.episode_count || <?= (int)($featured['episode_count'] ?? 3) ?>) + ' Episod'"><?= (int)($featured['episode_count'] ?? 3) ?> Episod</strong></span>
                    </div>

                    <div class="flex items-center gap-2.5 text-xs text-slate-300">
                        <span class="flex items-center gap-1 text-amber-400 font-extrabold">
                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                            <span x-text="currentHero?.rating || '<?= htmlspecialchars($featured['rating'] ?? '8.9') ?>'"><?= htmlspecialchars($featured['rating'] ?? '8.9') ?></span>
                        </span>
                        <span class="text-slate-500">•</span>
                        <span class="text-slate-300">Season 1 • Full HD •</span>
                        <span class="text-slate-200 font-bold" x-text="currentHero?.genre || '<?= htmlspecialchars($featured['genre'] ?? 'Action') ?>'"><?= htmlspecialchars($featured['genre'] ?? 'Action') ?></span>
                    </div>
                </div>

                <!-- Big Title (Netflix bold uppercase font) -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight uppercase font-['Bebas_Neue','Inter',sans-serif]"
                    x-text="currentHero?.title">
                    <?= htmlspecialchars($featured['title'] ?? 'Dendam Pewaris Yang Dibuang') ?>
                </h1>

                <!-- Synopsis Text (Emotionally Hooking) -->
                <p class="text-xs sm:text-sm text-slate-200 leading-relaxed line-clamp-3 max-w-xl font-medium"
                   x-text="currentHero?.synopsis">
                    <?= htmlspecialchars($featured['synopsis'] ?? 'Dibuang oleh keluarga konglomeratnya 10 tahun lalu dan dihina sebagai pengemis jalanan. Kini, sang naga tidur bangkit kembali sebagai penguasa kewangan global terkuat untuk menuntut setiap titis air mata dan maruah yang telah dirampas!') ?>
                </p>

                <!-- Netflix Red Watch & Info Buttons -->
                <div class="flex items-center gap-3 pt-2 sm:pt-4">
                    
                    <!-- Red Watch Button (▶ WATCH NOW) -->
                    <a :href="'/watch/' + (currentHero?.first_episode_id || <?= (int)($featured['first_episode_id'] ?? 1) ?>)" 
                       href="/watch/<?= (int)($featured['first_episode_id'] ?? 1) ?>"
                       class="px-6 sm:px-8 py-3 sm:py-3.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white font-extrabold text-xs sm:text-sm flex items-center gap-2.5 shadow-xl shadow-red-950/60 hover:scale-105 transition-all">
                        <i data-lucide="play" class="w-4 h-4 fill-white"></i>
                        <span>WATCH NOW</span>
                    </a>

                    <!-- Translucent Details Button (+ INFO PENUH) -->
                    <a :href="'/drama/' + (currentHero?.slug || '<?= htmlspecialchars($featured['slug'] ?? '') ?>')" 
                       href="/drama/<?= htmlspecialchars($featured['slug'] ?? 'dendam-pewaris-yang-dibuang') ?>"
                       class="px-5 sm:px-6 py-3 sm:py-3.5 rounded-full bg-white/15 hover:bg-white/25 backdrop-blur-md text-white font-bold text-xs sm:text-sm border border-white/20 flex items-center gap-2 transition-all">
                        <i data-lucide="info" class="w-4 h-4"></i>
                        <span>INFO PENUH</span>
                    </a>

                    <!-- Replay Teaser Button -->
                    <button @click="replayTeaser()" 
                            title="Main Semula Teaser"
                            class="p-3 sm:p-3.5 rounded-full bg-white/10 hover:bg-white/20 backdrop-blur-md text-slate-300 hover:text-white border border-white/10 transition-colors">
                        <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    </button>

                </div>

            </div>

            <!-- Bottom Center Carousel Indicators -->
            <div class="absolute bottom-6 sm:bottom-10 left-1/2 transform -translate-x-1/2 z-20 flex items-center gap-2">
                <template x-for="(hero, index) in heroList" :key="'indicator-' + hero.id">
                    <button @click="switchHero(index)" 
                            class="h-1 rounded-full transition-all duration-300"
                            :class="currentIndex === index ? 'w-8 bg-[#E50914]' : 'w-3 bg-white/30 hover:bg-white/60'">
                    </button>
                </template>
            </div>

        </div>

    </div>

    <!-- ======================================================== -->
    <!-- SUB-NAVIGATION: TRENDS NOW, POPULAR, PREMIERES -->
    <!-- ======================================================== -->
    <section class="max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12 space-y-4 -mt-6 sm:-mt-8 relative z-20">
        
        <!-- Tabs Row (Trends Now, Popular, Premieres, Recently Added) -->
        <div class="flex items-center space-x-6 sm:space-x-8 text-xs sm:text-sm font-bold border-b border-white/[0.08] pb-3 overflow-x-auto no-scrollbar">
            
            <a href="/?tab=trends" 
               class="flex items-center gap-2 whitespace-nowrap transition-colors relative py-1 <?= ($activeTab === 'trends' || empty($activeTab)) ? 'text-white' : 'text-slate-400 hover:text-white' ?>">
                <i data-lucide="trending-up" class="w-4 h-4 text-[#E50914]"></i>
                <span>Trends Now</span>
                <?php if ($activeTab === 'trends' || empty($activeTab)): ?>
                    <span class="absolute -bottom-3 left-0 right-0 h-0.5 bg-[#E50914]"></span>
                <?php endif; ?>
            </a>

            <a href="/?tab=popular" 
               class="flex items-center gap-2 whitespace-nowrap transition-colors relative py-1 <?= ($activeTab === 'popular') ? 'text-white' : 'text-slate-400 hover:text-white' ?>">
                <i data-lucide="flame" class="w-4 h-4 text-amber-500"></i>
                <span>Popular</span>
                <?php if ($activeTab === 'popular'): ?>
                    <span class="absolute -bottom-3 left-0 right-0 h-0.5 bg-[#E50914]"></span>
                <?php endif; ?>
            </a>

            <a href="/?tab=premieres" 
               class="flex items-center gap-2 whitespace-nowrap transition-colors relative py-1 <?= ($activeTab === 'premieres') ? 'text-white' : 'text-slate-400 hover:text-white' ?>">
                <i data-lucide="star" class="w-4 h-4 text-yellow-400"></i>
                <span>Premieres</span>
                <?php if ($activeTab === 'premieres'): ?>
                    <span class="absolute -bottom-3 left-0 right-0 h-0.5 bg-[#E50914]"></span>
                <?php endif; ?>
            </a>

            <a href="/subscribe" 
               class="flex items-center gap-2 whitespace-nowrap text-amber-400 hover:text-amber-300 py-1">
                <i data-lucide="crown" class="w-4 h-4 fill-amber-400"></i>
                <span>VIP Ad-Free (RM5)</span>
            </a>

        </div>

        <!-- Horizontal Category Pills Bar with Left & Right Arrows -->
        <div class="flex items-center gap-2 pt-2" x-data="{
            scrollLeft() { $refs.pillsContainer.scrollBy({ left: -250, behavior: 'smooth' }) },
            scrollRight() { $refs.pillsContainer.scrollBy({ left: 250, behavior: 'smooth' }) }
        }">
            
            <!-- Left Arrow Button -->
            <button @click="scrollLeft" 
                    class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300 hover:text-white shrink-0 transition-colors">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>

            <!-- Pills Container -->
            <div x-ref="pillsContainer" class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1 flex-1">
                <?php foreach ($genres as $g): ?>
                    <a href="<?= $g === 'Semua' ? '/' : '/?genre=' . urlencode($g) ?>" 
                       class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all <?= ($selectedGenre === $g) ? 'bg-[#E50914] text-white shadow-lg shadow-red-900/40' : 'bg-[#181818] text-slate-300 hover:text-white hover:bg-[#252525] border border-white/[0.06]' ?>">
                        <?= htmlspecialchars($g) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Right Arrow Button -->
            <button @click="scrollRight" 
                    class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300 hover:text-white shrink-0 transition-colors">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>

        </div>

    </section>

    <!-- ======================================================== -->
    <!-- NETFLIX POSTER ROW CAROUSEL 1: TRENDS NOW -->
    <!-- ======================================================== -->
    <section class="max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12 space-y-3" x-data="{
        scrollLeft() { $refs.row1.scrollBy({ left: -400, behavior: 'smooth' }) },
        scrollRight() { $refs.row1.scrollBy({ left: 400, behavior: 'smooth' }) }
    }">
        <div class="flex items-center justify-between">
            <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Trends Now</span>
                <span class="text-xs font-semibold text-[#E50914] uppercase">Trending #1</span>
            </h2>

            <div class="hidden sm:flex items-center gap-1.5">
                <button @click="scrollLeft" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </button>
                <button @click="scrollRight" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Horizontal Scroll Row -->
        <div x-ref="row1" class="carousel-row flex items-center gap-3 sm:gap-4 overflow-x-auto pb-4 pt-1">
            <?php foreach ($trendsNow as $drama): ?>
                <div class="w-36 sm:w-44 md:w-48 lg:w-52 shrink-0">
                    <a href="/drama/<?= $drama['slug'] ?>" class="group block">
                        <div class="relative aspect-vertical rounded-xl overflow-hidden bg-[#181818] border border-white/[0.08] netflix-card shadow-lg">
                            <img src="<?= htmlspecialchars($drama['poster_url']) ?>" alt="<?= htmlspecialchars($drama['title']) ?>" 
                                 class="w-full h-full object-cover">

                            <!-- Top Badges -->
                            <div class="absolute top-2 left-2 flex items-center gap-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-[#E50914] text-white">
                                    <?= htmlspecialchars($drama['genre']) ?>
                                </span>
                            </div>

                            <div class="absolute top-2 right-2">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-black/70 backdrop-blur-md text-white">
                                    <?= $drama['episode_count'] ?> Ep
                                </span>
                            </div>

                            <!-- Bottom Vignette with Title & Info -->
                            <div class="absolute inset-x-0 bottom-0 p-3 pt-12 bg-gradient-to-t from-black via-black/80 to-transparent flex flex-col justify-end">
                                <h3 class="text-xs sm:text-sm font-bold text-white group-hover:text-[#E50914] transition-colors truncate">
                                    <?= htmlspecialchars($drama['title']) ?>
                                </h3>
                                <div class="flex items-center justify-between text-[10px] text-slate-400 mt-0.5">
                                    <span class="flex items-center gap-1 text-amber-400 font-bold">
                                        <i data-lucide="star" class="w-2.5 h-2.5 fill-amber-400"></i> 7.5
                                    </span>
                                    <span><?= number_format($drama['views_count']) ?> views</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- NETFLIX POSTER ROW CAROUSEL 2: POPULAR ON FILEMAI -->
    <!-- ======================================================== -->
    <section class="max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12 space-y-3" x-data="{
        scrollLeft() { $refs.row2.scrollBy({ left: -400, behavior: 'smooth' }) },
        scrollRight() { $refs.row2.scrollBy({ left: 400, behavior: 'smooth' }) }
    }">
        <div class="flex items-center justify-between">
            <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Popular on Filemai</span>
            </h2>

            <div class="hidden sm:flex items-center gap-1.5">
                <button @click="scrollLeft" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </button>
                <button @click="scrollRight" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <div x-ref="row2" class="carousel-row flex items-center gap-3 sm:gap-4 overflow-x-auto pb-4 pt-1">
            <?php foreach ($popular as $drama): ?>
                <div class="w-36 sm:w-44 md:w-48 lg:w-52 shrink-0">
                    <a href="/drama/<?= $drama['slug'] ?>" class="group block">
                        <div class="relative aspect-vertical rounded-xl overflow-hidden bg-[#181818] border border-white/[0.08] netflix-card shadow-lg">
                            <img src="<?= htmlspecialchars($drama['poster_url']) ?>" alt="<?= htmlspecialchars($drama['title']) ?>" 
                                 class="w-full h-full object-cover">

                            <div class="absolute top-2 left-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-black/60 backdrop-blur-md text-cyan-300 border border-white/10">
                                    <?= htmlspecialchars($drama['genre']) ?>
                                </span>
                            </div>

                            <div class="absolute inset-x-0 bottom-0 p-3 pt-12 bg-gradient-to-t from-black via-black/80 to-transparent flex flex-col justify-end">
                                <h3 class="text-xs sm:text-sm font-bold text-white group-hover:text-[#E50914] transition-colors truncate">
                                    <?= htmlspecialchars($drama['title']) ?>
                                </h3>
                                <div class="flex items-center justify-between text-[10px] text-slate-400 mt-0.5">
                                    <span class="truncate"><?= htmlspecialchars($drama['creator_name']) ?></span>
                                    <span class="text-white font-semibold"><?= number_format($drama['views_count']) ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- NETFLIX POSTER ROW CAROUSEL 3: PREMIERES & LATEST -->
    <!-- ======================================================== -->
    <section class="max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12 space-y-3" x-data="{
        scrollLeft() { $refs.row3.scrollBy({ left: -400, behavior: 'smooth' }) },
        scrollRight() { $refs.row3.scrollBy({ left: 400, behavior: 'smooth' }) }
    }">
        <div class="flex items-center justify-between">
            <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Premieres & Terbaharu</span>
            </h2>

            <div class="hidden sm:flex items-center gap-1.5">
                <button @click="scrollLeft" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i>
                </button>
                <button @click="scrollRight" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors">
                    <i data-lucide="chevron-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <div x-ref="row3" class="carousel-row flex items-center gap-3 sm:gap-4 overflow-x-auto pb-4 pt-1">
            <?php foreach ($premieres as $drama): ?>
                <div class="w-36 sm:w-44 md:w-48 lg:w-52 shrink-0">
                    <a href="/drama/<?= $drama['slug'] ?>" class="group block">
                        <div class="relative aspect-vertical rounded-xl overflow-hidden bg-[#181818] border border-white/[0.08] netflix-card shadow-lg">
                            <img src="<?= htmlspecialchars($drama['poster_url']) ?>" alt="<?= htmlspecialchars($drama['title']) ?>" 
                                 class="w-full h-full object-cover">

                            <div class="absolute top-2 left-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#E50914] text-white">
                                    BARU
                                </span>
                            </div>

                            <div class="absolute inset-x-0 bottom-0 p-3 pt-12 bg-gradient-to-t from-black via-black/80 to-transparent flex flex-col justify-end">
                                <h3 class="text-xs sm:text-sm font-bold text-white group-hover:text-[#E50914] transition-colors truncate">
                                    <?= htmlspecialchars($drama['title']) ?>
                                </h3>
                                <div class="flex items-center justify-between text-[10px] text-slate-400 mt-0.5">
                                    <span><?= $drama['episode_count'] ?> Episod</span>
                                    <span><?= htmlspecialchars($drama['genre']) ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- NETFLIX RED VIP AD-FREE PROMO STRIP -->
    <!-- ======================================================== -->
    <section class="max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12">
        <div class="relative rounded-2xl p-6 sm:p-8 overflow-hidden bg-gradient-to-r from-red-950/80 via-[#181818] to-slate-950 border border-[#E50914]/40 shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-4 text-center sm:text-left">
                <div class="w-12 h-12 rounded-xl bg-[#E50914]/20 border border-[#E50914]/40 text-[#E50914] flex items-center justify-center shrink-0">
                    <i data-lucide="crown" class="w-6 h-6 fill-current"></i>
                </div>
                <div>
                    <h3 class="text-xl sm:text-2xl font-black text-white">
                        Langgan Pas VIP Filemai Hanya <span class="text-[#E50914]">RM 5 / Bulan</span>
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-300 mt-0.5">
                        Tonton siri tanpa henti, langkau semua iklan interstitial, dan dapatkan akses episod terkunci!
                    </p>
                </div>
            </div>

            <div class="shrink-0">
                <a href="/subscribe" class="px-6 py-3 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white font-extrabold text-xs sm:text-sm shadow-xl shadow-red-950/80 hover:scale-105 transition-all flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 fill-white"></i>
                    Langgan Sekarang
                </a>
            </div>
        </div>
    </section>

</div>

<!-- Alpine.js Carousel & 5s Trailer Teaser Handler -->
<script>
    function netflixHome(heroList) {
        return {
            heroList: heroList || [],
            currentIndex: 0,
            timer: null,
            trailerActive: true,
            trailerCountdown: 5,
            countdownInterval: null,
            isMuted: true,

            get currentHero() {
                return this.heroList[this.currentIndex] || this.heroList[0] || null;
            },

            init() {
                this.startTeaser();

                if (this.heroList.length > 1) {
                    this.timer = setInterval(() => {
                        this.switchHero((this.currentIndex + 1) % this.heroList.length);
                    }, 8500);
                }
                this.$nextTick(() => lucide.createIcons());
            },

            startTeaser() {
                this.trailerActive = true;
                this.trailerCountdown = 5;
                clearInterval(this.countdownInterval);

                if (this.$refs.trailerVideo) {
                    this.$refs.trailerVideo.currentTime = 0;
                    this.$refs.trailerVideo.play().catch(() => {});
                }

                this.countdownInterval = setInterval(() => {
                    if (this.trailerCountdown > 1) {
                        this.trailerCountdown--;
                    } else {
                        this.trailerActive = false;
                        clearInterval(this.countdownInterval);
                    }
                }, 1000);
            },

            replayTeaser() {
                this.startTeaser();
            },

            switchHero(index) {
                this.currentIndex = index;
                this.startTeaser();
                this.$nextTick(() => lucide.createIcons());
            },

            toggleMute() {
                if (this.$refs.trailerVideo) {
                    this.isMuted = !this.isMuted;
                    this.$refs.trailerVideo.muted = this.isMuted;
                    this.$nextTick(() => lucide.createIcons());
                }
            }
        }
    }
</script>
