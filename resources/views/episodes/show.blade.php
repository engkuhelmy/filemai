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

    <!-- YouTube IFrame Player API -->
    <script src="https://www.youtube.com/iframe_api"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        html, body {
            height: 100%;
            background-color: #08090b;
            touch-action: pan-x pan-y;
        }

        .vertical-theater {
            position: relative;
            width: 100%;
            max-width: 440px;
            aspect-ratio: 9 / 16;
            background: #000;
            box-shadow: 0 0 60px rgba(0, 0, 0, 0.9);
            border-radius: 1.5rem;
            overflow: hidden;
        }

        @media (max-width: 768px) {
            .vertical-theater {
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
<body class="bg-[#08090b] text-white select-none min-h-screen flex items-center justify-center p-0 md:p-6 font-sans"
      x-data="episodePlayer({
          episodeId: {{ (int)$episode->id }},
          currentEpisodeNumber: {{ (int)$episode->episode_number }},
          nextEpisodeUrl: {{ $nextEpisode ? json_encode(route('episodes.show', ['drama' => $drama->slug, 'episode' => $nextEpisode->episode_number])) : 'null' }},
          prevEpisodeUrl: {{ $prevEpisode ? json_encode(route('episodes.show', ['drama' => $drama->slug, 'episode' => $prevEpisode->episode_number])) : 'null' }},
          nextEpisodeNumber: {{ $nextEpisode ? (int)$nextEpisode->episode_number : 'null' }},
          nextEpisodeTitle: {{ $nextEpisode ? json_encode($nextEpisode->title) : 'null' }},
          youtubeVideoId: {{ json_encode($episode->youtube_video_id ?? '') }},
          isPremium: {{ $isPremium ? 'true' : 'false' }},
          hasAd: {{ ($ad && !$isPremium) ? 'true' : 'false' }},
          adDuration: {{ $ad ? (int)$ad->duration_seconds : 15 }}
      })">

    <!-- Responsive Layout: 9:16 Vertical Theater (Center/Left) + Episode Playlist (Right) -->
    <div class="w-full max-w-6xl mx-auto flex flex-col lg:flex-row items-center justify-center gap-8 h-full md:h-auto">

        <!-- ========================================================== -->
        <!-- 9:16 VERTICAL VIDEO PLAYER THEATER -->
        <!-- ========================================================== -->
        <div class="vertical-theater flex flex-col justify-between relative border border-white/10 shrink-0"
             id="theaterContainer"
             @touchstart="onTouchStart($event)"
             @touchend="onTouchEnd($event)">

            <!-- YouTube IFrame Container -->
            <div class="absolute inset-0 w-full h-full z-0 yt-iframe-container bg-black" id="ytContainer">
                <div id="ytPlayerFrame" class="w-full h-full"></div>
            </div>

            <!-- Top Header Overlay Controls -->
            <div class="relative z-20 p-4 bg-gradient-to-b from-black/95 via-black/60 to-transparent flex items-center justify-between pointer-events-auto">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('home') }}" 
                       class="w-9 h-9 rounded-full bg-black/60 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/20 transition-all border border-white/10 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <div class="flex flex-col min-w-0">
                        <span class="text-xs font-bold text-white truncate max-w-[170px] sm:max-w-[200px]">{{ $drama->title }}</span>
                        <span class="text-[11px] text-[#E50914] font-semibold truncate">{{ $episode->title }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <!-- YouTube Badge -->
                    <span class="px-2 py-0.5 rounded text-[10px] font-black tracking-wide bg-red-500/20 text-red-400 border border-red-500/30 flex items-center gap-1">
                        <svg class="w-3 h-3 fill-red-400" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                        <span>YT</span>
                    </span>

                    <!-- VIP / Ad-Free Status Badge -->
                    @if ($isPremium)
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 flex items-center gap-1">
                            <svg class="w-3 h-3 fill-amber-400" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                            VIP
                        </span>
                    @else
                        <a href="/subscribe" class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#E50914] hover:bg-[#F40612] text-white flex items-center gap-1 shadow-md transition-transform hover:scale-105">
                            <svg class="w-3 h-3 fill-white" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                            VIP RM5
                        </a>
                    @endif

                    <!-- Mobile Drawer Toggle -->
                    <button @click="drawerOpen = true" 
                            class="lg:hidden w-9 h-9 rounded-full bg-black/60 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/20 transition-all border border-white/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>

            <!-- Bottom Controls & Quick Nav -->
            <div class="relative z-20 p-4 bg-gradient-to-t from-black/95 via-black/75 to-transparent space-y-3 pointer-events-auto">
                <div class="flex items-center justify-between gap-3">
                    @if ($prevEpisode)
                        <a href="{{ route('episodes.show', ['drama' => $drama->slug, 'episode' => $prevEpisode->episode_number]) }}" 
                           class="flex-1 py-2.5 px-3 rounded-full bg-white/15 hover:bg-white/25 border border-white/10 flex items-center justify-center gap-1.5 text-xs font-bold transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                            <span>Episod {{ $prevEpisode->episode_number }}</span>
                        </a>
                    @else
                        <div class="flex-1 py-2.5 px-3 rounded-full bg-white/5 border border-white/5 text-center text-xs text-slate-500">
                            Episod Pertama
                        </div>
                    @endif

                    @if ($nextEpisode)
                        <button @click="triggerNextEpisode('{{ route('episodes.show', ['drama' => $drama->slug, 'episode' => $nextEpisode->episode_number]) }}')" 
                                class="flex-1 py-2.5 px-3 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white flex items-center justify-center gap-1.5 text-xs font-black shadow-lg shadow-red-950/80 transition-all">
                            <span>Episod {{ $nextEpisode->episode_number }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                        </button>
                    @else
                        <a href="{{ route('home') }}" 
                           class="flex-1 py-2.5 px-3 rounded-full bg-white/15 hover:bg-white/25 text-center text-xs font-bold text-white transition-colors">
                            Selesai Siri
                        </a>
                    @endif
                </div>
            </div>

            <!-- ========================================================== -->
            <!-- 2-SECOND AUTO-PLAY COUNTDOWN OVERLAY (PREMIUM / VIP ONLY) -->
            <!-- ========================================================== -->
            <div x-show="showCountdownOverlay" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
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
                    <span class="text-[11px] font-bold text-[#E50914] uppercase tracking-wider">VIP Auto-Advance</span>
                    <h3 class="text-base font-extrabold text-white" x-text="nextEpisodeTitle"></h3>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button @click="cancelAutoPlay()" class="px-5 py-2.5 rounded-full bg-white/10 hover:bg-white/20 text-xs font-bold text-slate-300 transition-colors">
                        Kekal di Sini
                    </button>
                    <button @click="proceedNextImmediately()" class="px-6 py-2.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-xs font-extrabold text-white shadow-lg flex items-center gap-1.5 transition-all">
                        <span>Main Sekarang</span>
                        <svg class="w-3.5 h-3.5 fill-white" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </button>
                </div>
            </div>

            <!-- ========================================================== -->
            <!-- STRICT INTERSTITIAL AD-GATE MODAL (#adModal FOR FREE USERS) -->
            <!-- ========================================================== -->
            @if ($ad && !$isPremium)
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
                        <span class="text-xs text-slate-300 truncate max-w-[150px]">{{ $ad->sponsor_name ?? 'Penaja Rasmi' }}</span>
                    </div>

                    <!-- Countdown Lock Badge -->
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#181818] border border-white/10 text-xs font-bold"
                         :class="adCountdown > 0 ? 'text-amber-400' : 'text-emerald-400 border-emerald-500/40'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
                               src="{{ $ad->video_url }}" 
                               autoplay 
                               playsinline 
                               muted
                               x-ref="adVideoPlayer">
                        </video>
                    </div>

                    <div class="space-y-1">
                        <h3 class="text-base font-extrabold text-white">{{ $ad->title }}</h3>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto">Tonton iklan {{ $ad->duration_seconds ?? 15 }} saat ini sehingga selesai untuk melangkah ke episod seterusnya.</p>
                    </div>

                    @if (!empty($ad->click_url))
                        <a href="{{ $ad->click_url }}" target="_blank" 
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white/10 hover:bg-white/20 text-[#E50914] text-xs font-bold border border-[#E50914]/40 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Lawati Laman Web Penaja
                        </a>
                    @endif
                </div>

                <!-- Ad Footer: Strictly Gated Action Button -->
                <div class="space-y-3 pt-4 border-t border-white/10">
                    <button @click="skipAdAndPlayNext()" 
                            :disabled="adCountdown > 0"
                            class="w-full py-3.5 rounded-full text-xs font-extrabold flex items-center justify-center gap-2 transition-all"
                            :class="adCountdown > 0 ? 'bg-[#222222] text-slate-500 cursor-not-allowed border border-white/5 opacity-80' : 'bg-[#E50914] hover:bg-[#F40612] text-white shadow-xl shadow-red-950/80 cursor-pointer'">
                        <template x-if="adCountdown > 0">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Menonton Iklan... (<span x-text="adCountdown"></span>s)</span>
                            </span>
                        </template>
                        <template x-if="adCountdown <= 0">
                            <span class="flex items-center gap-2">
                                <span>Langkau Iklan &amp; Tonton Episod Seterusnya</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </template>
                    </button>

                    <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-amber-300 font-medium">
                            <svg class="w-4 h-4 fill-amber-400" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                            <span>Langkau semua iklan dengan VIP RM5</span>
                        </div>
                        <a href="/subscribe" class="px-3 py-1 rounded-full bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 transition-colors">
                            Langgan
                        </a>
                    </div>
                </div>

            </div>
            @endif

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
                        <p class="text-xs text-slate-400">{{ $drama->title }}</p>
                    </div>
                    <button @click="drawerOpen = false" class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-slate-300 hover:text-white">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="overflow-y-auto space-y-2 pr-1 flex-1">
                    @foreach ($allEpisodes as $ep)
                        <div @click="handleEpisodeClick('{{ route('episodes.show', ['drama' => $drama->slug, 'episode' => $ep->episode_number]) }}', {{ (int)$ep->episode_number }})"
                             class="p-3 rounded-xl flex items-center justify-between gap-3 cursor-pointer transition-colors {{ ($ep->id == $episode->id) ? 'bg-[#E50914]/20 border border-[#E50914]/50 text-white' : 'bg-[#181818] hover:bg-[#222] text-slate-300 border border-white/5' }}">
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-lg {{ ($ep->id == $episode->id) ? 'bg-[#E50914] text-white' : 'bg-white/10 text-slate-400' }} flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ $ep->episode_number }}
                                </span>
                                <span class="text-xs font-medium truncate max-w-[200px]">{{ $ep->title }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-[10px] text-slate-400 shrink-0">
                                <span class="text-red-400 font-bold">YT</span>
                                @if ($ep->id == $episode->id)
                                    <span class="px-1.5 py-0.5 rounded bg-[#E50914] text-white font-bold">Sedang Main</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

        <!-- ========================================================== -->
        <!-- DESKTOP EPISODE PLAYLIST SIDEBAR (RIGHT COLUMN) -->
        <!-- ========================================================== -->
        <div class="hidden lg:flex flex-col w-80 max-h-[780px] rounded-3xl bg-[#121316] border border-white/10 p-5 shadow-2xl space-y-4">
            <div class="border-b border-white/10 pb-3 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#E50914]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        Senarai Episod
                    </h3>
                    <p class="text-[11px] text-slate-400">{{ count($allEpisodes) }} Episod Siri</p>
                </div>
                @if ($isPremium)
                    <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-bold border border-amber-500/30">
                        Ad-Free VIP
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 text-[10px] font-semibold">
                        Percuma (Iklan)
                    </span>
                @endif
            </div>

            <!-- Scrollable Episodes List with Intercepted Future Episodes -->
            <div class="overflow-y-auto space-y-2 pr-1 flex-1">
                @foreach ($allEpisodes as $ep)
                    <div @click="handleEpisodeClick('{{ route('episodes.show', ['drama' => $drama->slug, 'episode' => $ep->episode_number]) }}', {{ (int)$ep->episode_number }})"
                         class="p-3 rounded-2xl flex items-center justify-between gap-3 cursor-pointer transition-all {{ ($ep->id == $episode->id) ? 'bg-[#E50914]/20 border border-[#E50914]/50 text-white shadow-md' : 'bg-[#18191d] hover:bg-[#202227] text-slate-300 border border-white/5' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-8 h-8 rounded-xl {{ ($ep->id == $episode->id) ? 'bg-[#E50914] text-white shadow' : 'bg-white/5 text-slate-400' }} flex items-center justify-center text-xs font-extrabold shrink-0">
                                {{ $ep->episode_number }}
                            </span>
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-white truncate">{{ $ep->title }}</h4>
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5">
                                    <span class="text-red-400 font-bold">YouTube</span>
                                    <span>•</span>
                                    <span>{{ gmdate('i:s', $ep->duration ?? 60) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center">
                            @if ($ep->id == $episode->id)
                                <span class="w-6 h-6 rounded-full bg-[#E50914] text-white flex items-center justify-center">
                                    <svg class="w-3 h-3 fill-white ml-0.5" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                            @elseif (!$isPremium && $ep->episode_number > $episode->episode_number)
                                <span class="w-6 h-6 rounded-full bg-white/5 text-slate-400 flex items-center justify-center" title="Perlu tonton iklan">
                                    <svg class="w-3 h-3 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                </span>
                            @else
                                <span class="w-6 h-6 rounded-full bg-white/5 text-slate-400 flex items-center justify-center hover:bg-white/10">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Ad-Free Subscription Promo Callout -->
            @if (!$isPremium)
                <div class="pt-2 border-t border-white/10">
                    <a href="/subscribe" class="block p-3 rounded-2xl bg-gradient-to-r from-red-950/40 to-amber-950/40 border border-[#E50914]/30 hover:border-[#E50914]/60 transition-all text-center space-y-1">
                        <div class="flex items-center justify-center gap-1.5 text-xs font-extrabold text-amber-300">
                            <svg class="w-4 h-4 fill-amber-400" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                            <span>VIP Pas Tanpa Iklan</span>
                        </div>
                        <p class="text-[10px] text-slate-400">Tonton tanpa henti untuk hanya RM5/bulan</p>
                    </a>
                </div>
            @endif
        </div>

    </div>

    <!-- Alpine.js Integrated Video & Interstitial Ad Engine -->
    <script>
        function episodePlayer(config) {
            return {
                episodeId: config.episodeId,
                currentEpisodeNumber: config.currentEpisodeNumber,
                nextEpisodeUrl: config.nextEpisodeUrl,
                prevEpisodeUrl: config.prevEpisodeUrl,
                nextEpisodeNumber: config.nextEpisodeNumber,
                nextEpisodeTitle: config.nextEpisodeTitle || 'Episod Seterusnya',
                youtubeVideoId: config.youtubeVideoId,
                isPremium: config.isPremium,
                hasAd: config.hasAd,
                adDuration: config.adDuration || 15,

                drawerOpen: false,
                ytPlayer: null,

                // 2-Second Auto-Play Countdown (VIP)
                showCountdownOverlay: false,
                countdownSeconds: 2,
                countdownInterval: null,

                // Interstitial Ad Modal (Free Users)
                showAdModal: false,
                adCountdown: config.adDuration || 15,
                adTimerInterval: null,
                targetNextUrl: null,

                // Touch Gestures
                touchStartY: 0,
                touchStartX: 0,

                init() {
                    this.initYouTube();
                },

                initYouTube() {
                    if (!this.youtubeVideoId) return;

                    const mountYT = () => {
                        if (window.YT && window.YT.Player) {
                            this.ytPlayer = new YT.Player('ytPlayerFrame', {
                                videoId: this.youtubeVideoId,
                                playerVars: {
                                    playsinline: 1,
                                    rel: 0,
                                    modestbranding: 1,
                                    autoplay: 1,
                                    controls: 1,
                                    enablejsapi: 1,
                                    fs: 1,
                                    origin: window.location.origin
                                },
                                events: {
                                    'onReady': (event) => {
                                        event.target.playVideo();
                                    },
                                    'onStateChange': (event) => {
                                        // Listen for YT.PlayerState.ENDED (0)
                                        if (event.data === YT.PlayerState.ENDED) {
                                            this.onVideoEnded();
                                        }
                                    }
                                }
                            });
                        } else {
                            setTimeout(mountYT, 100);
                        }
                    };

                    mountYT();
                },

                // Video Ended Handler
                onVideoEnded() {
                    if (!this.nextEpisodeUrl) return;

                    if (this.isPremium || !this.hasAd) {
                        // VIP User: Auto advance with brief 2-second countdown
                        this.startAutoPlayCountdown();
                    } else {
                        // Free User: Strict Rewarded Interstitial Ad Gate
                        this.triggerNextEpisode(this.nextEpisodeUrl);
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
                            window.location.href = this.nextEpisodeUrl;
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
                    window.location.href = this.nextEpisodeUrl;
                },

                // Intercept Episode Clicks (Sidebar & Mobile Drawer)
                handleEpisodeClick(targetUrl, targetEpNumber) {
                    if (this.isPremium || !this.hasAd) {
                        window.location.href = targetUrl;
                        return;
                    }

                    // Free users clicking future episode: Must watch Ad first!
                    if (targetEpNumber > this.currentEpisodeNumber) {
                        this.drawerOpen = false;
                        this.triggerNextEpisode(targetUrl);
                    } else {
                        // Current or previous episode can be watched freely
                        window.location.href = targetUrl;
                    }
                },

                // Trigger Interstitial Ad Modal
                triggerNextEpisode(targetUrl) {
                    if (this.isPremium || !this.hasAd) {
                        window.location.href = targetUrl;
                        return;
                    }

                    // Pause YouTube playback if playing
                    if (this.ytPlayer && typeof this.ytPlayer.pauseVideo === 'function') {
                        try { this.ytPlayer.pauseVideo(); } catch (e) {}
                    }

                    this.targetNextUrl = targetUrl;
                    this.showAdModal = true;
                    this.adCountdown = this.adDuration;

                    this.$nextTick(() => {
                        const adVid = this.$refs.adVideoPlayer;
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
                    const dest = this.targetNextUrl || this.nextEpisodeUrl;
                    if (dest) {
                        window.location.href = dest;
                    } else {
                        this.showAdModal = false;
                    }
                },

                // Touch Swipe Handlers (Swipe Up -> Next Episode, Swipe Down -> Prev Episode)
                onTouchStart(e) {
                    this.touchStartY = e.touches[0].clientY;
                    this.touchStartX = e.touches[0].clientX;
                },

                onTouchEnd(e) {
                    const deltaY = e.changedTouches[0].clientY - this.touchStartY;
                    const deltaX = e.changedTouches[0].clientX - this.touchStartX;

                    if (Math.abs(deltaY) > 60 && Math.abs(deltaY) > Math.abs(deltaX)) {
                        if (deltaY < 0 && this.nextEpisodeUrl) {
                            this.triggerNextEpisode(this.nextEpisodeUrl);
                        } else if (deltaY > 0 && this.prevEpisodeUrl) {
                            window.location.href = this.prevEpisodeUrl;
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>
