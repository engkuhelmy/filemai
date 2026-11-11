@extends('layouts.app')

@php
    $featured = $heroDramas[0] ?? null;
@endphp

@section('content')
<div class="space-y-10 pb-16" x-data="netflixHome({{ json_encode($heroDramas) }})">

    <!-- ======================================================== -->
    <!-- NETFLIX HERO BILLBOARD SECTION WITH 5S TRAILER TEASER -->
    <!-- ======================================================== -->
    <div class="relative w-full h-[75vh] sm:h-[82vh] lg:h-[88vh] min-h-[520px] max-h-[900px] overflow-hidden select-none -mt-16 sm:-mt-20">
        
        <!-- Background Media Layer: 5-Second Video Trailer Teaser + High-Res Billboard Poster Fallback -->
        <div class="absolute inset-0 z-0">
            <!-- 5s Auto-play Muted Video Trailer Teaser -->
            <video x-ref="trailerVideo"
                   autoplay muted playsinline
                   :src="currentHero?.trailer_url || '/uploads/videos/sample_reel.mp4'"
                   class="w-full h-full object-cover object-center absolute inset-0 transition-opacity duration-1000"
                   :class="trailerActive ? 'opacity-85' : 'opacity-0'">
            </video>

            <!-- High-Res Billboard Poster -->
            <img :src="currentHero?.banner_url || '{{ $featured->banner_url ?? '/uploads/posters/dendam-billboard.jpg' }}'" 
                 :alt="currentHero?.title || '{{ $featured->title ?? 'Filemai Drama' }}'" 
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

        <!-- Hero Content Overlay -->
        <div class="relative z-10 max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12 h-full flex flex-col justify-center pt-24 sm:pt-28">
            
            <div class="max-w-2xl space-y-4 sm:space-y-5">
                
                <!-- Hook Badge & Duration Row -->
                <div class="space-y-1.5 text-xs sm:text-sm text-slate-300 font-medium">
                    <div class="flex items-center gap-2 text-slate-400">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-[#E50914]/20 border border-[#E50914]/40 text-[#E50914] font-black text-[11px] uppercase tracking-wider"
                              x-text="currentHero?.hook || '{{ $featured->hook ?? '🔥 Paling Trending di Malaysia' }}'">
                            {{ $featured->hook ?? '🔥 Paling Trending di Malaysia' }}
                        </span>
                        <span>•</span>
                        <span>Siri: <strong class="text-white" x-text="(currentHero?.episodes_count || {{ $featured->episodes_count ?? 3 }}) + ' Episod'">{{ $featured->episodes_count ?? 3 }} Episod</strong></span>
                    </div>

                    <div class="flex items-center gap-2.5 text-xs text-slate-300">
                        <span class="flex items-center gap-1 text-amber-400 font-extrabold">
                            <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400"></i>
                            <span x-text="currentHero?.rating || '{{ $featured->rating ?? '8.9' }}'">{{ $featured->rating ?? '8.9' }}</span>
                        </span>
                        <span class="text-slate-500">•</span>
                        <span class="text-slate-300">Format 9:16 • Full HD •</span>
                        <span class="text-slate-200 font-bold" x-text="currentHero?.genre || '{{ $featured->genre ?? 'Drama' }}'">{{ $featured->genre ?? 'Drama' }}</span>
                    </div>
                </div>

                <!-- Big Title -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight leading-tight uppercase font-['Bebas_Neue','Inter',sans-serif]"
                    x-text="currentHero?.title">
                    {{ $featured->title ?? 'Dendam Pewaris Yang Dibuang' }}
                </h1>

                <!-- Synopsis Text -->
                <p class="text-xs sm:text-sm text-slate-200 leading-relaxed line-clamp-3 max-w-xl font-medium"
                   x-text="currentHero?.synopsis">
                    {{ $featured->synopsis ?? 'Tonton siri penuh mini drama menegak terbaik.' }}
                </p>

                <!-- Netflix Red Watch & Info Buttons -->
                <div class="flex items-center gap-3 pt-2 sm:pt-4">
                    <!-- Red Watch Button -->
                    <a :href="'/drama/' + (currentHero?.slug || '{{ $featured->slug ?? '' }}') + '/episode/1'"
                       class="px-6 sm:px-8 py-3 sm:py-3.5 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white font-extrabold text-xs sm:text-sm flex items-center gap-2.5 shadow-xl shadow-red-950/60 hover:scale-105 transition-all">
                        <i data-lucide="play" class="w-4 h-4 fill-white"></i>
                        <span>WATCH NOW</span>
                    </a>

                    <!-- Creator Button -->
                    <a href="/creator/dramas/1/episodes/create"
                       class="px-5 sm:px-6 py-3 sm:py-3.5 rounded-full bg-white/15 hover:bg-white/25 backdrop-blur-md text-white font-bold text-xs sm:text-sm border border-white/20 flex items-center gap-2 transition-all">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span>UPLOAD EPISOD</span>
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
        
        <!-- Tabs Row -->
        <div class="flex items-center space-x-6 sm:space-x-8 text-xs sm:text-sm font-bold border-b border-white/[0.08] pb-3 overflow-x-auto no-scrollbar">
            <a href="/?tab=trends" class="flex items-center gap-2 whitespace-nowrap transition-colors relative py-1 {{ ($activeTab === 'trends' || empty($activeTab)) ? 'text-white' : 'text-slate-400 hover:text-white' }}">
                <i data-lucide="trending-up" class="w-4 h-4 text-[#E50914]"></i>
                <span>Trends Now</span>
                @if ($activeTab === 'trends' || empty($activeTab))
                    <span class="absolute -bottom-3 left-0 right-0 h-0.5 bg-[#E50914]"></span>
                @endif
            </a>

            <a href="/?tab=popular" class="flex items-center gap-2 whitespace-nowrap transition-colors relative py-1 {{ ($activeTab === 'popular') ? 'text-white' : 'text-slate-400 hover:text-white' }}">
                <i data-lucide="flame" class="w-4 h-4 text-amber-500"></i>
                <span>Popular</span>
                @if ($activeTab === 'popular')
                    <span class="absolute -bottom-3 left-0 right-0 h-0.5 bg-[#E50914]"></span>
                @endif
            </a>

            <a href="/subscribe" class="flex items-center gap-2 whitespace-nowrap text-amber-400 hover:text-amber-300 py-1">
                <i data-lucide="crown" class="w-4 h-4 fill-amber-400"></i>
                <span>VIP Ad-Free (RM5)</span>
            </a>
        </div>

        <!-- Horizontal Category Pills Bar -->
        <div class="flex items-center gap-2 pt-2" x-data="{
            scrollLeft() { $refs.pillsContainer.scrollBy({ left: -250, behavior: 'smooth' }) },
            scrollRight() { $refs.pillsContainer.scrollBy({ left: 250, behavior: 'smooth' }) }
        }">
            <button @click="scrollLeft" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300 hover:text-white shrink-0 transition-colors">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
            </button>

            <div x-ref="pillsContainer" class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1 flex-1">
                @foreach ($genres as $g)
                    <a href="{{ $g === 'Semua' ? '/' : '/?genre=' . urlencode($g) }}" 
                       class="px-4 sm:px-5 py-1.5 sm:py-2 rounded-full text-xs font-bold whitespace-nowrap transition-all {{ ($selectedGenre === $g) ? 'bg-[#E50914] text-white shadow-lg shadow-red-900/40' : 'bg-[#181818] text-slate-300 hover:text-white hover:bg-[#252525] border border-white/[0.06]' }}">
                        {{ $g }}
                    </a>
                @endforeach
            </div>

            <button @click="scrollRight" class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-slate-300 hover:text-white shrink-0 transition-colors">
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
            </button>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- POSTER ROW CAROUSEL 1: TRENDS NOW -->
    <!-- ======================================================== -->
    <section class="max-w-[1500px] mx-auto px-4 sm:px-8 lg:px-12 space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <span>Trends Now</span>
                <span class="text-xs font-semibold text-[#E50914] uppercase">Trending #1</span>
            </h2>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 pb-4 pt-1">
            @foreach ($trendsNow as $drama)
                <div class="w-full">
                    <a href="/drama/{{ $drama->slug }}/episode/1" class="group block">
                        <div class="relative aspect-[9/16] rounded-xl overflow-hidden bg-[#181818] border border-white/[0.08] shadow-lg group-hover:scale-[1.02] transition-transform duration-300">
                            <img src="{{ $drama->poster_url }}" alt="{{ $drama->title }}" 
                                 class="w-full h-full object-cover">

                            <div class="absolute top-2 left-2 flex items-center gap-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-[#E50914] text-white">
                                    {{ $drama->genre }}
                                </span>
                            </div>

                            <div class="absolute top-2 right-2">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-black/70 backdrop-blur-md text-white">
                                    {{ $drama->episodes_count }} Ep
                                </span>
                            </div>

                            <div class="absolute inset-x-0 bottom-0 p-3 pt-12 bg-gradient-to-t from-black via-black/80 to-transparent flex flex-col justify-end">
                                <h3 class="text-xs sm:text-sm font-bold text-white group-hover:text-[#E50914] transition-colors truncate">
                                    {{ $drama->title }}
                                </h3>
                                <div class="flex items-center justify-between text-[10px] text-slate-400 mt-0.5">
                                    <span class="flex items-center gap-1 text-amber-400 font-bold">
                                        <i data-lucide="star" class="w-2.5 h-2.5 fill-amber-400"></i> {{ $drama->rating ?? '8.5' }}
                                    </span>
                                    <span>{{ number_format($drama->views_count) }} tontonan</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- VIP AD-FREE PROMO STRIP -->
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
                        Tonton siri tanpa henti, langkau semua iklan, dan dapatkan akses episod terkunci!
                    </p>
                </div>
            </div>

            <div class="shrink-0">
                <a href="/subscribe" class="px-6 py-3 rounded-full bg-[#E50914] hover:bg-[#F40612] text-white font-extrabold text-xs sm:text-sm shadow-xl shadow-red-950/80 hover:scale-105 transition-all flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 fill-white"></i>
                    <span>Langgan Sekarang</span>
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
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
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
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                });
            },

            toggleMute() {
                if (this.$refs.trailerVideo) {
                    this.isMuted = !this.isMuted;
                    this.$refs.trailerVideo.muted = this.isMuted;
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                }
            }
        }
    }
</script>
@endsection
