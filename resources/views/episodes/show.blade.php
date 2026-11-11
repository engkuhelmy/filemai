<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? ($episode->title . ' - ' . $drama->title) }}</title>

    <!-- PWA & Mobile Standalone Meta -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#000000">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Fonts -->
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
                        brand: {
                            red: '#E50914',
                            redHover: '#F40612',
                            dark: '#0b0c0f',
                            card: '#181818',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        title: ['Bebas Neue', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- YouTube IFrame Player API -->
    <script src="https://www.youtube.com/iframe_api"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        html, body {
            height: 100%;
            width: 100%;
            background-color: #000000;
            overflow: hidden;
            touch-action: none;
            -webkit-user-select: none;
            user-select: none;
        }

        /* 9:16 Responsive Container */
        .tiktok-stage {
            position: relative;
            width: 100%;
            height: 100dvh;
            max-width: 440px;
            background: #000000;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .tiktok-stage {
                height: 94vh;
                max-height: 880px;
                aspect-ratio: 9 / 16;
                border-radius: 28px;
                box-shadow: 0 0 60px rgba(229, 9, 20, 0.25), 0 25px 50px -12px rgba(0, 0, 0, 0.9);
                border: 1px solid rgba(255, 255, 255, 0.12);
            }
        }

        /* Ambient background glow */
        .ambient-glow {
            position: fixed;
            inset: -10%;
            background-size: cover;
            background-position: center;
            filter: blur(80px) brightness(0.22);
            opacity: 0.6;
            z-index: 0;
            pointer-events: none;
        }

        /* YouTube IFrame Fill */
        .yt-frame-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
            object-fit: cover;
        }

        /* Custom Scrollbar for Drawer */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-black text-white min-h-screen flex items-center justify-center font-sans relative"
      x-data="tiktokDramaPlayer({
          dramaSlug: '{{ $drama->slug }}',
          dramaTitle: '{{ addslashes($drama->title) }}',
          currentEpisodeNumber: {{ (int)$episode->episode_number }},
          totalEpisodes: {{ count($allEpisodes) }},
          nextUrl: {{ $nextEpisode ? json_encode(route('episodes.show', ['drama' => $drama->slug, 'episode' => $nextEpisode->episode_number])) : 'null' }},
          prevUrl: {{ $prevEpisode ? json_encode(route('episodes.show', ['drama' => $drama->slug, 'episode' => $prevEpisode->episode_number])) : 'null' }},
          nextEpisodeNumber: {{ $nextEpisode ? (int)$nextEpisode->episode_number : 'null' }},
          prevEpisodeNumber: {{ $prevEpisode ? (int)$prevEpisode->episode_number : 'null' }},
          youtubeId: '{{ $episode->effective_youtube_id ?? '' }}',
          videoUrl: '{{ $episode->video_url ?? '' }}',
          isYouTube: {{ $episode->isYouTube() ? 'true' : 'false' }}
      })"
      @keydown.window="handleKeyDown($event)"
      @wheel.window="handleWheel($event)">

    @php
        $ytId = $episode->effective_youtube_id;
        $isYt = !empty($ytId) || $episode->isYouTube();
        $bgPoster = $episode->thumbnail_url ?: ($drama->banner_url ?: $drama->poster_url);
    @endphp

    <!-- Ambient Poster Glow for Desktop Viewers -->
    <div class="ambient-glow hidden md:block" style="background-image: url('{{ $bgPoster }}');"></div>

    <!-- Main TikTok Viewport Frame -->
    <div class="tiktok-stage relative z-10 flex flex-col justify-between"
         x-ref="tiktokStage"
         @touchstart="handleTouchStart($event)"
         @touchmove="handleTouchMove($event)"
         @touchend="handleTouchEnd($event)">

        <!-- ======================================================== -->
        <!-- VIDEO LAYER (YOUTUBE SHORTS / EMBED OR DIRECT HTML5)     -->
        <!-- ======================================================== -->
        <div class="absolute inset-0 w-full h-full bg-black z-0 overflow-hidden">
            @if (!empty($ytId))
                <!-- YouTube Vertical Embed -->
                <div class="yt-frame-container absolute inset-0 w-full h-full">
                    <iframe id="ytPlayer"
                            class="w-full h-full"
                            src="https://www.youtube-nocookie.com/embed/{{ $ytId }}?enablejsapi=1&autoplay=1&mute=0&playsinline=1&controls=1&rel=0&modestbranding=1&loop=1"
                            title="{{ $episode->title }}"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen>
                    </iframe>
                </div>
            @elseif (!empty($episode->video_url))
                <!-- Direct Video Storage Fallback -->
                <video x-ref="directVideo"
                       id="directVideo"
                       class="w-full h-full object-cover"
                       src="{{ $episode->video_url }}"
                       autoplay
                       playsinline
                       webkit-playsinline
                       loop
                       poster="{{ $bgPoster }}"
                       @click="toggleDirectPlay()">
                </video>
            @else
                <!-- No Media Placeholder -->
                <div class="w-full h-full flex flex-col items-center justify-center p-6 text-center bg-neutral-950">
                    <i data-lucide="video-off" class="w-12 h-12 text-neutral-600 mb-3"></i>
                    <p class="text-sm font-semibold text-neutral-400">Media episod belum dimuat naik.</p>
                </div>
            @endif
        </div>

        <!-- ======================================================== -->
        <!-- TOP OVERLAY BAR: BACK, TITLE, EPISODE COUNTER            -->
        <!-- ======================================================== -->
        <div class="relative z-20 p-4 pt-5 bg-gradient-to-b from-black/90 via-black/50 to-transparent flex items-center justify-between pointer-events-auto">
            <div class="flex items-center gap-2.5 min-w-0">
                <!-- Back Button to Drama Main Page -->
                <a href="{{ route('drama.show', $drama->slug) }}" 
                   class="w-9 h-9 rounded-full bg-black/60 backdrop-blur-md border border-white/15 flex items-center justify-center text-white hover:bg-white/20 transition-all shrink-0"
                   title="Kembali ke Halaman Drama">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </a>

                <div class="flex flex-col min-w-0">
                    <a href="{{ route('drama.show', $drama->slug) }}" class="text-xs font-bold text-white truncate max-w-[180px] sm:max-w-[220px] hover:underline">
                        {{ $drama->title }}
                    </a>
                    <div class="flex items-center gap-1.5 text-[11px] text-red-500 font-extrabold">
                        <span>Episod {{ $episode->episode_number }} / {{ count($allEpisodes) }}</span>
                        @if (!empty($ytId))
                            <span class="px-1.5 py-0.2 rounded bg-red-600/30 text-red-400 text-[9px] font-black border border-red-500/40">YT</span>
                        @endif
                        <span class="px-1 py-0.2 rounded bg-white/10 text-white text-[9px] font-bold">{{ $drama->age_rating ?? '13+' }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <!-- Episode Selector Button (Top Shortcut) -->
                <button @click="openEpisodeDrawer()"
                        class="px-3 py-1.5 rounded-full bg-black/60 backdrop-blur-md border border-white/20 hover:border-red-500/50 text-xs font-bold text-white flex items-center gap-1.5 shadow-lg transition-all hover:scale-105">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-red-500"></i>
                    <span>Episod</span>
                </button>
            </div>
        </div>

        <!-- 11. Creator Notice Board Announcement Overlay (If Any) -->
        @if(!empty($drama->announcement))
            <div class="relative z-20 mx-4 -mt-2 mb-2 py-1.5 px-3 rounded-xl bg-amber-500/20 border border-amber-500/40 text-amber-300 text-[11px] flex items-center gap-2 pointer-events-auto backdrop-blur-md">
                <i data-lucide="megaphone" class="w-3.5 h-3.5 text-amber-400 shrink-0"></i>
                <span class="truncate font-medium">{{ $drama->announcement }}</span>
            </div>
        @endif

        <!-- ======================================================== -->
        <!-- RIGHT TIKTOK ACTION SIDEBAR (FLOATING CONTROLS)          -->
        <!-- ======================================================== -->
        <div class="absolute right-3 bottom-24 z-20 flex flex-col items-center gap-4 pointer-events-auto">
            
            <!-- 1. Creator Avatar -->
            <div class="relative mb-1">
                <div class="w-11 h-11 rounded-full p-0.5 bg-gradient-to-tr from-yellow-500 via-red-600 to-pink-500 shadow-xl">
                    <div class="w-full h-full rounded-full bg-[#181818] overflow-hidden flex items-center justify-center text-xs font-black text-white">
                        {{ strtoupper(substr($drama->title, 0, 2)) }}
                    </div>
                </div>
                <button class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-4 h-4 rounded-full bg-red-600 flex items-center justify-center text-white text-[10px] font-black shadow">
                    +
                </button>
            </div>

            <!-- 2. Episode Selection Button (Primary Request) -->
            <button @click="openEpisodeDrawer()"
                    class="group flex flex-col items-center gap-1">
                <div class="w-11 h-11 rounded-full bg-black/65 backdrop-blur-md border border-white/20 group-hover:border-red-500/80 group-hover:bg-red-600/20 flex items-center justify-center text-white shadow-xl transition-all group-hover:scale-110">
                    <i data-lucide="list-video" class="w-5 h-5 text-red-500"></i>
                </div>
                <span class="text-[10px] font-bold text-white tracking-wide shadow-sm">Episod</span>
            </button>

            <!-- 3. Like Button with Tap Heart Animation -->
            <button @click="toggleLike()"
                    class="group flex flex-col items-center gap-1">
                <div class="w-11 h-11 rounded-full bg-black/65 backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-xl transition-all group-hover:scale-110"
                     :class="liked ? 'bg-red-600/30 border-red-500' : ''">
                    <i data-lucide="heart" 
                       class="w-5 h-5 transition-transform duration-200"
                       :class="liked ? 'fill-red-500 text-red-500 scale-125' : 'text-white'"></i>
                </div>
                <span class="text-[10px] font-bold text-white" x-text="likeCountFormatted">12.4K</span>
            </button>

            <!-- 4. Share Button -->
            <button @click="shareEpisode()"
                    class="group flex flex-col items-center gap-1">
                <div class="w-11 h-11 rounded-full bg-black/65 backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-xl transition-all group-hover:scale-110">
                    <i data-lucide="share-2" class="w-5 h-5 text-white"></i>
                </div>
                <span class="text-[10px] font-bold text-white">Kongsi</span>
            </button>

            <!-- 5. Next Episode Quick Trigger -->
            @if ($nextEpisode)
                <button @click="goNext()"
                        title="Episod Seterusnya (Swipe Up)"
                        class="group flex flex-col items-center gap-1">
                    <div class="w-11 h-11 rounded-full bg-[#E50914] text-white flex items-center justify-center shadow-xl shadow-red-950/80 transition-all hover:scale-110 animate-bounce">
                        <i data-lucide="arrow-up" class="w-5 h-5"></i>
                    </div>
                    <span class="text-[10px] font-black text-red-400">Next</span>
                </button>
            @endif

        </div>

        <!-- ======================================================== -->
        <!-- BOTTOM OVERLAY: TITLE, SYNOPSIS & SWIPE UP HINT          -->
        <!-- ======================================================== -->
        <div class="relative z-20 p-4 pb-6 bg-gradient-to-t from-black/95 via-black/75 to-transparent space-y-2.5 pointer-events-auto">
            
            <!-- Drama Creator Tag -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-black text-white hover:underline cursor-pointer">
                    @ {{ $drama->title }}
                </span>
                <span class="px-2 py-0.5 rounded-full bg-white/10 text-[9px] font-bold text-slate-300">
                    {{ $drama->genre }}
                </span>
            </div>

            <!-- Episode Title -->
            <h2 class="text-sm sm:text-base font-extrabold text-white leading-tight">
                {{ $episode->title }}
            </h2>

            <!-- Swipe Navigation Pill Indicator -->
            <div class="flex items-center justify-between pt-1">
                @if ($nextEpisode)
                    <div @click="goNext()"
                         class="cursor-pointer inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/15 hover:bg-white/25 border border-white/20 text-xs font-bold text-white transition-all shadow-md">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                        <i data-lucide="chevron-up" class="w-4 h-4 text-red-400 animate-pulse"></i>
                        <span>Swipe ke atas: <strong>Episod {{ $nextEpisode->episode_number }}</strong></span>
                    </div>
                @else
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-xs font-semibold text-slate-400">
                        <i data-lucide="check-circle" class="w-3.5 h-3.5 text-green-400"></i>
                        <span>Episod Akhir Siri</span>
                    </div>
                @endif

                @if ($prevEpisode)
                    <button @click="goPrev()" 
                            class="text-[11px] font-bold text-slate-400 hover:text-white flex items-center gap-1 transition-colors">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        <span>Ep {{ $prevEpisode->episode_number }}</span>
                    </button>
                @endif
            </div>

        </div>

        <!-- ======================================================== -->
        <!-- SLIDE-UP EPISODE SELECTOR DRAWER (BOTTOM SHEET)          -->
        <!-- ======================================================== -->
        <div x-show="episodeDrawerOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-full opacity-0"
             class="absolute inset-x-0 bottom-0 z-50 max-h-[75%] bg-[#0e1015]/95 backdrop-blur-2xl border-t border-white/15 rounded-t-3xl p-5 flex flex-col shadow-2xl pointer-events-auto"
             style="display: none;"
             @click.outside="episodeDrawerOpen = false">

            <!-- Drawer Header -->
            <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-3">
                <div class="flex items-center gap-2">
                    <i data-lucide="layers" class="w-5 h-5 text-[#E50914]"></i>
                    <div>
                        <h3 class="text-sm font-extrabold text-white">Pilih Episod</h3>
                        <p class="text-[11px] text-slate-400">
                            <a href="{{ route('drama.show', $drama->slug) }}" class="text-slate-300 hover:text-red-400 hover:underline font-bold">{{ $drama->title }}</a> • {{ count($allEpisodes) }} Episod
                        </p>
                    </div>
                </div>

                <button @click="episodeDrawerOpen = false" 
                        class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <!-- Scrollable Episodes Grid / List -->
            <div class="overflow-y-auto space-y-2 pr-1 flex-1 no-scrollbar max-h-[50vh]">
                @foreach ($allEpisodes as $ep)
                    @php
                        $isActive = ($ep->id === $episode->id);
                        $epYt = $ep->effective_youtube_id;
                    @endphp
                    <a href="{{ route('episodes.show', ['drama' => $drama->slug, 'episode' => $ep->episode_number]) }}"
                       class="p-3 rounded-2xl flex items-center justify-between gap-3 cursor-pointer transition-all {{ $isActive ? 'bg-[#E50914] text-white shadow-lg shadow-red-950/80 font-bold' : 'bg-white/5 hover:bg-white/10 text-slate-300 border border-white/5' }}">
                        
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-8 h-8 rounded-xl {{ $isActive ? 'bg-white text-red-600' : 'bg-white/10 text-slate-300' }} flex items-center justify-center text-xs font-black shrink-0">
                                {{ $ep->episode_number }}
                            </span>
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold truncate {{ $isActive ? 'text-white' : 'text-slate-200' }}">
                                    {{ $ep->title }}
                                </h4>
                                <div class="flex items-center gap-1.5 text-[10px] {{ $isActive ? 'text-white/80' : 'text-slate-400' }} mt-0.5">
                                    @if (!empty($epYt))
                                        <span class="font-bold flex items-center gap-0.5">
                                            <i data-lucide="youtube" class="w-3 h-3 text-red-400"></i> YouTube
                                        </span>
                                        <span>•</span>
                                    @endif
                                    <span>{{ $ep->formatted_duration }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0">
                            @if ($isActive)
                                <span class="px-2 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-black uppercase">
                                    Sedang Main
                                </span>
                            @else
                                <i data-lucide="play" class="w-4 h-4 text-slate-400 hover:text-white"></i>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- VIP Promotion Footer -->
            <div class="pt-3 mt-3 border-t border-white/10 flex items-center justify-between text-xs">
                <span class="text-slate-400 text-[11px]">Format 9:16 Pantas & Tanpa Gangguan</span>
                <a href="/subscribe" class="text-amber-400 hover:text-amber-300 font-bold flex items-center gap-1">
                    <i data-lucide="crown" class="w-3.5 h-3.5"></i>
                    <span>Pas VIP RM5</span>
                </a>
            </div>

        </div>

    </div>

    <!-- ======================================================== -->
    <!-- DESKTOP ARROW NAVIGATION CONTROLS (OUTSIDE PHONE FRAME)  -->
    <!-- ======================================================== -->
    <div class="hidden lg:flex flex-col items-center gap-3 ml-8 z-10">
        @if ($nextEpisode)
            <button @click="goNext()"
                    title="Episod Seterusnya (Swipe Up atau Scroll Down)"
                    class="w-12 h-12 rounded-full bg-white/10 hover:bg-red-600 text-white flex items-center justify-center border border-white/20 shadow-xl hover:scale-110 transition-all">
                <i data-lucide="chevron-up" class="w-6 h-6"></i>
            </button>
            <span class="text-[11px] font-bold text-slate-400">Ep {{ $nextEpisode->episode_number }}</span>
        @endif

        @if ($prevEpisode)
            <button @click="goPrev()"
                    title="Episod Sebelumnya (Swipe Down atau Scroll Up)"
                    class="w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center border border-white/20 shadow-xl hover:scale-110 transition-all mt-4">
                <i data-lucide="chevron-down" class="w-6 h-6"></i>
            </button>
            <span class="text-[11px] font-bold text-slate-400">Ep {{ $prevEpisode->episode_number }}</span>
        @endif

        <div class="text-[10px] text-slate-500 text-center mt-6 space-y-1">
            <p>⌨ Navigasi:</p>
            <p><kbd class="px-1 py-0.5 rounded bg-white/10 text-slate-300">↓</kbd> / <kbd class="px-1 py-0.5 rounded bg-white/10 text-slate-300">↑</kbd></p>
            <p>atau Scroll Mouse</p>
        </div>
    </div>

    <!-- Share Toast Notification -->
    <div id="shareToast" 
         class="fixed top-8 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-full bg-neutral-900/90 text-white text-xs font-bold border border-white/20 shadow-2xl transition-opacity duration-300 opacity-0 pointer-events-none">
        Pautan berjaya disalin ke papan keratan!
    </div>

    <!-- Alpine.js TikTok Drama Controller -->
    <script>
        function tiktokDramaPlayer(config) {
            return {
                dramaSlug: config.dramaSlug,
                dramaTitle: config.dramaTitle,
                currentEp: config.currentEpisodeNumber,
                totalEpisodes: config.totalEpisodes,
                nextUrl: config.nextUrl,
                prevUrl: config.prevUrl,
                nextEpisodeNumber: config.nextEpisodeNumber,
                prevEpisodeNumber: config.prevEpisodeNumber,
                youtubeId: config.youtubeId,
                videoUrl: config.videoUrl,
                isYouTube: config.isYouTube,

                // State
                episodeDrawerOpen: false,
                liked: false,
                likeCount: 12400 + Math.floor(Math.random() * 200),
                startY: 0,
                endY: 0,
                isWheelThrottled: false,

                get likeCountFormatted() {
                    return (this.likeCount / 1000).toFixed(1) + 'K';
                },

                init() {
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });

                    // Check if already liked in localStorage
                    const likeKey = `liked_${this.dramaSlug}_ep_${this.currentEp}`;
                    if (localStorage.getItem(likeKey) === 'true') {
                        this.liked = true;
                    }
                },

                openEpisodeDrawer() {
                    this.episodeDrawerOpen = true;
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                toggleLike() {
                    this.liked = !this.liked;
                    const likeKey = `liked_${this.dramaSlug}_ep_${this.currentEp}`;
                    if (this.liked) {
                        this.likeCount++;
                        localStorage.setItem(likeKey, 'true');
                    } else {
                        this.likeCount--;
                        localStorage.removeItem(likeKey);
                    }
                },

                shareEpisode() {
                    if (navigator.share) {
                        navigator.share({
                            title: this.dramaTitle + ' - Episod ' + this.currentEp,
                            url: window.location.href
                        }).catch(() => {});
                    } else {
                        navigator.clipboard.writeText(window.location.href);
                        const toast = document.getElementById('shareToast');
                        if (toast) {
                            toast.classList.remove('opacity-0');
                            setTimeout(() => toast.classList.add('opacity-0'), 2200);
                        }
                    }
                },

                // Gesture: Touch Swipe Up (Next) & Swipe Down (Prev)
                handleTouchStart(e) {
                    if (this.episodeDrawerOpen) return;
                    this.startY = e.touches[0].clientY;
                },

                handleTouchMove(e) {
                    if (this.episodeDrawerOpen) return;
                    this.endY = e.touches[0].clientY;
                },

                handleTouchEnd(e) {
                    if (this.episodeDrawerOpen) return;
                    if (!this.startY || !this.endY) return;

                    const deltaY = this.startY - this.endY;
                    const minSwipeDistance = 60; // 60px swipe threshold

                    if (deltaY > minSwipeDistance) {
                        // Swiped UP -> Next Episode
                        this.goNext();
                    } else if (deltaY < -minSwipeDistance) {
                        // Swiped DOWN -> Previous Episode
                        this.goPrev();
                    }

                    this.startY = 0;
                    this.endY = 0;
                },

                // Gesture: Mouse Wheel Scroll Up / Down
                handleWheel(e) {
                    if (this.episodeDrawerOpen || this.isWheelThrottled) return;

                    if (e.deltaY > 50) {
                        // Scroll DOWN -> Next Episode
                        this.isWheelThrottled = true;
                        this.goNext();
                        setTimeout(() => { this.isWheelThrottled = false; }, 800);
                    } else if (e.deltaY < -50) {
                        // Scroll UP -> Previous Episode
                        this.isWheelThrottled = true;
                        this.goPrev();
                        setTimeout(() => { this.isWheelThrottled = false; }, 800);
                    }
                },

                // Keyboard Arrow Down / Up
                handleKeyDown(e) {
                    if (this.episodeDrawerOpen) {
                        if (e.key === 'Escape') this.episodeDrawerOpen = false;
                        return;
                    }

                    if (e.key === 'ArrowDown' || e.key === 'j' || e.key === 'J') {
                        e.preventDefault();
                        this.goNext();
                    } else if (e.key === 'ArrowUp' || e.key === 'k' || e.key === 'K') {
                        e.preventDefault();
                        this.goPrev();
                    }
                },

                goNext() {
                    if (this.nextUrl) {
                        window.location.href = this.nextUrl;
                    } else {
                        this.openEpisodeDrawer();
                    }
                },

                goPrev() {
                    if (this.prevUrl) {
                        window.location.href = this.prevUrl;
                    }
                },

                toggleDirectPlay() {
                    const video = this.$refs.directVideo;
                    if (video) {
                        if (video.paused) {
                            video.play();
                        } else {
                            video.pause();
                        }
                    }
                }
            };
        }
    </script>
</body>
</html>
