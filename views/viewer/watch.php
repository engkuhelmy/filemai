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
    
    <!-- YouTube IFrame Player API -->
    <script src="https://www.youtube.com/iframe_api"></script>

    <!-- Hls.js for Adaptive Bitrate Streaming (Direct video mode) -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <link rel="stylesheet" href="/assets/css/style.css?v=3">

    <style>
        html, body {
            height: 100%;
            background-color: #0b0c0f;
            touch-action: pan-x pan-y;
        }

        .player-wrapper {
            position: relative;
            width: 100%;
            max-width: 440px;
            aspect-ratio: 9 / 16;
            background: #000;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.9);
            border-radius: 1.5rem;
            overflow: hidden;
        }

        @media (max-width: 768px) {
            .player-wrapper {
                max-width: 100%;
                height: 100vh;
                height: 100dvh;
                aspect-ratio: unset;
                border-radius: 0;
            }
        }

        .yt-iframe-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
    </style>
</head>
<body class="bg-[#08090b] text-white select-none min-h-screen flex items-center justify-center p-0 md:p-6" 
      x-data="videoPlayer({
          episodeId: <?= (int)$episode['id'] ?>,
          currentEpisodeNumber: <?= (int)$episode['episode_number'] ?>,
          nextEpisodeId: <?= $nextEpisode ? (int)$nextEpisode['id'] : 'null' ?>,
          prevEpisodeId: <?= $prevEpisode ? (int)$prevEpisode['id'] : 'null' ?>,
          nextEpisodeTitle: <?= json_encode($nextEpisode ? $nextEpisode['title'] : '') ?>,
          nextEpisodeNumber: <?= $nextEpisode ? (int)$nextEpisode['episode_number'] : 'null' ?>,
          prevEpisodeNumber: <?= $prevEpisode ? (int)$prevEpisode['episode_number'] : 'null' ?>,
          videoSource: <?= json_encode($episode['video_source'] ?? 'youtube') ?>,
          youtubeVideoId: <?= json_encode($episode['youtube_video_id'] ?? '') ?>,
          videoUrl: <?= json_encode($episode['video_url'] ?? '') ?>,
          hlsUrl: <?= json_encode($episode['hls_url'] ?? null) ?>,
          isPremium: <?= $isPremium ? 'true' : 'false' ?>,
          adDuration: <?= $ad ? (int)$ad['duration_seconds'] : 15 ?>,
          hasAd: <?= ($ad && !$isPremium) ? 'true' : 'false' ?>
      })">

    <!-- Outer Responsive Container: Center 9:16 Player + Desktop Sidebar Playlist -->
    <div class="w-full max-w-6xl mx-auto flex flex-col lg:flex-row items-center justify-center gap-8 h-full md:h-auto">
        
        <!-- ======================================================== -->
        <!-- 9:16 VERTICAL VIDEO PLAYER THEATER -->
        <!-- ======================================================== -->
        <div class="player-wrapper flex flex-col justify-between relative border border-white/10 shrink-0" 
             id="videoFrame"
             @touchstart="onTouchStart($event)"
             @touchend="onTouchEnd($event)">
            
            <!-- Source 1: YouTube IFrame Embed (pure iframe, no YT.Player API replacement) -->
            <div x-show="videoSource === 'youtube'" 
                 class="absolute inset-0 w-full h-full z-0 yt-iframe-container bg-black flex items-center justify-center overflow-hidden"
                 id="ytContainer">
                <?php if (($episode['video_source'] ?? '') === 'youtube' && !empty($episode['youtube_video_id'])): ?>
                    <?php
                        $ytId = htmlspecialchars($episode['youtube_video_id']);
                        // Clean standard YouTube embed URL - works for regular videos & Shorts, avoids Error 150/152
                        $ytSrc = "https://www.youtube.com/embed/{$ytId}?autoplay=1&mute=1&playsinline=1&controls=1&rel=0&modestbranding=1&enablejsapi=1";
                    ?>
                    <iframe id="ytPlayerFrame" 
                            class="w-full h-full border-0 absolute inset-0"
                            src="<?= $ytSrc ?>"
                            title="<?= htmlspecialchars($episode['title']) ?>"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
                            allowfullscreen
                            loading="eager">
                    </iframe>
                <?php else: ?>
                    <div id="ytPlayerFrame" class="w-full h-full bg-black"></div>
                <?php endif; ?>
            </div>

            <!-- Source 2: Direct MP4 / Adaptive HLS Video Element -->
            <video x-show="videoSource === 'direct'"
                   id="mainVideo" 
                   class="absolute inset-0 w-full h-full object-cover z-0 cursor-pointer"
                   playsinline
                   webkit-playsinline
                   preload="auto"
                   poster="<?= htmlspecialchars($episode['poster_url'] ?? $episode['drama_poster'] ?? '/uploads/posters/ep1_thumb.jpg') ?>"
                   x-ref="videoPlayer"
                   @click="togglePlay"
                   @timeupdate="onTimeUpdate"
                   @ended="onVideoEnded"
                   <?php if (($episode['video_source'] ?? 'direct') === 'direct' && empty($episode['hls_url']) && !empty($episode['video_url'])): ?>
                   src="<?= htmlspecialchars($episode['video_url']) ?>"
                   <?php endif; ?>>
                <?php if (($episode['video_source'] ?? 'direct') === 'direct'): ?>
                    <?php if (!empty($episode['hls_url'])): ?>
                        <source src="<?= htmlspecialchars($episode['hls_url']) ?>" type="application/x-mpegURL">
                    <?php endif; ?>
                    <?php if (!empty($episode['video_url'])): ?>
                        <source src="<?= htmlspecialchars($episode['video_url']) ?>" type="video/mp4">
                    <?php endif; ?>
                <?php endif; ?>
            </video>

            <!-- Top Header Overlay Controls -->
            <div class="relative z-20 p-4 bg-gradient-to-b from-black/95 via-black/60 to-transparent flex items-center justify-between pointer-events-auto">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="/drama/<?= htmlspecialchars($episode['drama_slug']) ?>" 
                       class="w-9 h-9 rounded-full bg-black/60 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/20 transition-all border border-white/10 shrink-0">
                        <i data-lucide="chevron-left" class="w-5 h-5"></i>
                    </a>
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs font-bold text-white truncate max-w-[170px] sm:max-w-[200px]"><?= htmlspecialchars($episode['drama_title']) ?></span>
                        <span class="text-[11px] text-[#E50914] font-semibold truncate"><?= htmlspecialchars($episode['title']) ?></span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <!-- Source Indicator Badge -->
                    <span class="px-2 py-0.5 rounded text-[10px] font-black tracking-wide flex items-center gap-1"
                          :class="videoSource === 'youtube' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30'">
                        <i :data-lucide="videoSource === 'youtube' ? 'youtube' : 'file-video'" class="w-3 h-3"></i>
                        <span x-text="videoSource === 'youtube' ? 'YT' : 'HD'"></span>
                    </span>

                    <!-- Direct YouTube Link Fallback -->
                    <?php if (($episode['video_source'] ?? '') === 'youtube' && !empty($episode['youtube_video_id'])): ?>
                        <a href="https://www.youtube.com/watch?v=<?= htmlspecialchars($episode['youtube_video_id']) ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           title="Tonton di YouTube"
                           class="w-7 h-7 rounded-full bg-red-600/30 hover:bg-red-600 text-red-300 hover:text-white flex items-center justify-center transition-all border border-red-500/40 text-[10px]">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        </a>
                    <?php endif; ?>

                    <!-- VIP Badge / Upgrade button -->
                    <?php if ($isPremium): ?>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 flex items-center gap-1">
                            <i data-lucide="crown" class="w-3 h-3 fill-amber-400"></i> VIP
                        </span>
                    <?php else: ?>
                        <a href="/subscribe" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#E50914] hover:bg-[#F40612] text-white flex items-center gap-1 shadow-md transition-transform hover:scale-105">
                            <i data-lucide="crown" class="w-3 h-3 fill-white"></i> VIP
                        </a>
                    <?php endif; ?>

                    <!-- Mobile Drawer Toggle -->
                    <button @click="drawerOpen = true" 
                            class="lg:hidden w-9 h-9 rounded-full bg-black/60 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/20 transition-all border border-white/10">
                        <i data-lucide="list" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Floating Unmute Button if Autoplay was forced Muted by Browser Policy -->
            <button x-show="isMuted && (isPlaying || videoSource === 'youtube')"
                    @click.stop="unmute()"
                    class="absolute top-20 right-4 z-30 px-3 py-1.5 rounded-full bg-black/80 backdrop-blur-md border border-white/20 text-white text-[11px] font-bold flex items-center gap-1.5 shadow-xl hover:bg-black transition-all animate-pulse"
                    style="display: none;">
                <i data-lucide="volume-x" class="w-3.5 h-3.5 text-[#E50914]"></i>
                <span>Ketik untuk Bersuara</span>
            </button>

            <!-- Center Big Play Indicator for Direct Video -->
            <div class="relative z-10 flex-1 flex items-center justify-center cursor-pointer pointer-events-auto" 
                 x-show="videoSource === 'direct' && !isPlaying" 
                 @click.stop="togglePlay"
                 x-transition>
                <div class="w-16 h-16 rounded-full bg-black/70 backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-2xl scale-110 hover:scale-125 transition-transform">
                    <i data-lucide="play" class="w-8 h-8 fill-white ml-1"></i>
                </div>
            </div>

            <!-- Bottom Controls & Navigation -->
            <div class="relative z-20 p-4 bg-gradient-to-t from-black/95 via-black/75 to-transparent space-y-3 pointer-events-none">
                
                <!-- Seekbar for Direct Video Source -->
                <div x-show="videoSource === 'direct'" class="space-y-1 pointer-events-auto">
                    <div class="relative w-full h-1.5 bg-white/20 rounded-full cursor-pointer overflow-hidden" @click="seek">
                        <div class="h-full bg-[#E50914] rounded-full transition-all duration-100" :style="`width: ${progressPercent}%`"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-400 font-medium">
                        <span x-text="currentTimeFormatted">00:00</span>
                        <span x-text="durationFormatted">00:00</span>
                    </div>
                </div>

                <!-- Navigation Bar: Prev & Next Episode Buttons -->
                <div class="flex items-center justify-between gap-3 pt-1 pointer-events-auto">
                    
                    <?php if ($prevEpisode): ?>
                        <a href="/watch/<?= $prevEpisode['id'] ?>" 
                           class="flex-1 py-2.5 px-3 rounded-full bg-white/15 hover:bg-white/25 border border-white/10 flex items-center justify-center gap-1.5 text-xs font-bold transition-colors">
                            <i data-lucide="skip-back" class="w-3.5 h-3.5"></i>
                            <span>Episod <?= $prevEpisode['episode_number'] ?></span>
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
                           class="flex-1 py-2.5 px-3 rounded-full bg-white/15 hover:bg-white/25 text-center text-xs font-bold text-white transition-colors">
                            Selesai Siri
                        </a>
                    <?php endif; ?>

                </div>

            </div>

            <!-- ======================================================== -->
            <!-- 2-SECOND AUTO-PLAY COUNTDOWN OVERLAY (VIP / AD-FREE) -->
            <!-- ======================================================== -->
            <div x-show="showCountdownOverlay" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute inset-0 z-40 bg-black/90 backdrop-blur-md flex flex-col items-center justify-center p-6 text-center space-y-4"
                 style="display: none;">
                
                <div class="relative w-20 h-20 flex items-center justify-center">
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
                        Kekal di Sini
                    </button>
                    <button @click="proceedNextImmediately()" class="px-6 py-2.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-xs font-extrabold text-white shadow-lg shadow-red-950/80 flex items-center gap-1.5 transition-all">
                        <span>Main Sekarang</span>
                        <i data-lucide="play" class="w-3.5 h-3.5 fill-white"></i>
                    </button>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- STRICT INTERSTITIAL AD-GATE MODAL (FOR FREE USERS) -->
            <!-- ======================================================== -->
            <?php if ($ad && !$isPremium): ?>
            <div id="adModal"
                 x-show="showAdModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute inset-0 z-50 bg-[#0c0d10]/98 backdrop-blur-2xl flex flex-col justify-between p-6"
                 style="display: none;">
                
                <!-- Ad Header -->
                <div class="flex items-center justify-between border-b border-white/10 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-[#E50914] text-white text-[10px] font-black uppercase tracking-wider">
                            Iklan Interstitial
                        </span>
                        <span class="text-xs text-slate-300 truncate max-w-[150px]"><?= htmlspecialchars($ad['sponsor_name'] ?? 'Penaja Rasmi') ?></span>
                    </div>

                    <!-- Countdown Lock Badge -->
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#181818] border border-white/10 text-xs font-bold"
                         :class="adCountdown > 0 ? 'text-amber-400' : 'text-emerald-400 border-emerald-500/40'">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        <template x-if="adCountdown > 0">
                            <span>Kunci dibuka: <strong class="text-white ml-0.5" x-text="adCountdown"></strong>s</span>
                        </template>
                        <template x-if="adCountdown <= 0">
                            <span class="font-black text-emerald-400">Sedia Ditonton!</span>
                        </template>
                    </div>
                </div>

                <!-- Ad Body & Media -->
                <div class="my-auto space-y-4 text-center">
                    <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-[#181818] border border-white/10 shadow-2xl">
                        <video id="adVideo" 
                               class="w-full h-full object-cover" 
                               src="<?= htmlspecialchars($ad['video_url']) ?>" 
                               autoplay 
                               playsinline 
                               muted
                               x-ref="adVideoPlayer">
                        </video>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-base font-extrabold text-white"><?= htmlspecialchars($ad['title']) ?></h3>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto">Tonton iklan 15 saat ini sehingga selesai untuk melangkah ke episod seterusnya.</p>
                    </div>

                    <?php if (!empty($ad['click_url'])): ?>
                        <a href="<?= htmlspecialchars($ad['click_url']) ?>" target="_blank" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white/10 hover:bg-white/20 text-[#E50914] text-xs font-bold border border-[#E50914]/40 transition-colors">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            Lawati Laman Web Penaja
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Ad Footer: Strictly Gated Button -->
                <div class="space-y-3 pt-4 border-t border-white/10">
                    <button @click="skipAdAndPlayNext()" 
                            :disabled="adCountdown > 0"
                            class="w-full py-3.5 rounded-full text-xs font-extrabold flex items-center justify-center gap-2 transition-all"
                            :class="adCountdown > 0 ? 'bg-[#222222] text-slate-500 cursor-not-allowed border border-white/5 opacity-80' : 'bg-[#E50914] hover:bg-[#F40612] text-white shadow-xl shadow-red-950/80 cursor-pointer'">
                        <template x-if="adCountdown > 0">
                            <span class="flex items-center gap-2">
                                <i data-lucide="lock" class="w-4 h-4"></i>
                                <span>Menonton Iklan... (<span x-text="adCountdown"></span>s)</span>
                            </span>
                        </template>
                        <template x-if="adCountdown <= 0">
                            <span class="flex items-center gap-2">
                                <span>Langkau Iklan &amp; Tonton Episod Seterusnya</span>
                                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </span>
                        </template>
                    </button>

                    <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-amber-300 font-medium">
                            <i data-lucide="crown" class="w-4 h-4 fill-amber-400"></i>
                            <span>Langkau semua iklan dengan VIP RM5</span>
                        </div>
                        <a href="/subscribe" class="px-3 py-1 rounded-full bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 transition-colors">
                            Langgan
                        </a>
                    </div>
                </div>

            </div>
            <?php endif; ?>

            <!-- Slide-over Episode Drawer Modal (Mobile View) -->
            <div x-show="drawerOpen" 
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="lg:hidden absolute inset-x-0 bottom-0 z-40 max-h-[75vh] bg-[#141414]/95 backdrop-blur-2xl border-t border-white/10 rounded-t-3xl p-5 flex flex-col"
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
                        <div @click="handleEpisodeClick(<?= (int)$ep['id'] ?>, <?= (int)$ep['episode_number'] ?>)"
                             class="p-3 rounded-xl flex items-center justify-between gap-3 cursor-pointer transition-colors <?= ($ep['id'] == $episode['id']) ? 'bg-[#E50914]/20 border border-[#E50914]/50 text-white' : 'bg-[#181818] hover:bg-[#222] text-slate-300 border border-white/5' ?>">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-lg <?= ($ep['id'] == $episode['id']) ? 'bg-[#E50914] text-white' : 'bg-white/10 text-slate-400' ?> flex items-center justify-center text-xs font-bold shrink-0">
                                    <?= $ep['episode_number'] ?>
                                </span>
                                <span class="text-xs font-medium truncate max-w-[200px]"><?= htmlspecialchars($ep['title']) ?></span>
                            </div>
                            <div class="flex items-center gap-2 text-[10px] text-slate-400 shrink-0">
                                <?php if (($ep['video_source'] ?? '') === 'youtube'): ?>
                                    <span class="text-red-400 font-bold">YT</span>
                                <?php endif; ?>
                                <?php if ($ep['id'] == $episode['id']): ?>
                                    <span class="px-1.5 py-0.5 rounded bg-[#E50914] text-white font-bold">Sedang Main</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </div>

        </div>

        <!-- ======================================================== -->
        <!-- DESKTOP SIDEBAR PLAYLIST (RIGHT COLUMN) -->
        <!-- ======================================================== -->
        <div class="hidden lg:flex flex-col w-80 max-h-[780px] rounded-3xl bg-[#121316] border border-white/10 p-5 shadow-2xl space-y-4">
            <div class="border-b border-white/10 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                        <i data-lucide="layers" class="w-4 h-4 text-[#E50914]"></i>
                        Senarai Episod
                    </h3>
                    <p class="text-[11px] text-slate-400"><?= count($allEpisodes) ?> Episod Siri</p>
                </div>
                <?php if ($isPremium): ?>
                    <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-bold border border-amber-500/30">
                        Ad-Free VIP
                    </span>
                <?php else: ?>
                    <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[10px] font-semibold">
                        Percuma (Iklan)
                    </span>
                <?php endif; ?>
            </div>

            <!-- Scrollable Episodes List -->
            <div class="overflow-y-auto space-y-2 pr-1 flex-1">
                <?php foreach ($allEpisodes as $ep): ?>
                    <div @click="handleEpisodeClick(<?= (int)$ep['id'] ?>, <?= (int)$ep['episode_number'] ?>)"
                         class="p-3 rounded-2xl flex items-center justify-between gap-3 cursor-pointer transition-all <?= ($ep['id'] == $episode['id']) ? 'bg-[#E50914]/20 border border-[#E50914]/50 text-white shadow-md' : 'bg-[#18191d] hover:bg-[#202227] text-slate-300 border border-white/5' ?>">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-8 h-8 rounded-xl <?= ($ep['id'] == $episode['id']) ? 'bg-[#E50914] text-white shadow' : 'bg-white/5 text-slate-400' ?> flex items-center justify-center text-xs font-extrabold shrink-0">
                                <?= $ep['episode_number'] ?>
                            </span>
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-white truncate"><?= htmlspecialchars($ep['title']) ?></h4>
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5">
                                    <?php if (($ep['video_source'] ?? '') === 'youtube'): ?>
                                        <span class="text-red-400 font-bold">YouTube</span>
                                        <span>•</span>
                                    <?php endif; ?>
                                    <span><?= gmdate('i:s', $ep['duration'] ?? 60) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center">
                            <?php if ($ep['id'] == $episode['id']): ?>
                                <span class="w-6 h-6 rounded-full bg-[#E50914] text-white flex items-center justify-center">
                                    <i data-lucide="play" class="w-3 h-3 fill-white ml-0.5"></i>
                                </span>
                            <?php elseif (!$isPremium && $ep['episode_number'] > $episode['episode_number']): ?>
                                <span class="w-6 h-6 rounded-full bg-white/5 text-slate-400 flex items-center justify-center" title="Perlu tonton iklan">
                                    <i data-lucide="lock" class="w-3 h-3 text-amber-400"></i>
                                </span>
                            <?php else: ?>
                                <span class="w-6 h-6 rounded-full bg-white/5 text-slate-400 flex items-center justify-center hover:bg-white/10">
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Ad-Free Subscription Promo Callout -->
            <?php if (!$isPremium): ?>
                <div class="pt-2 border-t border-white/10">
                    <a href="/subscribe" class="block p-3 rounded-2xl bg-gradient-to-r from-red-950/40 to-amber-950/40 border border-[#E50914]/30 hover:border-[#E50914]/60 transition-all text-center space-y-1">
                        <div class="flex items-center justify-center gap-1.5 text-xs font-extrabold text-amber-300">
                            <i data-lucide="crown" class="w-4 h-4 fill-amber-400"></i>
                            <span>VIP Pas Tanpa Iklan</span>
                        </div>
                        <p class="text-[10px] text-slate-400">Tonton tanpa henti untuk hanya RM5/bulan</p>
                    </a>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- Alpine.js Integrated Video & Ad Engine Script -->
    <script>
        function videoPlayer(config) {
            return {
                episodeId: config.episodeId,
                currentEpisodeNumber: config.currentEpisodeNumber,
                nextEpisodeId: config.nextEpisodeId,
                prevEpisodeId: config.prevEpisodeId,
                nextEpisodeTitle: config.nextEpisodeTitle || 'Episod Seterusnya',
                nextEpisodeNumber: config.nextEpisodeNumber,
                prevEpisodeNumber: config.prevEpisodeNumber,
                videoSource: config.videoSource,
                youtubeVideoId: config.youtubeVideoId,
                videoUrl: config.videoUrl,
                hlsUrl: config.hlsUrl,
                isPremium: config.isPremium,
                hasAd: config.hasAd,
                adDuration: config.adDuration || 15,
                
                isPlaying: false,
                isMuted: false,
                drawerOpen: false,
                progressPercent: 0,
                currentTimeFormatted: '00:00',
                durationFormatted: '00:00',

                // YouTube Player instance
                ytPlayer: null,

                // 2s Auto-Play Countdown
                showCountdownOverlay: false,
                countdownSeconds: 2,
                countdownInterval: null,

                // Touch Swipe
                touchStartY: 0,
                touchStartX: 0,

                // Rewarded Ad Modal
                showAdModal: false,
                adCountdown: config.adDuration || 15,
                adTimerInterval: null,
                targetNextId: null,

                init() {
                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                        if (this.videoSource === 'youtube') {
                            this.initYouTube();
                        } else {
                            const video = this.$refs.videoPlayer || document.getElementById('mainVideo');
                            this.initDirectVideo(video);
                        }
                    });
                },

                initYouTube() {
                    if (!this.youtubeVideoId) return;

                    this.isMuted = true;
                    this.isPlaying = true; // iframe autoplays via mute=1&autoplay=1 in src

                    // ── postMessage state tracking (works without YT.Player API) ──────
                    const ytMsgHandler = (e) => {
                        try {
                            const data = typeof e.data === 'string' ? JSON.parse(e.data) : e.data;
                            if (!data) return;
                            // onStateChange: 0=ENDED 1=PLAYING 2=PAUSED
                            if (data.event === 'onStateChange') {
                                const s = data.info;
                                if (s === 1) { this.isPlaying = true; }
                                else if (s === 2) { this.isPlaying = false; }
                                else if (s === 0) { this.isPlaying = false; this.onVideoEnded(); }
                            }
                            if (data.event === 'onReady') { this.isPlaying = true; }
                        } catch (_) {}
                    };
                    window.addEventListener('message', ytMsgHandler);

                    // ── Store direct iframe reference for postMessage commands ─────────
                    // We do NOT call new YT.Player() because it replaces the existing
                    // iframe element (even when passed as a DOM node), destroying the
                    // autoplay=1&mute=1 state already set via the src URL.
                    this._ytIframe = document.getElementById('ytPlayerFrame');
                },

                // Send postMessage command to YouTube iframe
                _ytCmd(func, args) {
                    const f = this._ytIframe || document.getElementById('ytPlayerFrame');
                    if (f && f.contentWindow) {
                        try {
                            const payload = JSON.stringify({
                                event: 'command',
                                func: func,
                                args: args !== undefined ? args : ''
                            });
                            f.contentWindow.postMessage(payload, '*');
                        } catch (_) {}
                    }
                },

                initDirectVideo(video) {
                    if (!video) return;

                    // Sync video events with state
                    video.addEventListener('play', () => { 
                        this.isPlaying = true; 
                    });
                    video.addEventListener('pause', () => { 
                        this.isPlaying = false; 
                    });
                    video.addEventListener('volumechange', () => {
                        this.isMuted = video.muted;
                    });

                    const attemptAutoplay = () => {
                        // Start muted autoplay (guaranteed permission by mobile & desktop browsers)
                        video.muted = true;
                        this.isMuted = true;
                        const playPromise = video.play();
                        if (playPromise !== undefined) {
                            playPromise.then(() => {
                                this.isPlaying = true;
                                if (typeof lucide !== 'undefined') lucide.createIcons();
                            }).catch(() => {
                                // If unmuted was requested and failed, ensure muted is set and retry
                                video.muted = true;
                                this.isMuted = true;
                                video.play().then(() => {
                                    this.isPlaying = true;
                                    if (typeof lucide !== 'undefined') lucide.createIcons();
                                }).catch(() => {
                                    this.isPlaying = false;
                                });
                            });
                        }
                    };

                    // Check if HLS.js is supported (only if this episode actually has an HLS playlist)
                    if (this.hlsUrl && typeof Hls !== 'undefined' && Hls.isSupported()) {
                        const hls = new Hls({ 
                            autoStartLoad: true,
                            enableWorker: true,
                            lowLatencyMode: true
                        });
                        hls.loadSource(this.hlsUrl);
                        hls.attachMedia(video);
                        hls.on(Hls.Events.MANIFEST_PARSED, () => {
                            attemptAutoplay();
                        });
                        hls.on(Hls.Events.ERROR, (event, data) => {
                            if (data.fatal) {
                                switch (data.type) {
                                    case Hls.ErrorTypes.NETWORK_ERROR:
                                        hls.startLoad();
                                        break;
                                    case Hls.ErrorTypes.MEDIA_ERROR:
                                        hls.recoverMediaError();
                                        break;
                                    default:
                                        hls.destroy();
                                        if (this.videoUrl) {
                                            video.src = this.videoUrl;
                                            attemptAutoplay();
                                        }
                                        break;
                                }
                            }
                        });
                    } else if (this.hlsUrl && (video.canPlayType('application/vnd.apple.mpegurl') || video.canPlayType('application/x-mpegURL'))) {
                        // Native HLS for Safari & iOS
                        video.src = this.hlsUrl;
                        video.addEventListener('loadedmetadata', () => {
                            attemptAutoplay();
                        }, { once: true });
                    } else {
                        // Direct MP4 Video Playback
                        if (!video.src && this.videoUrl) {
                            video.src = this.videoUrl;
                        }

                        // Immediate check: if video already has enough data, play immediately
                        if (video.readyState >= 1) {
                            attemptAutoplay();
                        } else {
                            video.addEventListener('loadedmetadata', () => {
                                if (!this.isPlaying) attemptAutoplay();
                            }, { once: true });
                            video.addEventListener('canplay', () => {
                                if (!this.isPlaying) attemptAutoplay();
                            }, { once: true });
                        }

                        // Safety timer: attempt autoplay in case events were already consumed
                        setTimeout(() => {
                            if (!this.isPlaying && video.paused) {
                                attemptAutoplay();
                            }
                        }, 250);
                    }
                },

                unmute() {
                    if (this.videoSource === 'youtube') {
                        this._ytCmd('unMute', '');
                        this._ytCmd('setVolume', [100]);
                        this._ytCmd('playVideo', '');
                        this.isMuted = false;
                        return;
                    }
                    const video = this.$refs.videoPlayer || document.getElementById('mainVideo');
                    if (video) {
                        video.muted = false;
                        this.isMuted = false;
                    }
                },

                togglePlay() {
                    if (this.videoSource === 'youtube') {
                        if (this.isPlaying) {
                            this._ytCmd('pauseVideo', '');
                            this.isPlaying = false;
                        } else {
                            if (this.isMuted) {
                                this._ytCmd('unMute', '');
                                this._ytCmd('setVolume', [100]);
                                this.isMuted = false;
                            }
                            this._ytCmd('playVideo', '');
                            this.isPlaying = true;
                        }
                        return;
                    }
                    const video = this.$refs.videoPlayer || document.getElementById('mainVideo');
                    if (!video) return;
                    if (video.paused) {
                        if (this.isMuted) {
                            video.muted = false;
                            this.isMuted = false;
                        }
                        video.play().then(() => {
                            this.isPlaying = true;
                        }).catch(() => {
                            video.muted = true;
                            this.isMuted = true;
                            video.play().then(() => {
                                this.isPlaying = true;
                            }).catch(() => {});
                        });
                    } else {
                        video.pause();
                        this.isPlaying = false;
                    }
                },

                onTimeUpdate() {
                    const video = this.$refs.videoPlayer || document.getElementById('mainVideo');
                    if (!video || isNaN(video.duration)) return;
                    this.progressPercent = (video.currentTime / video.duration) * 100;
                    this.currentTimeFormatted = this.formatTime(video.currentTime);
                    this.durationFormatted = this.formatTime(video.duration);
                },

                seek(e) {
                    const video = this.$refs.videoPlayer || document.getElementById('mainVideo');
                    if (!video || !video.duration) return;
                    const rect = e.currentTarget.getBoundingClientRect();
                    const pos = (e.clientX - rect.left) / rect.width;
                    video.currentTime = pos * video.duration;
                },

                formatTime(seconds) {
                    const mins = Math.floor(seconds / 60);
                    const secs = Math.floor(seconds % 60);
                    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                },

                // Video Ended Handler (Called by YouTube onStateChange ENDED or video onended)
                onVideoEnded() {
                    this.isPlaying = false;
                    if (!this.nextEpisodeId) return;

                    if (this.isPremium || !this.hasAd) {
                        // VIP User: Auto advance with brief 2-second countdown
                        this.startAutoPlayCountdown();
                    } else {
                        // Free User: Strict Rewarded Interstitial Ad Gate
                        this.triggerNextEpisode(this.nextEpisodeId);
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
                            window.location.href = `/watch/${this.nextEpisodeId}`;
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
                    window.location.href = `/watch/${this.nextEpisodeId}`;
                },

                // Intercept Episode clicks from Sidebar or Drawer
                handleEpisodeClick(targetEpId, targetEpNumber) {
                    // VIP or free without ads: direct navigation
                    if (this.isPremium || !this.hasAd) {
                        window.location.href = `/watch/${targetEpId}`;
                        return;
                    }

                    // Free users clicking future episode: Intercept with Ad Modal!
                    if (targetEpNumber > this.currentEpisodeNumber) {
                        this.drawerOpen = false;
                        this.triggerNextEpisode(targetEpId);
                    } else {
                        // Re-watching current or previous episode: allow direct
                        window.location.href = `/watch/${targetEpId}`;
                    }
                },

                // Trigger Interstitial Ad Modal
                triggerNextEpisode(targetEpId) {
                    if (this.isPremium || !this.hasAd) {
                        window.location.href = `/watch/${targetEpId}`;
                        return;
                    }

                    // Pause YouTube or Direct video if still playing
                    if (this.videoSource === 'youtube') {
                        this._ytCmd('pauseVideo');
                    }
                    const video = this.$refs.videoPlayer || document.getElementById('mainVideo');
                    if (video) {
                        try { video.pause(); } catch (e) {}
                    }

                    this.targetNextId = targetEpId;
                    this.showAdModal = true;
                    this.adCountdown = this.adDuration;

                    this.$nextTick(() => {
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                        const adVid = this.$refs.adVideoPlayer || document.getElementById('adVideo');
                        if (adVid) {
                            adVid.currentTime = 0;
                            adVid.play().catch(() => {});
                        }
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
                    const destId = this.targetNextId || this.nextEpisodeId;
                    if (destId) {
                        window.location.href = `/watch/${destId}`;
                    } else {
                        this.showAdModal = false;
                    }
                },

                // Mobile Touch Swipe Handling
                onTouchStart(e) {
                    this.touchStartY = e.touches[0].clientY;
                    this.touchStartX = e.touches[0].clientX;
                },

                onTouchEnd(e) {
                    const deltaY = e.changedTouches[0].clientY - this.touchStartY;
                    const deltaX = e.changedTouches[0].clientX - this.touchStartX;

                    if (Math.abs(deltaY) > 60 && Math.abs(deltaY) > Math.abs(deltaX)) {
                        if (deltaY < 0 && this.nextEpisodeId) {
                            // Swiped Up -> Next Episode
                            this.triggerNextEpisode(this.nextEpisodeId);
                        } else if (deltaY > 0 && this.prevEpisodeId) {
                            // Swiped Down -> Prev Episode
                            window.location.href = `/watch/${this.prevEpisodeId}`;
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>
