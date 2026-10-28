<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= htmlspecialchars($title) ?></title>
    
    <!-- PWA & Mobile Standalone Meta -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#000000">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Filemai">
    <link rel="apple-touch-icon" href="/assets/icons/icon-192.png">

    <!-- Google Fonts: Bebas Neue & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        netflix: {
                            red: '#E50914',
                            redHover: '#F40612',
                            black: '#0b0c0f',
                            card: '#181818',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        brand: ['Bebas Neue', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Hls.js for Adaptive Bitrate Streaming -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="/assets/css/style.css?v=3">

    <style>
        html, body {
            height: 100%;
            overflow: hidden;
            background-color: #000;
            touch-action: pan-x pan-y;
        }

        .player-container {
            max-width: 480px;
            height: 100vh;
            margin: 0 auto;
            position: relative;
            background: #000;
            box-shadow: 0 0 60px rgba(0, 0, 0, 0.95);
        }

        @media (max-width: 640px) {
            .player-container {
                max-width: 100%;
                height: 100%;
            }
        }
    </style>
</head>
<body class="bg-black text-white select-none" 
      x-data="videoPlayer({
          episodeId: <?= (int)$episode['id'] ?>,
          nextEpisodeId: <?= $nextEpisode ? (int)$nextEpisode['id'] : 'null' ?>,
          prevEpisodeId: <?= $prevEpisode ? (int)$prevEpisode['id'] : 'null' ?>,
          nextEpisodeTitle: <?= json_encode($nextEpisode ? $nextEpisode['title'] : '') ?>,
          nextEpisodeNumber: <?= $nextEpisode ? (int)$nextEpisode['episode_number'] : 'null' ?>,
          prevEpisodeNumber: <?= $prevEpisode ? (int)$prevEpisode['episode_number'] : 'null' ?>,
          videoUrl: <?= json_encode($episode['video_url']) ?>,
          hlsUrl: <?= json_encode($episode['hls_url'] ?? null) ?>,
          nextVideoUrl: <?= json_encode($nextEpisode ? $nextEpisode['video_url'] : null) ?>,
          nextHlsUrl: <?= json_encode($nextEpisode ? ($nextEpisode['hls_url'] ?? null) : null) ?>,
          isPremium: <?= $isPremium ? 'true' : 'false' ?>,
          adDuration: <?= $ad ? (int)$ad['duration_seconds'] : 15 ?>,
          hasAd: <?= ($ad && !$isPremium) ? 'true' : 'false' ?>
      })">

    <!-- Vertical Video Player Frame (9:16 Aspect) with Mobile Touch Swipe -->
    <div class="player-container flex flex-col justify-between overflow-hidden relative" 
         id="videoFrame"
         @touchstart="onTouchStart($event)"
         @touchend="onTouchEnd($event)"
         @wheel.passive="onWheel($event)">
        
        <!-- Main Video Element (Supports HLS .m3u8 & MP4 fallback) -->
        <video id="mainVideo" 
               class="absolute inset-0 w-full h-full object-cover z-0"
               playsinline
               webkit-playsinline
               x-ref="videoPlayer"
               @click="togglePlay"
               @timeupdate="onTimeUpdate"
               @ended="onVideoEnded">
        </video>

        <!-- Top Header Overlay Controls -->
        <div class="relative z-20 p-4 bg-gradient-to-b from-black/90 via-black/50 to-transparent flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/drama/<?= htmlspecialchars($episode['drama_slug']) ?>" 
                   class="w-9 h-9 rounded-full bg-black/60 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/20 transition-all border border-white/10">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </a>
                <div class="flex flex-col min-w-0">
                    <span class="text-xs font-bold text-white truncate max-w-[190px]"><?= htmlspecialchars($episode['drama_title']) ?></span>
                    <span class="text-[11px] text-[#E50914] font-semibold truncate"><?= htmlspecialchars($episode['title']) ?></span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <!-- Adaptive Bitrate HLS Stream Badge -->
                <span class="px-2 py-0.5 rounded text-[10px] font-black tracking-wide"
                      :class="usingHls ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-white/10 text-slate-300'">
                    <span x-text="usingHls ? '⚡ HLS' : 'HD'"></span>
                </span>

                <!-- VIP Badge / Upgrade button -->
                <?php if ($isPremium): ?>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 flex items-center gap-1">
                        <i data-lucide="crown" class="w-3 h-3 fill-amber-400"></i> VIP
                    </span>
                <?php else: ?>
                    <a href="/subscribe" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#E50914] hover:bg-[#F40612] text-white flex items-center gap-1 shadow-md transition-transform hover:scale-105">
                        <i data-lucide="crown" class="w-3 h-3 fill-white"></i> VIP RM5
                    </a>
                <?php endif; ?>

                <!-- Drawer Toggle -->
                <button @click="drawerOpen = true" 
                        class="w-9 h-9 rounded-full bg-black/60 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/20 transition-all border border-white/10">
                    <i data-lucide="list" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- Center Big Play/Pause Indicator -->
        <div class="relative z-10 flex-1 flex items-center justify-center pointer-events-none" x-show="!isPlaying" x-transition>
            <div class="w-16 h-16 rounded-full bg-black/70 backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-2xl scale-110">
                <i data-lucide="play" class="w-8 h-8 fill-white ml-1"></i>
            </div>
        </div>

        <!-- Swipe Gestures Feedback Toast Overlay -->
        <div x-show="swipeMessage" 
             x-transition.opacity.duration.200ms
             class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-40 px-4 py-2 rounded-full bg-black/85 backdrop-blur-xl border border-white/20 text-xs font-bold text-white shadow-2xl flex items-center gap-2 pointer-events-none"
             style="display: none;">
            <i :data-lucide="swipeDirection === 'up' ? 'arrow-up' : 'arrow-down'" class="w-4 h-4 text-[#E50914]"></i>
            <span x-text="swipeMessage"></span>
        </div>

        <!-- TikTok Style Discovery Hint (Fades out automatically after 3.5s) -->
        <div x-show="showSwipeGuide && nextEpisodeId" 
             x-transition.opacity.duration.600ms
             class="absolute bottom-28 left-1/2 transform -translate-x-1/2 z-30 pointer-events-none text-center">
            <div class="px-3.5 py-1.5 rounded-full bg-black/70 backdrop-blur-md border border-white/15 text-[10px] font-bold text-slate-200 shadow-xl flex items-center gap-1.5 animate-bounce">
                <i data-lucide="chevrons-up" class="w-3.5 h-3.5 text-[#E50914]"></i>
                <span>Leret ke atas untuk episod seterusnya</span>
            </div>
        </div>

        <!-- Right Side Floating Actions (TikTok / Reels layout) -->
        <div class="absolute right-3 bottom-24 z-20 flex flex-col items-center gap-3.5">
            
            <a href="/drama/<?= $episode['drama_slug'] ?>" class="relative group" title="Profil Pembikin Filem">
                <div class="w-10 h-10 rounded-full bg-[#E50914] border-2 border-white flex items-center justify-center font-bold text-white text-xs shadow-lg">
                    <?= substr($episode['creator_name'], 0, 1) ?>
                </div>
            </a>

            <!-- Like Button -->
            <button @click="isLiked = !isLiked" class="flex flex-col items-center gap-1 text-white">
                <div class="w-10 h-10 rounded-full bg-black/50 backdrop-blur-md flex items-center justify-center border border-white/10"
                     :class="isLiked ? 'text-red-500 bg-red-500/20 border-red-500/40' : ''">
                    <i data-lucide="heart" class="w-5 h-5" :class="isLiked ? 'fill-red-500' : ''"></i>
                </div>
                <span class="text-[10px] font-bold">1.4K</span>
            </button>

            <!-- Episodes List Button -->
            <button @click="drawerOpen = true" class="flex flex-col items-center gap-1 text-white" title="Senarai Episod">
                <div class="w-10 h-10 rounded-full bg-black/50 backdrop-blur-md flex items-center justify-center border border-white/10">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
                <span class="text-[10px] font-bold">Episod</span>
            </button>

            <!-- Mute / Unmute -->
            <button @click="toggleMute" class="flex flex-col items-center gap-1 text-white" title="Audio">
                <div class="w-10 h-10 rounded-full bg-black/50 backdrop-blur-md flex items-center justify-center border border-white/10">
                    <i :data-lucide="isMuted ? 'volume-x' : 'volume-2'" class="w-5 h-5"></i>
                </div>
            </button>

            <!-- Buffer indicator for Next Episode (Appears when 80% is buffered) -->
            <div x-show="nextPreloaded" x-transition.opacity.duration.300ms class="flex flex-col items-center" title="Episod seterusnya telah sedia dibuffer">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
            </div>
        </div>

        <!-- Bottom Controls (Netflix Red Progress Bar & Next/Prev) -->
        <div class="relative z-20 p-4 bg-gradient-to-t from-black/95 via-black/70 to-transparent space-y-3">
            
            <!-- Netflix Red Seekbar -->
            <div class="space-y-1">
                <div class="relative w-full h-1.5 bg-white/20 rounded-full cursor-pointer overflow-hidden" @click="seek">
                    <div class="h-full bg-[#E50914] rounded-full transition-all duration-100" :style="`width: ${progressPercent}%`"></div>
                </div>
                <div class="flex items-center justify-between text-[10px] text-slate-400 font-medium">
                    <span x-text="currentTimeFormatted">00:00</span>
                    <span class="text-[9px] text-emerald-400 font-semibold" x-show="nextPreloaded">⚡ Episod Seterusnya Dibuffer (80%)</span>
                    <span x-text="durationFormatted">00:00</span>
                </div>
            </div>

            <!-- Bottom Navigation Bar: Prev & Next Episode (Swipeable) -->
            <div class="flex items-center justify-between gap-3 pt-1">
                
                <?php if ($prevEpisode): ?>
                    <a href="/watch/<?= $prevEpisode['id'] ?>" 
                       class="flex-1 py-2.5 px-3 rounded-full bg-white/15 hover:bg-white/25 border border-white/10 flex items-center justify-center gap-1.5 text-xs font-bold transition-colors">
                        <i data-lucide="skip-back" class="w-3.5 h-3.5"></i>
                        Episod <?= $prevEpisode['episode_number'] ?>
                    </a>
                <?php else: ?>
                    <div class="flex-1 py-2.5 px-3 rounded-full bg-white/5 border border-white/5 text-center text-xs text-slate-500">
                        Episod Pertama
                    </div>
                <?php endif; ?>

                <?php if ($nextEpisode): ?>
                    <button @click="triggerNextEpisode(<?= $nextEpisode['id'] ?>)" 
                            class="flex-1 py-2.5 px-3 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white flex items-center justify-center gap-1.5 text-xs font-black shadow-lg shadow-red-950/80 transition-all">
                        <span>Episod <?= $nextEpisode['episode_number'] ?></span>
                        <i data-lucide="skip-forward" class="w-3.5 h-3.5"></i>
                    </button>
                <?php else: ?>
                    <a href="/drama/<?= $episode['drama_slug'] ?>" 
                       class="flex-1 py-2.5 px-3 rounded-full bg-white/15 text-center text-xs font-bold text-white">
                        Selesai Siri
                    </a>
                <?php endif; ?>

            </div>

        </div>

        <!-- ======================================================== -->
        <!-- 2-SECOND NEXT EPISODE AUTO-PLAY COUNTDOWN OVERLAY -->
        <!-- ======================================================== -->
        <div x-show="showCountdownOverlay" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute inset-0 z-40 bg-black/85 backdrop-blur-md flex flex-col items-center justify-center p-6 text-center space-y-4"
             style="display: none;">
            
            <div class="relative w-20 h-20 flex items-center justify-center">
                <!-- Circular Animated Countdown Ring -->
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="4" class="text-white/20" fill="transparent"/>
                    <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="4" class="text-[#E50914] transition-all duration-1000 ease-linear"
                            fill="transparent"
                            stroke-dasharray="213.6"
                            :stroke-dashoffset="213.6 * (1 - (countdownSeconds / 2))"/>
                </svg>
                <span class="absolute text-2xl font-black text-white" x-text="countdownSeconds">2</span>
            </div>

            <div class="space-y-1 max-w-xs">
                <span class="text-[11px] font-bold text-[#E50914] uppercase tracking-wider">Memulakan Episod Seterusnya</span>
                <h3 class="text-base font-extrabold text-white" x-text="nextEpisodeTitle"></h3>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button @click="cancelAutoPlay()" class="px-5 py-2.5 rounded-full bg-white/10 hover:bg-white/20 text-xs font-bold text-slate-300 transition-colors">
                    Kekal / Batal
                </button>
                <button @click="proceedNextImmediately()" class="px-6 py-2.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-xs font-extrabold text-white shadow-lg shadow-red-950/80 flex items-center gap-1.5 transition-all">
                    <span>Main Sekarang</span>
                    <i data-lucide="play" class="w-3.5 h-3.5 fill-white"></i>
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- THE REWARDED INTERSTITIAL AD ENGINE MODAL (FOR FREE USERS) -->
        <!-- ======================================================== -->
        <?php if ($ad && !$isPremium): ?>
        <div x-show="showAdModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="absolute inset-0 z-50 bg-black/95 backdrop-blur-xl flex flex-col justify-between p-6"
             style="display: none;">
            
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded bg-[#E50914] text-white text-[10px] font-black uppercase tracking-wider">
                        IKLAN TAJAAN
                    </span>
                    <span class="text-xs text-slate-300"><?= htmlspecialchars($ad['sponsor_name'] ?? 'Penaja Rasmi') ?></span>
                </div>

                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#181818] border border-white/10 text-xs font-bold text-amber-400">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    <template x-if="adCountdown > 0">
                        <span>Langkau dalam <strong class="text-white" x-text="adCountdown"></strong>s</span>
                    </template>
                    <template x-if="adCountdown <= 0">
                        <span class="text-emerald-400 font-extrabold">Boleh Dilangkau!</span>
                    </template>
                </div>
            </div>

            <div class="my-auto space-y-4 text-center">
                <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-[#181818] border border-white/10 shadow-2xl">
                    <video id="adVideo" 
                           class="w-full h-full object-cover" 
                           src="<?= htmlspecialchars($ad['video_url']) ?>" 
                           autoplay 
                           playsinline 
                           x-ref="adVideoPlayer">
                    </video>
                </div>

                <div class="space-y-1">
                    <h3 class="text-lg font-extrabold text-white"><?= htmlspecialchars($ad['title']) ?></h3>
                    <p class="text-xs text-slate-400">Tonton iklan pendek ini untuk teruskan ke episod seterusnya secara percuma.</p>
                </div>

                <?php if (!empty($ad['click_url'])): ?>
                    <a href="<?= htmlspecialchars($ad['click_url']) ?>" target="_blank" 
                       @click="recordAdClick(<?= $ad['id'] ?>)"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white/10 hover:bg-white/20 text-[#E50914] text-xs font-bold border border-[#E50914]/40 transition-colors">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        Lawati Laman Web Penaja
                    </a>
                <?php endif; ?>
            </div>

            <div class="space-y-3 pt-4 border-t border-white/10">
                <button @click="skipAdAndPlayNext" 
                        :disabled="adCountdown > 0"
                        class="w-full py-3.5 rounded-full text-sm font-extrabold flex items-center justify-center gap-2 transition-all"
                        :class="adCountdown > 0 ? 'bg-[#222222] text-slate-500 cursor-not-allowed border border-white/5' : 'bg-[#E50914] hover:bg-[#F40612] text-white shadow-xl shadow-red-950/80 cursor-pointer'">
                    <template x-if="adCountdown > 0">
                        <span>Menonton Iklan (<span x-text="adCountdown"></span>s)</span>
                    </template>
                    <template x-if="adCountdown <= 0">
                        <span class="flex items-center gap-2">
                            <span>Langkau Iklan &amp; Mainkan Episod <?= $nextEpisode ? $nextEpisode['episode_number'] : 'Seterusnya' ?></span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </span>
                    </template>
                </button>

                <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 text-amber-300 font-medium">
                        <i data-lucide="crown" class="w-4 h-4 fill-amber-400"></i>
                        <span>Benci Iklan? Langgan VIP RM5/bulan</span>
                    </div>
                    <a href="/subscribe" class="px-2.5 py-1 rounded-full bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 transition-colors">
                        Langgan
                    </a>
                </div>
            </div>

        </div>
        <?php endif; ?>

        <!-- Slide-over Episode Drawer Modal -->
        <div x-show="drawerOpen" 
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="absolute inset-x-0 bottom-0 z-40 max-h-[75vh] bg-[#141414]/95 backdrop-blur-2xl border-t border-white/10 rounded-t-3xl p-5 flex flex-col"
             style="display: none;">
            
            <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-4">
                <div>
                    <h3 class="text-base font-extrabold text-white">Senarai Episod</h3>
                    <p class="text-xs text-slate-400"><?= htmlspecialchars($episode['drama_title']) ?></p>
                </div>
                <button @click="drawerOpen = false" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-slate-300 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="overflow-y-auto space-y-2 pr-1 flex-1">
                <?php foreach ($allEpisodes as $ep): ?>
                    <a href="/watch/<?= $ep['id'] ?>" 
                       class="p-3 rounded-xl flex items-center justify-between gap-3 transition-colors <?= ($ep['id'] == $episode['id']) ? 'bg-[#E50914]/20 border border-[#E50914]/50 text-white' : 'bg-[#181818] hover:bg-[#222] text-slate-300 border border-white/5' ?>">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg <?= ($ep['id'] == $episode['id']) ? 'bg-[#E50914] text-white' : 'bg-white/10 text-slate-400' ?> flex items-center justify-center text-xs font-bold">
                                <?= $ep['episode_number'] ?>
                            </span>
                            <span class="text-xs font-medium truncate max-w-[220px]"><?= htmlspecialchars($ep['title']) ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-[10px] text-slate-400">
                            <span><?= gmdate('i:s', $ep['duration']) ?></span>
                            <?php if ($ep['id'] == $episode['id']): ?>
                                <span class="px-1.5 py-0.5 rounded bg-[#E50914] text-white font-bold">Sedang Main</span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>

    </div>

    <!-- Alpine.js Video Player Script -->
    <script>
        function videoPlayer(config) {
            return {
                episodeId: config.episodeId,
                nextEpisodeId: config.nextEpisodeId,
                prevEpisodeId: config.prevEpisodeId,
                nextEpisodeTitle: config.nextEpisodeTitle || 'Episod Seterusnya',
                nextEpisodeNumber: config.nextEpisodeNumber,
                prevEpisodeNumber: config.prevEpisodeNumber,
                videoUrl: config.videoUrl,
                hlsUrl: config.hlsUrl,
                nextVideoUrl: config.nextVideoUrl,
                nextHlsUrl: config.nextHlsUrl,
                isPremium: config.isPremium,
                hasAd: config.hasAd,
                adDuration: config.adDuration || 15,
                
                isPlaying: false,
                isMuted: false,
                isLiked: false,
                drawerOpen: false,
                progressPercent: 0,
                currentTimeFormatted: '00:00',
                durationFormatted: '00:00',
                usingHls: false,

                // 80% Preloading Next Episode
                nextPreloaded: false,

                // 2s Auto-Play Countdown
                showCountdownOverlay: false,
                countdownSeconds: 2,
                countdownInterval: null,

                // Swipe Gestures
                touchStartY: 0,
                touchStartX: 0,
                swipeMessage: '',
                swipeDirection: 'up',
                showSwipeGuide: true,
                wheelTimeout: null,

                // Rewarded Ad Modal
                showAdModal: false,
                adCountdown: config.adDuration || 15,
                adTimerInterval: null,
                targetNextId: null,

                init() {
                    const video = this.$refs.videoPlayer;
                    this.initStreaming(video);

                    // Touch guide auto-hide after 3.5 seconds
                    setTimeout(() => {
                        this.showSwipeGuide = false;
                    }, 3500);

                    this.$nextTick(() => lucide.createIcons());
                },

                initStreaming(video) {
                    if (!video) return;

                    // 1. Try HLS.js adaptive bitrate streaming
                    if (this.hlsUrl && typeof Hls !== 'undefined' && Hls.isSupported()) {
                        const hls = new Hls({
                            autoStartLoad: true,
                            startLevel: -1,
                            capLevelToPlayerSize: true,
                            maxBufferLength: 30,
                            maxMaxBufferLength: 60
                        });
                        hls.loadSource(this.hlsUrl);
                        hls.attachMedia(video);
                        hls.on(Hls.Events.MANIFEST_PARSED, () => {
                            this.usingHls = true;
                            video.play().then(() => { this.isPlaying = true; }).catch(() => { this.isPlaying = false; });
                        });
                        hls.on(Hls.Events.ERROR, (event, data) => {
                            if (data.fatal) {
                                console.warn('HLS Fatal Error, falling back to MP4:', data);
                                hls.destroy();
                                this.usingHls = false;
                                video.src = this.videoUrl;
                                video.play().catch(() => {});
                            }
                        });
                    } 
                    // 2. Native Safari iOS HLS support
                    else if (this.hlsUrl && video.canPlayType('application/vnd.apple.mpegurl')) {
                        video.src = this.hlsUrl;
                        this.usingHls = true;
                        video.play().then(() => { this.isPlaying = true; }).catch(() => { this.isPlaying = false; });
                    } 
                    // 3. Progressive MP4 fallback
                    else {
                        video.src = this.videoUrl;
                        this.usingHls = false;
                        video.play().then(() => { this.isPlaying = true; }).catch(() => { this.isPlaying = false; });
                    }
                },

                togglePlay() {
                    const video = this.$refs.videoPlayer;
                    if (video.paused) {
                        video.play();
                        this.isPlaying = true;
                    } else {
                        video.pause();
                        this.isPlaying = false;
                    }
                },

                toggleMute() {
                    const video = this.$refs.videoPlayer;
                    video.muted = !video.muted;
                    this.isMuted = video.muted;
                    this.$nextTick(() => lucide.createIcons());
                },

                onTimeUpdate() {
                    const video = this.$refs.videoPlayer;
                    if (!video || isNaN(video.duration)) return;
                    this.progressPercent = (video.currentTime / video.duration) * 100;
                    this.currentTimeFormatted = this.formatTime(video.currentTime);
                    this.durationFormatted = this.formatTime(video.duration);

                    // Pre-loading at 80% duration
                    const ratio = video.currentTime / video.duration;
                    if (ratio >= 0.8 && !this.nextPreloaded && this.nextEpisodeId) {
                        this.nextPreloaded = true;
                        this.preloadNextEpisode();
                    }
                },

                preloadNextEpisode() {
                    if (!this.nextEpisodeId) return;
                    console.log('⚡ Pre-buffering Next Episode (80% reached)...');

                    // 1. Prefetch next HTML document
                    const linkPrefetch = document.createElement('link');
                    linkPrefetch.rel = 'prefetch';
                    linkPrefetch.href = `/watch/${this.nextEpisodeId}`;
                    document.head.appendChild(linkPrefetch);

                    // 2. Pre-buffer HLS or video file
                    if (this.nextHlsUrl) {
                        fetch(this.nextHlsUrl).then(r => r.text()).then(m3u8 => {
                            const lines = m3u8.split('\n');
                            const firstTs = lines.find(l => l.trim().endsWith('.ts'));
                            if (firstTs) {
                                fetch('/uploads/videos/hls/' + firstTs.trim(), { cache: 'force-cache' });
                            }
                        }).catch(() => {});
                    } else if (this.nextVideoUrl) {
                        const preVid = document.createElement('video');
                        preVid.preload = 'auto';
                        preVid.muted = true;
                        preVid.src = this.nextVideoUrl;
                        preVid.load();
                    }
                },

                seek(e) {
                    const video = this.$refs.videoPlayer;
                    const rect = e.currentTarget.getBoundingClientRect();
                    const pos = (e.clientX - rect.left) / rect.width;
                    video.currentTime = pos * video.duration;
                },

                formatTime(seconds) {
                    const mins = Math.floor(seconds / 60);
                    const secs = Math.floor(seconds % 60);
                    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                },

                // Video Ended: Start 2-Second Countdown
                onVideoEnded() {
                    this.isPlaying = false;
                    if (this.nextEpisodeId) {
                        this.startAutoPlayCountdown();
                    }
                },

                startAutoPlayCountdown() {
                    this.showCountdownOverlay = true;
                    this.countdownSeconds = 2;
                    clearInterval(this.countdownInterval);

                    this.countdownInterval = setInterval(() => {
                        this.countdownSeconds--;
                        if (this.countdownSeconds <= 0) {
                            clearInterval(this.countdownInterval);
                            this.showCountdownOverlay = false;
                            this.triggerNextEpisode(this.nextEpisodeId);
                        }
                    }, 1000);
                },

                cancelAutoPlay() {
                    clearInterval(this.countdownInterval);
                    this.showCountdownOverlay = false;
                },

                proceedNextImmediately() {
                    clearInterval(this.countdownInterval);
                    this.showCountdownOverlay = false;
                    this.triggerNextEpisode(this.nextEpisodeId);
                },

                // Mobile Touch Swipe Handling (TikTok Style)
                onTouchStart(e) {
                    this.touchStartY = e.touches[0].clientY;
                    this.touchStartX = e.touches[0].clientX;
                },

                onTouchEnd(e) {
                    const touchEndY = e.changedTouches[0].clientY;
                    const touchEndX = e.changedTouches[0].clientX;
                    const deltaY = touchEndY - this.touchStartY;
                    const deltaX = touchEndX - this.touchStartX;

                    // Ensure gesture is predominantly vertical and meets threshold
                    if (Math.abs(deltaY) > 55 && Math.abs(deltaY) > Math.abs(deltaX)) {
                        if (deltaY < 0) {
                            // Swiped UP -> Next Episode
                            this.handleSwipeNext();
                        } else {
                            // Swiped DOWN -> Prev Episode
                            this.handleSwipePrev();
                        }
                    }
                },

                onWheel(e) {
                    // Desktop trackpad / mousewheel gesture support
                    if (this.wheelTimeout) return;
                    if (e.deltaY > 60) {
                        this.wheelTimeout = setTimeout(() => { this.wheelTimeout = null; }, 800);
                        this.handleSwipeNext();
                    } else if (e.deltaY < -60) {
                        this.wheelTimeout = setTimeout(() => { this.wheelTimeout = null; }, 800);
                        this.handleSwipePrev();
                    }
                },

                handleSwipeNext() {
                    if (this.nextEpisodeId) {
                        this.swipeDirection = 'up';
                        this.swipeMessage = `↑ Episod ${this.nextEpisodeNumber || ''}...`;
                        setTimeout(() => {
                            this.swipeMessage = '';
                            this.triggerNextEpisode(this.nextEpisodeId);
                        }, 250);
                    } else {
                        this.swipeDirection = 'up';
                        this.swipeMessage = 'Tamat Siri';
                        setTimeout(() => { this.swipeMessage = ''; }, 1000);
                    }
                },

                handleSwipePrev() {
                    if (this.prevEpisodeId) {
                        this.swipeDirection = 'down';
                        this.swipeMessage = `↓ Episod ${this.prevEpisodeNumber || ''}...`;
                        setTimeout(() => {
                            window.location.href = `/watch/${this.prevEpisodeId}`;
                        }, 250);
                    }
                },

                triggerNextEpisode(targetEpId) {
                    // VIP or free episode without interstitial ad transitions instantly
                    if (this.isPremium || !this.hasAd) {
                        window.location.href = `/watch/${targetEpId}`;
                        return;
                    }

                    // Free user: Trigger Rewarded Ad Modal
                    this.targetNextId = targetEpId;
                    this.showAdModal = true;
                    this.adCountdown = this.adDuration;

                    this.$nextTick(() => {
                        lucide.createIcons();
                        const adVid = this.$refs.adVideoPlayer;
                        if (adVid) adVid.play().catch(() => {});
                    });

                    clearInterval(this.adTimerInterval);
                    this.adTimerInterval = setInterval(() => {
                        this.adCountdown--;
                        if (this.adCountdown <= 0) {
                            clearInterval(this.adTimerInterval);
                        }
                    }, 1000);
                },

                skipAdAndPlayNext() {
                    if (this.adCountdown > 0) return;
                    clearInterval(this.adTimerInterval);
                    if (this.targetNextId) {
                        window.location.href = `/watch/${this.targetNextId}`;
                    } else if (this.nextEpisodeId) {
                        window.location.href = `/watch/${this.nextEpisodeId}`;
                    } else {
                        this.showAdModal = false;
                    }
                },

                recordAdClick(adId) {
                    fetch(`/api/ads/${adId}/click`, { method: 'POST' });
                }
            }
        }
    </script>
</body>
</html>
