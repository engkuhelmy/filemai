@props([
    'videoType' => null,           // 'storage' | 'youtube' (if null, auto-detected from videoUrl)
    'videoUrl' => '',              // Storage URL (MP4/WebM) or YouTube URL
    'youtubeId' => null,           // 11-char YouTube ID (optional, auto-extracted if empty)
    'poster' => '',                // Thumbnail/poster image URL
    'title' => 'Episod Drama',     // Episode title
    'dramaTitle' => 'Drama Siri',  // Drama title
    'aspectRatio' => '9:16',       // '9:16' or '16:9'
    'nextEpisodeUrl' => null,      // URL for next episode
    'prevEpisodeUrl' => null,      // URL for previous episode
    'nextEpisodeTitle' => null,    // Title of next episode
    'nextEpisodeNumber' => null,   // Number of next episode
    'isPremium' => false,          // VIP status boolean
    'ad' => null,                  // Ad object/array with video_url, duration_seconds, sponsor_name, click_url
    'adDuration' => 15,            // Default ad countdown in seconds (10 - 20s)
    'autoplay' => true,            // Auto-play media on load
])

@php
    // Auto-detect source type if not explicitly provided
    $effectiveType = $videoType;
    if (empty($effectiveType)) {
        $effectiveType = \App\Helpers\MediaHelper::detectSource($videoUrl);
    }
    $effectiveType = ($effectiveType === 'youtube') ? 'youtube' : 'storage';

    // Auto-extract YouTube ID if type is youtube
    $effectiveYtId = $youtubeId;
    if ($effectiveType === 'youtube' && empty($effectiveYtId)) {
        $effectiveYtId = \App\Helpers\MediaHelper::extractYouTubeId($videoUrl);
    }

    // Default poster fallback
    $effectivePoster = $poster;
    if (empty($effectivePoster) && $effectiveType === 'youtube' && !empty($effectiveYtId)) {
        $effectivePoster = \App\Helpers\MediaHelper::getYouTubeThumbnail($effectiveYtId);
    }

    $uniqueId = 'player_' . substr(md5($videoUrl . '_' . $effectiveYtId . '_' . microtime()), 0, 8);
    $adSeconds = $ad ? ((int)($ad->duration_seconds ?? $ad['duration_seconds'] ?? $adDuration)) : $adDuration;
    $adSeconds = max(5, min(30, $adSeconds)); // clamp between 5 and 30s
@endphp

<div id="{{ $uniqueId }}"
     class="universal-video-player-component relative w-full flex items-center justify-center select-none"
     x-data="universalVideoPlayer({
         id: '{{ $uniqueId }}',
         videoType: '{{ $effectiveType }}',
         videoUrl: {{ json_encode($videoUrl) }},
         youtubeId: {{ json_encode($effectiveYtId) }},
         poster: {{ json_encode($effectivePoster) }},
         aspectRatio: '{{ $aspectRatio }}',
         nextEpisodeUrl: {{ $nextEpisodeUrl ? json_encode($nextEpisodeUrl) : 'null' }},
         prevEpisodeUrl: {{ $prevEpisodeUrl ? json_encode($prevEpisodeUrl) : 'null' }},
         nextEpisodeTitle: {{ $nextEpisodeTitle ? json_encode($nextEpisodeTitle) : json_encode('Episod Seterusnya') }},
         nextEpisodeNumber: {{ $nextEpisodeNumber ? (int)$nextEpisodeNumber : 'null' }},
         isPremium: {{ $isPremium ? 'true' : 'false' }},
         hasAd: {{ ($ad && !$isPremium) ? 'true' : 'false' }},
         adDuration: {{ $adSeconds }},
         autoplay: {{ $autoplay ? 'true' : 'false' }}
     })"
     x-init="initPlayer()">

    <!-- Player Viewport Container -->
    <div class="theater-viewport relative w-full overflow-hidden bg-black shadow-2xl transition-all duration-300
                {{ $aspectRatio === '9:16' ? 'max-w-[440px] aspect-[9/16] rounded-2xl md:rounded-3xl border border-white/10' : 'max-w-4xl aspect-video rounded-2xl border border-white/10' }}
                h-[100dvh] md:h-auto max-h-[100dvh]"
         @touchstart="handleTouchStart($event)"
         @touchend="handleTouchEnd($event)">

        <!-- ======================================================== -->
        <!-- SOURCE 1: YOUTUBE IFRAME PLAYER API -->
        <!-- ======================================================== -->
        <div x-show="videoType === 'youtube'" 
             class="absolute inset-0 w-full h-full z-10 yt-iframe-container bg-black overflow-hidden flex items-center justify-center pointer-events-auto"
             {{ $effectiveType !== 'youtube' ? 'style="display: none;"' : '' }}
             x-ref="ytWrapper">
            @if (!empty($effectiveYtId))
                <iframe id="ytPlayer_{{ $uniqueId }}" 
                        class="w-full h-full border-0 absolute inset-0"
                        src="https://www.youtube-nocookie.com/embed/{{ $effectiveYtId }}?enablejsapi=1&autoplay={{ $autoplay ? 1 : 0 }}&mute=1&playsinline=1&controls=1&rel=0&modestbranding=1"
                        title="{{ $title }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allowfullscreen>
                </iframe>
            @else
                <div id="ytPlayer_{{ $uniqueId }}" class="w-full h-full bg-black"></div>
            @endif
        </div>

        <!-- ======================================================== -->
        <!-- SOURCE 2: DIRECT STORAGE HTML5 VIDEO (MP4 / WebM / CDN) -->
        <!-- ======================================================== -->
        <div x-show="videoType === 'storage'" 
             {{ $effectiveType === 'youtube' ? 'style="display: none;"' : '' }}
             class="absolute inset-0 w-full h-full z-0 bg-black overflow-hidden">
            <video x-ref="html5Video"
                   id="html5Video_{{ $uniqueId }}"
                   class="w-full h-full object-cover cursor-pointer"
                   playsinline
                   webkit-playsinline
                   preload="auto"
                   muted
                   @click="togglePlay"
                   @play="isPlaying = true"
                   @pause="isPlaying = false"
                   @ended="handleMediaEnded"
                   @timeupdate="onHtml5TimeUpdate"
                   @volumechange="isMuted = $refs.html5Video.muted"
                   poster="{{ $effectivePoster }}">
                @if ($effectiveType === 'storage' && !empty($videoUrl))
                    <source src="{{ $videoUrl }}" type="video/mp4">
                    <source src="{{ $videoUrl }}" type="video/webm">
                @endif
                Pelayar anda tidak menyokong tag video HTML5.
            </video>
        </div>

        <!-- Top Header Overlay Controls -->
        <div class="absolute top-0 inset-x-0 z-20 p-4 bg-gradient-to-b from-black/95 via-black/60 to-transparent flex items-center justify-between pointer-events-auto">
            <div class="flex items-center gap-3 min-w-0">
                @if ($prevEpisodeUrl)
                    <a href="{{ $prevEpisodeUrl }}" 
                       class="w-8 h-8 rounded-full bg-black/60 backdrop-blur-md flex items-center justify-center text-white hover:bg-white/20 transition-all border border-white/10 shrink-0"
                       title="Episod Sebelumnya">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif
                <div class="flex flex-col min-w-0">
                    <span class="text-xs font-bold text-white truncate max-w-[160px] sm:max-w-[220px]">{{ $dramaTitle }}</span>
                    <span class="text-[11px] text-[#E50914] font-semibold truncate">{{ $title }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <!-- Source Badge -->
                <span class="px-2 py-0.5 rounded text-[10px] font-black tracking-wide flex items-center gap-1"
                      :class="videoType === 'youtube' ? 'bg-red-500/20 text-red-400 border border-red-500/30' : 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/30'">
                    <template x-if="videoType === 'youtube'">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3 fill-red-400" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            <span>YouTube</span>
                        </span>
                    </template>
                    <template x-if="videoType === 'storage'">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3 fill-cyan-400" viewBox="0 0 24 24"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm0 2v12h16V6H4zm2 2h3v3H6V8zm0 5h3v3H6v-3zm5-5h7v3h-7V8zm0 5h7v3h-7v-3z"/></svg>
                            <span>HD Storage</span>
                        </span>
                    </template>
                </span>

                <!-- VIP Status Badge -->
                @if ($isPremium)
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 flex items-center gap-1">
                        <svg class="w-3 h-3 fill-amber-400" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                        VIP
                    </span>
                @else
                    <a href="/subscribe" 
                       class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-[#E50914] hover:bg-[#F40612] text-white flex items-center gap-1 shadow-md transition-transform hover:scale-105">
                        <svg class="w-3 h-3 fill-white" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                        VIP RM5
                    </a>
                @endif
            </div>
        </div>

        <!-- Floating Unmute Audio Pill Badge (Handles Browser Autoplay Policies) -->
        <button x-show="isMuted"
                @click.stop="unmute()"
                class="absolute top-16 right-4 z-30 px-3 py-1.5 rounded-full bg-black/85 backdrop-blur-md border border-red-500/40 text-white text-[11px] font-bold flex items-center gap-1.5 shadow-2xl hover:bg-black transition-all animate-pulse"
                style="display: none;">
            <svg class="w-4 h-4 text-[#E50914]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
            <span>Tekan untuk Bunyi</span>
        </button>

        <!-- Center Big Play Indicator (When paused) -->
        <div class="absolute inset-0 z-10 flex items-center justify-center cursor-pointer pointer-events-auto"
             x-show="!isPlaying && isReady"
             @click.stop="togglePlay"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
             style="display: none;">
            <div class="w-16 h-16 rounded-full bg-black/75 backdrop-blur-md border border-white/20 flex items-center justify-center text-white shadow-2xl scale-105 hover:scale-120 transition-transform">
                <svg class="w-8 h-8 fill-white ml-1" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
            </div>
        </div>

        <!-- Bottom Controls & Navigation -->
        <div class="absolute bottom-0 inset-x-0 z-20 p-4 bg-gradient-to-t from-black/95 via-black/75 to-transparent space-y-3 pointer-events-auto">
            
            <!-- Seekbar for Storage HTML5 Video -->
            <template x-if="videoType === 'storage'">
                <div class="space-y-1">
                    <div class="relative w-full h-1.5 bg-white/20 rounded-full cursor-pointer overflow-hidden" 
                         @click="seekHtml5($event)">
                        <div class="h-full bg-[#E50914] rounded-full transition-all duration-100" 
                             :style="`width: ${progressPercent}%`"></div>
                    </div>
                    <div class="flex items-center justify-between text-[10px] text-slate-400 font-medium">
                        <span x-text="currentTimeFormatted">00:00</span>
                        <span x-text="durationFormatted">00:00</span>
                    </div>
                </div>
            </template>

            <!-- Navigation Action Buttons (Prev / Next) -->
            <div class="flex items-center justify-between gap-3 pt-1">
                @if ($prevEpisodeUrl)
                    <a href="{{ $prevEpisodeUrl }}" 
                       class="flex-1 py-2.5 px-3 rounded-full bg-white/15 hover:bg-white/25 border border-white/10 flex items-center justify-center gap-1.5 text-xs font-bold transition-colors text-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                        <span>Sebelumnya</span>
                    </a>
                @else
                    <div class="flex-1 py-2.5 px-3 rounded-full bg-white/5 border border-white/5 text-center text-xs text-slate-500">
                        Episod Pertama
                    </div>
                @endif

                @if ($nextEpisodeUrl)
                    <button type="button"
                            @click="triggerNextEpisode('{{ $nextEpisodeUrl }}')" 
                            class="flex-1 py-2.5 px-3 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white flex items-center justify-center gap-1.5 text-xs font-black shadow-lg shadow-red-950/80 transition-all">
                        <span>Episod Seterusnya</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                    </button>
                @else
                    <a href="/" 
                       class="flex-1 py-2.5 px-3 rounded-full bg-white/15 hover:bg-white/25 text-center text-xs font-bold text-white transition-colors">
                        Selesai Siri
                    </a>
                @endif
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2-SECOND AUTO-PLAY COUNTDOWN OVERLAY (PREMIUM VIP ONLY)   -->
        <!-- ======================================================== -->
        <div x-show="showVipOverlay" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="absolute inset-0 z-40 bg-black/92 backdrop-blur-md flex flex-col items-center justify-center p-6 text-center space-y-4"
             style="display: none;">
            
            <div class="relative w-20 h-20 flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90">
                    <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="4" class="text-white/20" fill="transparent"/>
                    <circle cx="40" cy="40" r="34" stroke="currentColor" stroke-width="4" class="text-[#E50914] transition-all duration-1000 ease-linear"
                            fill="transparent"
                            stroke-dasharray="213.6"
                            :stroke-dashoffset="213.6 * (1 - (vipCountdown / 2))"/>
                </svg>
                <span class="absolute text-2xl font-black text-white" x-text="vipCountdown">2</span>
            </div>

            <div class="space-y-1 max-w-xs">
                <span class="text-[11px] font-bold text-[#E50914] uppercase tracking-wider">VIP Auto-Advance</span>
                <h3 class="text-base font-extrabold text-white" x-text="nextEpisodeTitle"></h3>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="button" @click="cancelVipAdvance()" class="px-5 py-2.5 rounded-full bg-white/10 hover:bg-white/20 text-xs font-bold text-slate-300 transition-colors">
                    Kekal di Sini
                </button>
                <button type="button" @click="proceedNextImmediately()" class="px-6 py-2.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-xs font-extrabold text-white shadow-lg flex items-center gap-1.5 transition-all">
                    <span>Main Sekarang</span>
                    <svg class="w-3.5 h-3.5 fill-white" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- STRICT INTERSTITIAL AD-GATE MODAL (FOR FREE USERS)       -->
        <!-- ======================================================== -->
        @if ($ad && !$isPremium)
        <div id="interstitialAdModal"
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
                        Iklan Penaja
                    </span>
                    <span class="text-xs text-slate-300 truncate max-w-[150px]">
                        {{ $ad->sponsor_name ?? $ad['sponsor_name'] ?? 'Penaja Rasmi' }}
                    </span>
                </div>

                <!-- Countdown Timer Pill Badge -->
                <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#181818] border text-xs font-bold transition-colors"
                     :class="adCountdown > 0 ? 'text-amber-400 border-amber-500/30' : 'text-emerald-400 border-emerald-500/50 bg-emerald-950/40'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <template x-if="adCountdown > 0">
                        <span>Kunci dibuka: <strong class="text-white ml-0.5" x-text="adCountdown"></strong>s</span>
                    </template>
                    <template x-if="adCountdown <= 0">
                        <span class="font-black text-emerald-400">Sedia Ditonton!</span>
                    </template>
                </div>
            </div>

            <!-- Ad Media Player Container -->
            <div class="my-auto space-y-4 text-center">
                <div class="relative w-full aspect-video rounded-2xl overflow-hidden bg-[#181818] border border-white/10 shadow-2xl">
                    <video id="adVideo_{{ $uniqueId }}" 
                           class="w-full h-full object-cover" 
                           src="{{ $ad->video_url ?? $ad['video_url'] ?? '/uploads/videos/ad1.mp4' }}" 
                           autoplay 
                           playsinline 
                           muted
                           x-ref="adVideoPlayer">
                    </video>
                </div>

                <div class="space-y-1">
                    <h3 class="text-base font-extrabold text-white">
                        {{ $ad->title ?? $ad['title'] ?? 'Tonton Iklan Untuk Teruskan' }}
                    </h3>
                    <p class="text-xs text-slate-400 max-w-xs mx-auto">
                        Tonton iklan <span class="text-white font-bold" x-text="adDuration"></span> saat ini sehingga selesai untuk membuka episod seterusnya secara percuma.
                    </p>
                </div>

                @if (!empty($ad->click_url ?? $ad['click_url'] ?? null))
                    <a href="{{ $ad->click_url ?? $ad['click_url'] }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white/10 hover:bg-white/20 text-[#E50914] text-xs font-bold border border-[#E50914]/40 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Lawati Laman Web Penaja
                    </a>
                @endif
            </div>

            <!-- Ad Footer Action Button -->
            <div class="space-y-3 pt-4 border-t border-white/10">
                <button type="button"
                        @click="skipAdAndPlayNext()" 
                        :disabled="adCountdown > 0"
                        class="w-full py-3.5 rounded-full text-xs font-extrabold flex items-center justify-center gap-2 transition-all"
                        :class="adCountdown > 0 ? 'bg-[#222222] text-slate-500 cursor-not-allowed border border-white/5 opacity-80' : 'bg-[#E50914] hover:bg-[#F40612] text-white shadow-xl shadow-red-950/80 cursor-pointer animate-bounce'">
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

    </div>
</div>

<!-- ======================================================== -->
<!-- ALPINE.JS UNIVERSAL VIDEO PLAYER ENGINE SCRIPT           -->
<!-- ======================================================== -->
<script>
    // Ensure YouTube IFrame API script tag is appended once globally
    if (!window._ytApiLoading && !window.YT) {
        window._ytApiLoading = true;
        const tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }

    function universalVideoPlayer(config) {
        return {
            id: config.id,
            videoType: config.videoType,
            videoUrl: config.videoUrl,
            youtubeId: config.youtubeId,
            poster: config.poster,
            aspectRatio: config.aspectRatio,
            nextEpisodeUrl: config.nextEpisodeUrl,
            prevEpisodeUrl: config.prevEpisodeUrl,
            nextEpisodeTitle: config.nextEpisodeTitle,
            nextEpisodeNumber: config.nextEpisodeNumber,
            isPremium: config.isPremium,
            hasAd: config.hasAd,
            adDuration: config.adDuration || 15,
            autoplay: config.autoplay,

            // Unified Player State
            isReady: false,
            isPlaying: false,
            isMuted: true,
            progressPercent: 0,
            currentTimeFormatted: '00:00',
            durationFormatted: '00:00',

            // Player instances
            ytPlayer: null,

            // VIP Auto-play Overlay (2s)
            showVipOverlay: false,
            vipCountdown: 2,
            vipTimer: null,

            // Interstitial Ad Modal (Free users)
            showAdModal: false,
            adCountdown: config.adDuration || 15,
            adTimer: null,
            pendingNextUrl: null,

            // Touch Swipe Gestures
            touchStartY: 0,
            touchStartX: 0,

            initPlayer() {
                this.$nextTick(() => {
                    if (this.videoType === 'youtube') {
                        this.mountYouTubePlayer();
                    } else {
                        this.mountHtml5Player();
                    }
                });
            },

            mountYouTubePlayer() {
                if (!this.youtubeId) {
                    console.warn('[UniversalPlayer] No YouTube ID found for episode.');
                    return;
                }

                const mountId = 'ytPlayer_' + this.id;

                // 1. PostMessage listener fallback (immediate without waiting for API)
                window.addEventListener('message', (e) => {
                    try {
                        const data = typeof e.data === 'string' ? JSON.parse(e.data) : e.data;
                        if (data && data.event === 'onStateChange') {
                            if (data.info === 1) { this.isPlaying = true; }
                            else if (data.info === 2) { this.isPlaying = false; }
                            else if (data.info === 0) { this.isPlaying = false; this.handleMediaEnded(); }
                        }
                    } catch (_) {}
                });

                // 2. Bind official YT.Player API to existing iframe
                const checkAndInit = () => {
                    if (window.YT && window.YT.Player) {
                        try {
                            this.ytPlayer = new YT.Player(mountId, {
                                events: {
                                    'onReady': (event) => {
                                        this.isReady = true;
                                        this.isMuted = true;
                                        if (this.autoplay) {
                                            try { event.target.playVideo(); } catch (_) {}
                                            this.isPlaying = true;
                                        }
                                    },
                                    'onStateChange': (event) => {
                                        if (event.data === YT.PlayerState.PLAYING) {
                                            this.isPlaying = true;
                                        } else if (event.data === YT.PlayerState.PAUSED) {
                                            this.isPlaying = false;
                                        } else if (event.data === YT.PlayerState.ENDED) {
                                            this.isPlaying = false;
                                            this.handleMediaEnded();
                                        }
                                    }
                                }
                            });
                        } catch (err) {
                            console.warn('[UniversalPlayer] YouTube mount notice:', err);
                        }
                    } else {
                        setTimeout(checkAndInit, 100);
                    }
                };

                checkAndInit();
            },

            mountHtml5Player() {
                const video = this.$refs.html5Video;
                if (!video) return;

                video.muted = true;
                this.isMuted = true;
                this.isReady = true;

                if (this.autoplay) {
                    const playPromise = video.play();
                    if (playPromise !== undefined) {
                        playPromise.then(() => {
                            this.isPlaying = true;
                        }).catch(() => {
                            // Browser prevented unmuted or initial play; stays ready on muted
                            video.muted = true;
                            video.play().then(() => {
                                this.isPlaying = true;
                            }).catch(() => {
                                this.isPlaying = false;
                            });
                        });
                    }
                }
            },

            // Unified Play/Pause Toggle
            togglePlay() {
                if (this.videoType === 'youtube') {
                    const frame = document.getElementById('ytPlayer_' + this.id);
                    if (this.isPlaying) {
                        if (this.ytPlayer && typeof this.ytPlayer.pauseVideo === 'function') {
                            try { this.ytPlayer.pauseVideo(); } catch (_) {}
                        }
                        if (frame && frame.contentWindow) {
                            try { frame.contentWindow.postMessage(JSON.stringify({event: 'command', func: 'pauseVideo', args: ''}), '*'); } catch (_) {}
                        }
                        this.isPlaying = false;
                    } else {
                        if (this.ytPlayer && typeof this.ytPlayer.playVideo === 'function') {
                            try { this.ytPlayer.playVideo(); } catch (_) {}
                        }
                        if (frame && frame.contentWindow) {
                            try { frame.contentWindow.postMessage(JSON.stringify({event: 'command', func: 'playVideo', args: ''}), '*'); } catch (_) {}
                        }
                        this.isPlaying = true;
                    }
                    return;
                } else if (this.videoType === 'storage' && this.$refs.html5Video) {
                    const video = this.$refs.html5Video;
                    if (video.paused) {
                        video.play().then(() => { this.isPlaying = true; }).catch(() => {});
                    } else {
                        video.pause();
                        this.isPlaying = false;
                    }
                }
            },

            // Unified Unmute
            unmute() {
                if (this.videoType === 'youtube') {
                    if (this.ytPlayer && typeof this.ytPlayer.unMute === 'function') {
                        try {
                            this.ytPlayer.unMute();
                            this.ytPlayer.setVolume(100);
                            this.ytPlayer.playVideo();
                        } catch (_) {}
                    }
                    const frame = document.getElementById('ytPlayer_' + this.id);
                    if (frame && frame.contentWindow) {
                        try {
                            frame.contentWindow.postMessage(JSON.stringify({event: 'command', func: 'unMute', args: ''}), '*');
                            frame.contentWindow.postMessage(JSON.stringify({event: 'command', func: 'setVolume', args: [100]}), '*');
                            frame.contentWindow.postMessage(JSON.stringify({event: 'command', func: 'playVideo', args: ''}), '*');
                        } catch (_) {}
                    }
                    this.isMuted = false;
                    return;
                } else if (this.videoType === 'storage' && this.$refs.html5Video) {
                    this.$refs.html5Video.muted = false;
                    this.isMuted = false;
                }
            },

            onHtml5TimeUpdate(e) {
                const video = e.target;
                if (!video || !video.duration) return;
                this.progressPercent = (video.currentTime / video.duration) * 100;
                this.currentTimeFormatted = this.formatSeconds(video.currentTime);
                this.durationFormatted = this.formatSeconds(video.duration);
            },

            seekHtml5(e) {
                const video = this.$refs.html5Video;
                if (!video || !video.duration) return;
                const rect = e.currentTarget.getBoundingClientRect();
                const pos = (e.clientX - rect.left) / rect.width;
                video.currentTime = pos * video.duration;
            },

            formatSeconds(sec) {
                sec = Math.floor(sec || 0);
                const m = Math.floor(sec / 60);
                const s = sec % 60;
                return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            },

            // ========================================================
            // MEDIA ENDED HANDLER (Unified HTML5 & YouTube)
            // ========================================================
            handleMediaEnded() {
                this.isPlaying = false;
                if (!this.nextEpisodeUrl) return;

                if (this.isPremium || !this.hasAd) {
                    // VIP User: Auto advance with brief 2-second countdown
                    this.startVipCountdown();
                } else {
                    // Free User: Show interstitial ad modal
                    this.triggerNextEpisode(this.nextEpisodeUrl);
                }
            },

            startVipCountdown() {
                this.showVipOverlay = true;
                this.vipCountdown = 2;
                clearInterval(this.vipTimer);

                this.vipTimer = setInterval(() => {
                    this.vipCountdown--;
                    if (this.vipCountdown <= 0) {
                        clearInterval(this.vipTimer);
                        this.showVipOverlay = false;
                        window.location.href = this.nextEpisodeUrl;
                    }
                }, 1000);
            },

            cancelVipAdvance() {
                clearInterval(this.vipTimer);
                this.showVipOverlay = false;
            },

            proceedNextImmediately() {
                clearInterval(this.vipTimer);
                this.showVipOverlay = false;
                if (this.nextEpisodeUrl) {
                    window.location.href = this.nextEpisodeUrl;
                }
            },

            // Trigger Interstitial Ad Modal for Next Episode
            triggerNextEpisode(targetUrl) {
                if (this.isPremium || !this.hasAd) {
                    window.location.href = targetUrl;
                    return;
                }

                // Pause main video if playing
                if (this.videoType === 'youtube' && this.ytPlayer) {
                    try { this.ytPlayer.pauseVideo(); } catch (_) {}
                } else if (this.videoType === 'storage' && this.$refs.html5Video) {
                    try { this.$refs.html5Video.pause(); } catch (_) {}
                }

                this.pendingNextUrl = targetUrl;
                this.showAdModal = true;
                this.adCountdown = this.adDuration;

                this.$nextTick(() => {
                    const adVid = this.$refs.adVideoPlayer;
                    if (adVid) {
                        adVid.currentTime = 0;
                        adVid.play().catch(() => {});
                    }
                });

                clearInterval(this.adTimer);
                this.adTimer = setInterval(() => {
                    this.adCountdown--;
                    if (this.adCountdown <= 0) {
                        clearInterval(this.adTimer);
                    }
                }, 1000);
            },

            skipAdAndPlayNext() {
                if (this.adCountdown > 0) return;
                clearInterval(this.adTimer);
                const dest = this.pendingNextUrl || this.nextEpisodeUrl;
                if (dest) {
                    window.location.href = dest;
                } else {
                    this.showAdModal = false;
                }
            },

            // Touch Swipe Up (Next) & Down (Prev)
            handleTouchStart(e) {
                this.touchStartY = e.touches[0].clientY;
                this.touchStartX = e.touches[0].clientX;
            },

            handleTouchEnd(e) {
                const deltaY = e.changedTouches[0].clientY - this.touchStartY;
                const deltaX = e.changedTouches[0].clientX - this.touchStartX;

                // Threshold of 60px vertical swipe
                if (Math.abs(deltaY) > 60 && Math.abs(deltaY) > Math.abs(deltaX)) {
                    if (deltaY < 0 && this.nextEpisodeUrl) {
                        this.triggerNextEpisode(this.nextEpisodeUrl);
                    } else if (deltaY > 0 && this.prevEpisodeUrl) {
                        window.location.href = this.prevEpisodeUrl;
                    }
                }
            }
        };
    }
</script>
