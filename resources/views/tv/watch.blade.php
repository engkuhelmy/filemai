<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Filemai TV - {{ $episode->title }} ({{ $drama->title }})</title>

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
                        tv: {
                            bg: '#000000',
                            red: '#E50914',
                            redHover: '#F40612'
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

    <!-- HLS.js for Adaptive Bitrate Streaming on Smart TVs -->
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>

    <!-- YouTube IFrame API -->
    <script src="https://www.youtube.com/iframe_api"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }

        html, body {
            background-color: #000000;
            color: #ffffff;
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            -webkit-user-select: none;
            user-select: none;
        }

        .tv-focus:focus,
        .tv-focus:focus-visible {
            outline: none;
            transform: scale(1.08);
            box-shadow: 0 0 0 4px #E50914, 0 10px 25px rgba(229, 9, 20, 0.7);
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-black text-white w-screen h-screen relative flex items-center justify-center"
      x-data="tvPlayer({
          dramaId: {{ (int)$drama->id }},
          episodeId: {{ (int)$episode->id }},
          dramaSlug: '{{ $drama->slug }}',
          episodeNumber: {{ (int)$episode->episode_number }},
          totalEpisodes: {{ count($episodes) }},
          resumeSeconds: {{ ($progress && $progress->progress_seconds > 5 && !$progress->completed) ? (int)$progress->progress_seconds : 0 }},
          videoUrl: '{{ $episode->video_url ?? "" }}',
          youtubeId: '{{ $episode->effective_youtube_id ?? "" }}',
          isYouTube: {{ $episode->isYouTube() ? 'true' : 'false' }},
          nextUrl: {{ $nextEpisode ? json_encode(route('tv.watch', ['slug' => $drama->slug, 'episode' => $nextEpisode->episode_number])) : 'null' }},
          prevUrl: {{ $prevEpisode ? json_encode(route('tv.watch', ['slug' => $drama->slug, 'episode' => $prevEpisode->episode_number])) : 'null' }}
      })"
      @mousemove="resetControlsTimer()"
      @keydown.window="handleTvRemoteKey($event)">

    @php
        $ytId = $episode->effective_youtube_id;
        $isYt = !empty($ytId) || $episode->isYouTube();
        $bgPoster = $episode->thumbnail_url ?: ($drama->banner_url ?: $drama->poster_url);
    @endphp

    <!-- ======================================================== -->
    <!-- 10-FOOT TV VIDEO PLAYER CANVAS                           -->
    <!-- ======================================================== -->
    <div class="absolute inset-0 w-full h-full bg-black z-0 flex items-center justify-center overflow-hidden">
        @if (!empty($ytId))
            <div class="relative w-full h-full flex items-center justify-center">
                <iframe id="tvYtPlayer"
                        class="w-full h-full max-w-[100vw] max-h-[100vh]"
                        src="https://www.youtube-nocookie.com/embed/{{ $ytId }}?enablejsapi=1&autoplay=1&mute=0&controls=0&rel=0&modestbranding=1"
                        title="{{ $episode->title }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                </iframe>
            </div>
        @elseif (!empty($episode->video_url))
            <video x-ref="tvVideo"
                   id="tvVideo"
                   class="w-full h-full object-contain"
                   src="{{ $episode->video_url }}"
                   autoplay
                   playsinline
                   webkit-playsinline
                   x-webkit-airplay="allow"
                   airplay="allow"
                   poster="{{ $bgPoster }}"
                   @timeupdate="onTimeUpdate($event)"
                   @ended="onVideoEnded()">
            </video>
        @else
            <!-- Placeholder screen -->
            <div class="flex flex-col items-center justify-center p-8 text-center space-y-4">
                <i data-lucide="clock" class="w-16 h-16 text-amber-500 animate-pulse"></i>
                <h2 class="text-2xl font-black text-white">Episod Akan Datang</h2>
                <p class="text-slate-400 max-w-md">Episod ini sedang dalam pembikinan.</p>
                <a href="{{ route('tv.index') }}" class="tv-focus px-6 py-3 rounded-2xl bg-red-600 text-white font-bold" tabindex="0">
                    Kembali ke Menu TV
                </a>
            </div>
        @endif
    </div>

    <!-- ======================================================== -->
    <!-- TV PLAYER HUD OVERLAY (Fades after inactivity)           -->
    <!-- ======================================================== -->
    <div x-show="showControls"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 z-20 flex flex-col justify-between p-8 lg:p-12 bg-gradient-to-t from-black/90 via-black/20 to-black/80 pointer-events-none">

        <!-- Top Header Info -->
        <div class="flex items-center justify-between pointer-events-auto">
            <div class="flex items-center gap-4">
                <a href="{{ route('tv.index') }}" 
                   class="tv-focus w-12 h-12 rounded-2xl bg-white/15 hover:bg-red-600 flex items-center justify-center text-white transition-colors"
                   tabindex="0"
                   title="Kembali ke Menu Utama">
                    <i data-lucide="chevron-left" class="w-7 h-7"></i>
                </a>

                <div>
                    <span class="text-xs font-bold text-red-500 uppercase tracking-widest">
                        {{ $drama->title }} • EPISOD {{ $episode->episode_number }}
                    </span>
                    <h1 class="text-xl lg:text-2xl font-black text-white font-title">
                        {{ $episode->title }}
                    </h1>
                </div>
            </div>

            <!-- TV Mode Info Badge -->
            <div class="flex items-center gap-3">
                <button @click="playlistDrawerOpen = !playlistDrawerOpen"
                        class="tv-focus px-4 py-2.5 rounded-xl bg-white/15 hover:bg-white/25 text-white text-xs font-bold flex items-center gap-2"
                        tabindex="0">
                    <i data-lucide="layers" class="w-4 h-4 text-red-500"></i>
                    <span>Senarai Episod</span>
                </button>
                <div class="px-3 py-1.5 rounded-xl bg-red-600/30 text-red-400 border border-red-500/40 text-xs font-black">
                    1080p HD
                </div>
            </div>
        </div>

        <!-- Center Play/Pause Large Remote Indicator -->
        <div class="flex items-center justify-center pointer-events-none">
            <div x-show="isPaused" 
                 class="w-20 h-20 rounded-full bg-black/70 backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-2xl">
                <i data-lucide="pause" class="w-10 h-10 fill-white"></i>
            </div>
        </div>

        <!-- Bottom Controls & Progress Bar -->
        <div class="space-y-4 pointer-events-auto">
            <!-- Timeline Scrub Bar -->
            <div class="space-y-2">
                <div class="w-full h-2 rounded-full bg-white/20 overflow-hidden cursor-pointer relative"
                     @click="seekFromClick($event)">
                    <div class="h-full bg-[#E50914] rounded-full transition-all duration-100"
                         :style="'width: ' + progressPercent + '%;'"></div>
                </div>

                <div class="flex items-center justify-between text-xs font-mono text-slate-300">
                    <span x-text="formatTime(currentTime)">00:00</span>
                    <span x-text="formatTime(duration)">00:00</span>
                </div>
            </div>

            <!-- Remote Control Action Bar -->
            <div class="flex items-center justify-between pt-1">
                <div class="flex items-center gap-4">
                    <!-- Prev Ep -->
                    @if ($prevEpisode)
                        <a href="{{ route('tv.watch', ['slug' => $drama->slug, 'episode' => $prevEpisode->episode_number]) }}"
                           class="tv-focus px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-bold text-slate-300 hover:text-white flex items-center gap-1.5"
                           tabindex="0">
                            <i data-lucide="skip-back" class="w-4 h-4"></i>
                            <span>Ep {{ $prevEpisode->episode_number }}</span>
                        </a>
                    @endif

                    <!-- Play / Pause -->
                    <button @click="togglePlay()" 
                            class="tv-focus px-5 py-2.5 rounded-xl bg-[#E50914] hover:bg-red-700 text-white text-xs font-black flex items-center gap-2 shadow-lg"
                            tabindex="0">
                        <i data-lucide="play" class="w-4 h-4 fill-white" x-show="isPaused"></i>
                        <i data-lucide="pause" class="w-4 h-4" x-show="!isPaused"></i>
                        <span x-text="isPaused ? 'Main' : 'Jeda'">Main</span>
                    </button>

                    <!-- Next Ep -->
                    @if ($nextEpisode)
                        <a href="{{ route('tv.watch', ['slug' => $drama->slug, 'episode' => $nextEpisode->episode_number]) }}"
                           class="tv-focus px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-bold text-slate-300 hover:text-white flex items-center gap-1.5"
                           tabindex="0">
                            <span>Ep {{ $nextEpisode->episode_number }}</span>
                            <i data-lucide="skip-forward" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>

                <!-- D-Pad Hints -->
                <div class="hidden md:flex items-center gap-4 text-[11px] text-slate-400">
                    <span>◄ ► Imbas 10s</span>
                    <span>•</span>
                    <span>▲ Episod</span>
                    <span>•</span>
                    <span>ENTER Main / Jeda</span>
                </div>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- RESUME PLAYBACK POPUP ("SAMBUNG TONTON DARI MM:SS?")      -->
    <!-- ======================================================== -->
    <div x-show="showResumePopup"
         x-cloak
         x-transition
         class="absolute top-12 left-1/2 -translate-x-1/2 z-40 p-4 rounded-2xl bg-[#0f121c]/95 backdrop-blur-xl border border-red-500/50 shadow-2xl flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-red-600/20 text-red-500 flex items-center justify-center shrink-0">
            <i data-lucide="history" class="w-6 h-6"></i>
        </div>
        <div>
            <h4 class="text-sm font-black text-white">Sambung Tontonan?</h4>
            <p class="text-xs text-slate-300">Daripada minit <strong class="text-red-400" x-text="formatTime(resumeSeconds)">00:00</strong></p>
        </div>
        <div class="flex items-center gap-2 pl-2">
            <button @click="dismissResume()" class="tv-focus px-3 py-1.5 rounded-xl bg-white/10 text-xs font-bold text-slate-300" tabindex="0">
                Mula Semula
            </button>
            <button @click="applyResume()" class="tv-focus px-4 py-1.5 rounded-xl bg-red-600 text-xs font-black text-white" tabindex="0">
                Sambung
            </button>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- AUTO-PLAY NEXT EPISODE COUNTDOWN OVERLAY                 -->
    <!-- ======================================================== -->
    @if ($nextEpisode)
        <div x-show="showNextCountdown"
             x-cloak
             x-transition
             class="absolute inset-0 z-50 bg-black/90 backdrop-blur-md flex flex-col items-center justify-center p-8 text-center space-y-4">
            <span class="px-3 py-1 rounded-full bg-red-600/20 text-red-400 border border-red-500/30 text-xs font-black uppercase">
                Episod Seterusnya
            </span>
            <h2 class="text-3xl font-black text-white font-title">
                Episod {{ $nextEpisode->episode_number }}: {{ $nextEpisode->title }}
            </h2>
            <p class="text-slate-300 text-sm">
                Bermula secara automatik dalam <strong class="text-red-500 text-lg font-mono" x-text="countdownSeconds">5</strong> saat...
            </p>
            <div class="flex items-center gap-4 pt-2">
                <button @click="cancelCountdown()" class="tv-focus px-5 py-2.5 rounded-xl bg-white/15 text-sm font-bold text-white" tabindex="0">
                    Batal
                </button>
                <a href="{{ route('tv.watch', ['slug' => $drama->slug, 'episode' => $nextEpisode->episode_number]) }}"
                   class="tv-focus px-6 py-2.5 rounded-xl bg-[#E50914] text-sm font-black text-white" tabindex="0" autofocus>
                    Tonton Sekarang
                </a>
            </div>
        </div>
    @endif

    <!-- ======================================================== -->
    <!-- SLIDE-OVER PLAYLIST DRAWER (D-PAD NAVIGABLE)             -->
    <!-- ======================================================== -->
    <div x-show="playlistDrawerOpen"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-x-full opacity-0"
         x-transition:enter-end="translate-x-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="translate-x-full opacity-0"
         class="absolute top-0 right-0 bottom-0 w-80 lg:w-96 z-40 bg-[#0e1017]/95 backdrop-blur-2xl border-l border-white/10 p-6 flex flex-col shadow-2xl pointer-events-auto"
         @click.outside="playlistDrawerOpen = false">

        <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-4">
            <h3 class="text-base font-extrabold text-white">Senarai Episod</h3>
            <button @click="playlistDrawerOpen = false" class="tv-focus p-2 rounded-xl bg-white/10 text-slate-300 hover:text-white" tabindex="0">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto space-y-2 no-scrollbar pr-1">
            @foreach ($episodes as $ep)
                @php $isActive = ($ep->id === $episode->id); @endphp
                <a href="{{ route('tv.watch', ['slug' => $drama->slug, 'episode' => $ep->episode_number]) }}"
                   class="tv-focus block p-3 rounded-2xl transition-all {{ $isActive ? 'bg-[#E50914] text-white font-bold' : 'bg-white/5 hover:bg-white/10 text-slate-300' }}"
                   tabindex="0">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl {{ $isActive ? 'bg-white text-red-600' : 'bg-white/10 text-slate-300' }} flex items-center justify-center text-xs font-black shrink-0">
                            {{ $ep->episode_number }}
                        </span>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold truncate">{{ $ep->title }}</h4>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $ep->formatted_duration }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Alpine.js TV Player Controller -->
    <script>
        function tvPlayer(config) {
            return {
                dramaId: config.dramaId,
                episodeId: config.episodeId,
                dramaSlug: config.dramaSlug,
                episodeNumber: config.episodeNumber,
                resumeSeconds: config.resumeSeconds || 0,
                videoUrl: config.videoUrl,
                youtubeId: config.youtubeId,
                isYouTube: config.isYouTube,
                nextUrl: config.nextUrl,
                prevUrl: config.prevUrl,

                // States
                showControls: true,
                controlsTimeout: null,
                isPaused: false,
                currentTime: 0,
                duration: 60,
                progressPercent: 0,
                showResumePopup: false,
                playlistDrawerOpen: false,
                showNextCountdown: false,
                countdownSeconds: 5,
                countdownTimer: null,
                lastProgressReported: 0,

                init() {
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });

                    // Resume popup check
                    if (this.resumeSeconds > 5) {
                        this.showResumePopup = true;
                        setTimeout(() => { this.showResumePopup = false; }, 10000);
                    }

                    // Setup adaptive streaming if HLS
                    this.setupHls();

                    // Auto hide controls after 4 seconds
                    this.resetControlsTimer();

                    // Progress reporting timer (every 8s)
                    setInterval(() => {
                        this.saveProgress();
                    }, 8000);
                },

                setupHls() {
                    const video = this.$refs.tvVideo;
                    if (video && this.videoUrl && this.videoUrl.includes('.m3u8')) {
                        if (window.Hls && window.Hls.isSupported()) {
                            const hls = new Hls();
                            hls.loadSource(this.videoUrl);
                            hls.attachMedia(video);
                            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                                video.play().catch(() => {});
                            });
                        }
                    }
                },

                resetControlsTimer() {
                    this.showControls = true;
                    if (this.controlsTimeout) clearTimeout(this.controlsTimeout);
                    this.controlsTimeout = setTimeout(() => {
                        if (!this.isPaused && !this.playlistDrawerOpen) {
                            this.showControls = false;
                        }
                    }, 4000);
                },

                togglePlay() {
                    const video = this.$refs.tvVideo;
                    if (video) {
                        if (video.paused) {
                            video.play();
                            this.isPaused = false;
                        } else {
                            video.pause();
                            this.isPaused = true;
                        }
                    }
                    this.resetControlsTimer();
                },

                applyResume() {
                    const video = this.$refs.tvVideo;
                    if (video && this.resumeSeconds > 0) {
                        video.currentTime = this.resumeSeconds;
                        video.play().catch(() => {});
                    }
                    this.showResumePopup = false;
                },

                dismissResume() {
                    this.showResumePopup = false;
                },

                onTimeUpdate(e) {
                    const video = e.target;
                    this.currentTime = Math.floor(video.currentTime);
                    if (video.duration) {
                        this.duration = Math.floor(video.duration);
                        this.progressPercent = (this.currentTime / this.duration) * 100;
                    }
                },

                onVideoEnded() {
                    this.saveProgress(true);
                    if (this.nextUrl) {
                        this.triggerNextCountdown();
                    }
                },

                triggerNextCountdown() {
                    this.showNextCountdown = true;
                    this.countdownSeconds = 5;
                    this.countdownTimer = setInterval(() => {
                        this.countdownSeconds--;
                        if (this.countdownSeconds <= 0) {
                            clearInterval(this.countdownTimer);
                            window.location.href = this.nextUrl;
                        }
                    }, 1000);
                },

                cancelCountdown() {
                    if (this.countdownTimer) clearInterval(this.countdownTimer);
                    this.showNextCountdown = false;
                },

                seekFromClick(e) {
                    const bar = e.currentTarget;
                    const rect = bar.getBoundingClientRect();
                    const clickX = e.clientX - rect.left;
                    const percent = clickX / rect.width;
                    const newTime = percent * this.duration;
                    const video = this.$refs.tvVideo;
                    if (video) {
                        video.currentTime = newTime;
                    }
                },

                saveProgress(isCompleted = false) {
                    if (this.currentTime <= 0 || this.currentTime === this.lastProgressReported) return;
                    this.lastProgressReported = this.currentTime;

                    fetch('/api/tv/playback/progress', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            drama_id: this.dramaId,
                            episode_id: this.episodeId,
                            progress_seconds: this.currentTime,
                            duration_seconds: this.duration || 60
                        })
                    }).catch(() => {});
                },

                formatTime(sec) {
                    const s = parseInt(sec, 10) || 0;
                    const mins = Math.floor(s / 60);
                    const remainder = s % 60;
                    return (mins < 10 ? '0' : '') + mins + ':' + (remainder < 10 ? '0' : '') + remainder;
                },

                handleTvRemoteKey(e) {
                    this.resetControlsTimer();
                    const key = e.key;

                    if (key === ' ' || key === 'Enter') {
                        if (document.activeElement.tagName === 'BUTTON' || document.activeElement.tagName === 'A') {
                            return; // Let native button click happen
                        }
                        e.preventDefault();
                        this.togglePlay();
                    } else if (key === 'ArrowRight') {
                        // Fast forward 10s
                        const video = this.$refs.tvVideo;
                        if (video) video.currentTime = Math.min(video.duration, video.currentTime + 10);
                    } else if (key === 'ArrowLeft') {
                        // Rewind 10s
                        const video = this.$refs.tvVideo;
                        if (video) video.currentTime = Math.max(0, video.currentTime - 10);
                    } else if (key === 'ArrowUp') {
                        // Open Episode drawer
                        this.playlistDrawerOpen = true;
                    } else if (key === 'ArrowDown') {
                        this.playlistDrawerOpen = false;
                    } else if (key === 'Escape' || key === 'Backspace') {
                        if (this.playlistDrawerOpen) {
                            this.playlistDrawerOpen = false;
                        } else {
                            window.location.href = '{{ route("tv.index") }}';
                        }
                    }
                }
            };
        }
    </script>
</body>
</html>
